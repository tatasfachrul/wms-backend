<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\TransactionModel;
use App\Models\ProductModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class TransactionController extends ResourceController
{
    protected $modelName = TransactionModel::class;
    protected $format = 'json';

    // GET /api/transactions
    public function index()
    {
        $page = (int) $this->request->getGet('page') ?: 1;
        $perPage = (int) $this->request->getGet('perPage') ?: 10;
        $offset = ($page - 1) * $perPage;

        $model = new TransactionModel();
        $result = $model->withProduct($perPage, $offset);

        $data = array_map(fn($row) => [
            'id' => (int) $row['id'],
            'product_id' => (int) $row['product_id'],
            'quantity' => (int) $row['quantity'],
            'product_stock' => isset($row['product_stock']) ? (int) $row['product_stock'] : null,
            'type' => $row['type'],
            'product_name' => $row['product_name'] ?? null,
            'created_at' => $row['created_at'],
        ], $result['data']);

        return $this->respond(['success' => true, 'data' => $data, 'pagination' => [
            'page' => $page,
            'perPage' => $perPage,
            'total' => $result['total'],
            'totalPages' => ceil($result['total'] / $perPage)
        ]]);
    }

    // POST /api/transactions
    public function create()
    {
        $payload = $this->request->getJSON(true) ?: $this->request->getPost();
        $rules = [
            'product_id' => 'required|integer',
            'type' => 'required|in_list[IN,OUT]',
            'quantity' => 'required|integer|greater_than_equal_to[1]'
        ];
        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $productModel = new ProductModel();
        $product = $productModel->find($payload['product_id']);
        if (!$product)
            return $this->failNotFound('Product not found');

        $quantity = (int) $payload['quantity'];
        $type = strtoupper($payload['type']);

        // DB transaction to ensure atomicity
        $db = \Config\Database::connect();
        $db->transStart();

        if ($type === 'IN') {
            $newStock = $product['stock'] + $quantity;
        } else { // OUT
            $newStock = $product['stock'] - $quantity;
            if ($newStock < 0) {
                $db->transComplete(); // just finish
                return $this->failValidationError('Insufficient stock');
            }
        }

        // update stock
        $productModel->update($product['id'], ['stock' => $newStock]);

        // insert transaction
        $transModel = new TransactionModel();
        $transModel->insert([
            'product_id' => $product['id'],
            'type' => $type,
            'quantity' => $quantity,
        ]);

        $db->transComplete();
        if ($db->transStatus() === false) {
            return $this->failServerError('Database transaction failed');
        }

        return $this->respondCreated([
            'success' => true,
            'message' => 'Transaction recorded',
            'data' => [
                'product_id' => $product['id'],
                'type' => $type,
                'quantity' => $quantity,
                'stock' => $newStock
            ]
        ]);
    }
}
