<?php

namespace App\Controllers\Api;

use App\Models\ProductModel;

class ProductController extends BaseApiController
{
    protected ProductModel $productModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
    }

    /**
     * GET /api/products
     * Supports: ?search=keyword&category_id=1&page=1&per_page=10
     */
    public function index()
    {
        $search     = $this->request->getGet('search');
        $categoryId = $this->request->getGet('category_id');
        $perPage    = (int) ($this->request->getGet('per_page') ?? 10);
        $perPage    = $perPage > 0 ? $perPage : 10;

        $builder = $this->productModel->getWithCategory()->orderBy('products.id', 'ASC');

        if (! empty($search)) {
            $builder->groupStart()
                ->like('products.name', $search)
                ->orLike('products.sku', $search)
                ->groupEnd();
        }

        if (! empty($categoryId)) {
            $builder->where('products.category_id', $categoryId);
        }

        $products = $builder->paginate($perPage);

        return $this->respondSuccess([
            'items'      => $products,
            'pagination' => [
                'total'        => $this->productModel->pager->getTotal(),
                'per_page'     => $perPage,
                'current_page' => $this->productModel->pager->getCurrentPage(),
                'last_page'    => $this->productModel->pager->getPageCount(),
            ],
        ], 'Products retrieved successfully');
    }

    /**
     * GET /api/products/{id}
     */
    public function show($id = null)
    {
        $product = $this->productModel->getWithCategory((int) $id);

        if (! $product) {
            return $this->respondNotFoundError('Product not found');
        }

        return $this->respondSuccess($product, 'Product retrieved successfully');
    }

    /**
     * POST /api/products
     */
    public function create()
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();

        if (! $this->productModel->save($data)) {
            return $this->respondValidationError($this->productModel->errors());
        }

        $product = $this->productModel->getWithCategory($this->productModel->getInsertID());

        return $this->respondCreatedSuccess($product, 'Product created successfully');
    }

    /**
     * PUT/PATCH /api/products/{id}
     */
    public function update($id = null)
    {
        $product = $this->productModel->find($id);

        if (! $product) {
            return $this->respondNotFoundError('Product not found');
        }

        $data = $this->request->getJSON(true) ?? $this->request->getRawInput();

        if (! $this->productModel->update($id, $data)) {
            return $this->respondValidationError($this->productModel->errors());
        }

        $product = $this->productModel->getWithCategory((int) $id);

        return $this->respondSuccess($product, 'Product updated successfully');
    }

    /**
     * DELETE /api/products/{id}
     */
    public function delete($id = null)
    {
        $product = $this->productModel->find($id);

        if (! $product) {
            return $this->respondNotFoundError('Product not found');
        }

        $this->productModel->delete($id);

        return $this->respondSuccess(null, 'Product deleted successfully');
    }
}
