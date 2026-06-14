<?php

namespace App\Controllers\Api;

use App\Models\CategoryModel;
use App\Models\ProductModel;
use CodeIgniter\HTTP\ResponseInterface;

class CategoryController extends BaseApiController
{
    protected CategoryModel $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
    }

    /**
     * GET /api/categories
     * Supports: ?search=keyword&page=1&per_page=10
     */
    public function index()
    {
        $search  = $this->request->getGet('search');
        $perPage = (int) ($this->request->getGet('per_page') ?? 10);
        $perPage = $perPage > 0 ? $perPage : 10;

        $builder = $this->categoryModel->orderBy('id', 'ASC');

        if (! empty($search)) {
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('description', $search)
                ->groupEnd();
        }

        $categories = $builder->paginate($perPage);

        return $this->respondSuccess([
            'items'      => $categories,
            'pagination' => [
                'total'        => $this->categoryModel->pager->getTotal(),
                'per_page'     => $perPage,
                'current_page' => $this->categoryModel->pager->getCurrentPage(),
                'last_page'    => $this->categoryModel->pager->getPageCount(),
            ],
        ], 'Categories retrieved successfully');
    }

    /**
     * GET /api/categories/{id}
     */
    public function show($id = null)
    {
        $category = $this->categoryModel->find($id);

        if (! $category) {
            return $this->respondNotFoundError('Category not found');
        }

        return $this->respondSuccess($category, 'Category retrieved successfully');
    }

    /**
     * POST /api/categories
     */
    public function create()
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        if (! $this->categoryModel->save($data)) {
            return $this->respondValidationError($this->categoryModel->errors());
        }

        $category = $this->categoryModel->find($this->categoryModel->getInsertID());

        return $this->respondCreatedSuccess($category, 'Category created successfully');
    }

    /**
     * PUT/PATCH /api/categories/{id}
     */
    public function update($id = null)
    {
        $category = $this->categoryModel->find($id);

        if (! $category) {
            return $this->respondNotFoundError('Category not found');
        }

        $data = $this->request->getJSON(true) ?? $this->request->getRawInput();

        if (! $this->categoryModel->update($id, $data)) {
            return $this->respondValidationError($this->categoryModel->errors());
        }

        $category = $this->categoryModel->find($id);

        return $this->respondSuccess($category, 'Category updated successfully');
    }

    /**
     * DELETE /api/categories/{id}
     */
    public function delete($id = null)
    {
        $category = $this->categoryModel->find($id);

        if (! $category) {
            return $this->respondNotFoundError('Category not found');
        }

        $productModel = new ProductModel();
        if ($productModel->where('category_id', $id)->countAllResults() > 0) {
            return $this->respondError(
                'Category cannot be deleted because it still has related products',
                ResponseInterface::HTTP_CONFLICT
            );
        }

        $this->categoryModel->delete($id);

        return $this->respondSuccess(null, 'Category deleted successfully');
    }
}
