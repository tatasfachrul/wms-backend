<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ProductModel;

class ProductController extends ResourceController
{
    protected $modelName = ProductModel::class;
    protected $format = 'json';

    // GET /api/products
    public function index()
    {
        // Query params
        $keyword = $this->request->getGet('keyword');
        $sort = $this->request->getGet('sort', FILTER_SANITIZE_STRING) ?: 'id';
        $order = strtoupper($this->request->getGet('order', FILTER_SANITIZE_STRING)) === 'DESC' ? 'DESC' : 'ASC';
        $page = (int) $this->request->getGet('page') ?: 1;
        $perPage = (int) $this->request->getGet('perPage') ?: 10;

        $builder = $this->model;

        if ($keyword) {
            $builder = $builder->groupStart()
                ->like('LOWER(name)', strtolower($keyword))
                ->orLike('LOWER(sku)', strtolower($keyword))
                ->groupEnd();
        }

        // Validate sort column (simple whitelist)
        $allowedSort = ['id', 'name', 'sku', 'stock', 'minimum_stock'];
        if (!in_array($sort, $allowedSort)) {
            $sort = 'id';
        }

        // Pagination
        $offset = ($page - 1) * $perPage;

        // Fetch Data
        $total = $builder->countAllResults(false);
        $data = $builder->orderBy($sort, $order)->findAll($perPage, $offset);

        $data = array_map(fn($row) => [
            'id' => (int) $row['id'],
            'stock' => (int) $row['stock'],
            'minimum_stock' => (int) $row['minimum_stock'],
            'name' => $row['name'],
            'sku' => $row['sku'],
            'shelf_location' => $row['shelf_location'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
        ], $data);


        return $this->respond([
            'success' => true,
            'data' => $data,
            'meta' => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => ceil($total / $perPage)
            ]
        ]);
    }

    // GET /api/products/{id}
    public function show($id = null)
    {
        $data = $this->model->find($id);
        if (!$data)
            return $this->failNotFound("Product not found");

        $data['id'] = (int) $data['id'];
        $data['stock'] = (int) $data['stock'];
        $data['minimum_stock'] = (int) $data['minimum_stock'];
        
        return $this->respond(['success' => true, 'data' => $data]);
    }

    // POST /api/products
    public function create()
    {
        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $rules = [
            'name' => 'required|max_length[255]',
            'sku' => 'required|max_length[100]',
            'stock' => 'required|integer',
        ];
        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // check SKU uniqueness
        if ($this->model->where('sku', $payload['sku'])->first()) {
            return $this->failValidationError('SKU already exists');
        }

        $id = $this->model->insert($payload, true);
        $created = $this->model->find($id);
        return $this->respondCreated(['success' => true, 'data' => $created]);
    }

    // PUT /api/products/{id}
    public function update($id = null)
    {
        $product = $this->model->find($id);
        if (!$product)
            return $this->failNotFound("Product not found");

        $payload = $this->request->getJSON(true) ?: $this->request->getRawInput();
        $rules = [
            'name' => 'permit_empty|max_length[255]',
            'sku' => 'permit_empty|max_length[100]',
            'stock' => 'permit_empty|integer',
        ];
        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // if SKU changed, ensure unique
        if (!empty($payload['sku']) && $payload['sku'] !== $product->sku) {
            if ($this->model->where('sku', $payload['sku'])->first()) {
                return $this->failValidationError('SKU already exists');
            }
        }

        // update entity
        foreach ($payload as $key => $value) {
            $product->$key = $value;
        }

        $this->model->update($id, $payload);
        return $this->respond(['success' => true, 'data' => $this->model->find($id)]);
    }

    // DELETE /api/products/{id}
    public function delete($id = null)
    {
        $product = $this->model->find($id);
        if (!$product)
            return $this->failNotFound("Product not found");

        $this->model->delete($id);
        return $this->respondDeleted(['success' => true, 'message' => 'Deleted', 'id' => $id]);
    }
}
