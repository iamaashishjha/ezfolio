<?php

namespace App\Http\Controllers\Frontend;

use App\Helpers\CoreConstants;
use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Services\Contracts\BlogCategoryInterface;
use App\Services\Contracts\BlogCommentInterface;
use App\Services\Contracts\BlogPostInterface;
use App\Services\Contracts\BlogTagInterface;
use App\Services\Contracts\FrontendInterface;
use Carbon\Carbon;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Validator;

class BlogController extends Controller
{
    /**
     * @var FrontendInterface
     */
    private $frontend;

    /**
     * Create a new instance
     *
     * @param FrontendInterface $frontend
     * @return void
     */
    public function __construct(FrontendInterface $frontend)
    {
        $this->frontend = $frontend;
    }

    /**
     * Blog index page
     *
     * @param Request $request
     * @return View|Factory
     */
    public function index(Request $request)
    {
        $data = $this->loadBaseData();
        if (!$data) {
            return view('errors.404');
        }
        if (!empty($data['maintenance'])) {
            return view('frontend.maintenance', $data);
        }

        $filters = [
            'search' => $request->q,
            'category' => $request->category,
            'tag' => $request->tag,
            'perPage' => 6,
        ];

        $postsResult = resolve(BlogPostInterface::class)->getFrontendPaginated($filters);
        $categoriesResult = resolve(BlogCategoryInterface::class)->getAll();
        $tagsResult = resolve(BlogTagInterface::class)->getAll();

        $data['posts'] = $postsResult['status'] === CoreConstants::STATUS_CODE_SUCCESS ? $postsResult['payload'] : null;
        $data['categories'] = $categoriesResult['status'] === CoreConstants::STATUS_CODE_SUCCESS ? $categoriesResult['payload']->where('is_active', CoreConstants::TRUE) : [];
        $data['tags'] = $tagsResult['status'] === CoreConstants::STATUS_CODE_SUCCESS ? $tagsResult['payload'] : [];
        $data['filters'] = $filters;
        $data['pageTitle'] = 'Blog — ' . ($data['portfolioConfig']['seo']['title'] ?: $data['about']->name);
        $data['pageDescription'] = $data['portfolioConfig']['seo']['description'];
        $data['canonicalUrl'] = url('/blog');

        return view('frontend.blog.index', $data);
    }

    /**
     * Blog show page
     *
     * @param string $slug
     * @return View|Factory
     */
    public function show(string $slug)
    {
        $data = $this->loadBaseData();
        if (!$data) {
            return view('errors.404');
        }
        if (!empty($data['maintenance'])) {
            return view('frontend.maintenance', $data);
        }

        $postResult = resolve(BlogPostInterface::class)->getFrontendBySlug($slug);
        if ($postResult['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
            return view('errors.404');
        }

        $post = $postResult['payload'];
        $post->increment('views_count');
        $commentsResult = resolve(BlogCommentInterface::class)->getApprovedByPostId($post->id);
        $comments = $commentsResult['status'] === CoreConstants::STATUS_CODE_SUCCESS ? $commentsResult['payload'] : collect();

        $data['post'] = $post;
        $data['commentCount'] = $comments->count();
        $data['comments'] = $this->buildCommentTree($comments);
        $data['pageTitle'] = $post->meta_title ?: $post->title;
        $data['pageDescription'] = $post->meta_description ?: ($post->excerpt ?: Str::limit(strip_tags($post->body), 160));
        $data['pageImage'] = $post->cover_image_url ?: ($data['portfolioConfig']['seo']['image_url'] ?? $data['portfolioConfig']['seo']['image']);
        $data['canonicalUrl'] = $post->canonical_url ?: url()->current();
        $data['relatedPosts'] = BlogPost::where('status', 'published')
            ->where('id', '<>', $post->id)
            ->where('category_id', $post->category_id)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', Carbon::now()->format('Y-m-d H:i:s'));
            })
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        return view('frontend.blog.show', $data);
    }

    /**
     * Store blog comment
     *
     * @param Request $request
     * @param string $slug
     * @return RedirectResponse
     */
    public function storeComment(Request $request, string $slug)
    {
        $data = $this->loadBaseData();
        if (!$data) {
            return redirect()->route('frontend');
        }
        if (!empty($data['maintenance'])) {
            return redirect()->route('frontend');
        }

        $postResult = resolve(BlogPostInterface::class)->getFrontendBySlug($slug);
        if ($postResult['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
            return redirect()->route('blog.index');
        }

        $post = $postResult['payload'];
        $commentsUrl = route('blog.show', $post->slug) . '#comments';
        if (!$post->allow_comments) {
            return redirect()->to($commentsUrl)->with('comment_status', 'Comments are disabled for this post.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'email' => 'nullable|email',
            'body' => 'required|string|max:5000',
            'parent_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->to($commentsUrl)
                ->withErrors($validator)
                ->withInput();
        }

        $payload = [
            'blog_post_id' => $post->id,
            'parent_id' => $request->parent_id ?: null,
            'name' => $request->name,
            'email' => $request->email,
            'body' => $request->body,
            'is_approved' => CoreConstants::FALSE,
        ];

        $result = resolve(BlogCommentInterface::class)->store($payload);
        if ($result['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
            return redirect()->to($commentsUrl)->with('comment_status', $result['message']);
        }

        return redirect()->to($commentsUrl)->with('comment_status', 'Thanks! Your comment is awaiting approval.');
    }

    /**
     * RSS feed
     *
     * @return Response
     */
    public function rss()
    {
        $data = $this->loadBaseData();
        if (!$data || !empty($data['maintenance'])) {
            return response()->view('frontend.blog.rss', ['posts' => collect()], 404);
        }

        $posts = BlogPost::where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', Carbon::now()->format('Y-m-d H:i:s'));
            })
            ->orderBy('published_at', 'desc')
            ->limit(20)
            ->get();

        return response()
            ->view('frontend.blog.rss', [
                'posts' => $posts,
                'portfolioConfig' => $data['portfolioConfig'],
                'about' => $data['about'],
            ])
            ->header('Content-Type', 'application/rss+xml');
    }

    private function loadBaseData()
    {
        $data = $this->frontend->getAllData();
        if ($data['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
            return null;
        }

        $payload = $data['payload'];
        $payload['demoMode'] = Config::get('custom.demo_mode');

        if (empty($payload['about'])) {
            return null;
        }

        if ((int) $payload['portfolioConfig']['maintenanceMode'] === CoreConstants::TRUE) {
            $payload['maintenance'] = true;
        }

        if (empty($payload['portfolioConfig']['visibility']['blog']) || (int) $payload['portfolioConfig']['visibility']['blog'] !== CoreConstants::TRUE) {
            return null;
        }

        return $payload;
    }

    private function buildCommentTree($comments)
    {
        $grouped = $comments->groupBy('parent_id');

        $build = function ($parentId) use (&$build, $grouped) {
            return $grouped->get($parentId, collect())->map(function ($comment) use ($build) {
                $comment->replies = $build($comment->id);
                return $comment;
            });
        };

        return $build(null);
    }
}
