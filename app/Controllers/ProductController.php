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
        $q = $this->request->getGet('q'); // search
        $sort = $this->request->getGet('sort', FILTER_SANITIZE_STRING) ?: 'id';
        $order = $this->request->getGet('order', FILTER_SANITIZE_STRING) ?: 'ASC';
        $limit = (int) $this->request->getGet('limit') ?: null;

        $builder = $this->model;

        if ($q) {
            $builder = $builder->like('nama_barang', $q)->orLike('sku', $q);
        }

        // Validate sort column (simple whitelist)
        $allowedSort = ['id', 'nama_barang', 'sku', 'stok', 'minimum_stok'];
        if (!in_array($sort, $allowedSort))
            $sort = 'id';
        $order = (strtoupper($order) === 'DESC') ? 'DESC' : 'ASC';

        $data = $builder->orderBy($sort, $order);
        if ($limit)
            $data = $data->findAll($limit);
        else
            $data = $data->findAll();

        return $this->respond([
            'success' => true,
            'data' => $data
        ]);
    }

    // GET /api/products/{id}
    public function show($id = null)
    {
        $data = $this->model->find($id);
        if (!$data)
            return $this->failNotFound("Product not found");
        return $this->respond(['success' => true, 'data' => $data]);
    }

    // POST /api/products
    public function create()
    {
        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $rules = [
            'nama_barang' => 'required|max_length[255]',
            'sku' => 'required|max_length[100]',
            'stok' => 'required|integer',
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
            'nama_barang' => 'permit_empty|max_length[255]',
            'sku' => 'permit_empty|max_length[100]',
            'stok' => 'permit_empty|integer',
        ];
        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // if SKU changed, ensure unique
        if (!empty($payload['sku']) && $payload['sku'] !== $product['sku']) {
            if ($this->model->where('sku', $payload['sku'])->first()) {
                return $this->failValidationError('SKU already exists');
            }
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
