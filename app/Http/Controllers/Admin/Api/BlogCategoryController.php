<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Services\Contracts\BlogCategoryInterface;
use CoreConstants;
use Illuminate\Http\Request;

class BlogCategoryController extends Controller
{
    /**
     * @var BlogCategoryInterface
     */
    private $category;

    /**
     * Create a new instance
     *
     * @param BlogCategoryInterface $category
     * @return void
     */
    public function __construct(BlogCategoryInterface $category)
    {
        $this->category = $category;
    }

    /**
     * Get all items
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->has('all')) {
            $result = $this->category->getAll();
        } else {
            $result = $this->category->getAllWithPaginate($request->all());
        }

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
        $result = $this->category->store($request->all());

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
        $result = $this->category->getById($id);

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
        $result = $this->category->store($request->all());

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
        $result = $this->category->deleteByIds($request->ids);

        return response()->json($result, !empty($result['status']) ? $result['status'] : CoreConstants::STATUS_CODE_SUCCESS);
    }
}
