<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\BlogPostInterface;
use CoreConstants;
use Illuminate\Http\Request;

class BlogPostController extends Controller
{
    /**
     * @var BlogPostInterface
     */
    private $post;

    /**
     * Create a new instance
     *
     * @param BlogPostInterface $post
     * @return void
     */
    public function __construct(BlogPostInterface $post)
    {
        $this->post = $post;
    }

    /**
     * Get all items
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $result = $this->post->getAllWithPaginate($request->all());

        return response()->json($result, !empty($result['status']) ? $result['status'] : CoreConstants::STATUS_CODE_SUCCESS);
    }

    /**
     * Store a new item
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $result = $this->post->store($request->all());

        return response()->json($result, !empty($result['status']) ? $result['status'] : CoreConstants::STATUS_CODE_SUCCESS);
    }

    /**
     * Show the given item
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id)
    {
        $result = $this->post->getById($id);

        return response()->json($result, !empty($result['status']) ? $result['status'] : CoreConstants::STATUS_CODE_SUCCESS);
    }

    /**
     * Update the given item
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id)
    {
        $result = $this->post->store($request->all());

        return response()->json($result, !empty($result['status']) ? $result['status'] : CoreConstants::STATUS_CODE_SUCCESS);
    }

    /**
     * Destroy the given items
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request)
    {
        $result = $this->post->deleteByIds($request->ids);

        return response()->json($result, !empty($result['status']) ? $result['status'] : CoreConstants::STATUS_CODE_SUCCESS);
    }
}
