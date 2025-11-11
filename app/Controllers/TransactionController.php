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
        $model = new TransactionModel();
        $data = $model->orderBy('created_at', 'DESC')->findAll();
        return $this->respond(['success' => true, 'data' => $data]);
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
            $newStock = $product['stok'] + $quantity;
        } else { // OUT
            $newStock = $product['stok'] - $quantity;
            if ($newStock < 0) {
                $db->transComplete(); // just finish
                return $this->failValidationError('Insufficient stock');
            }
        }

        // update stock
        $productModel->update($product['id'], ['stok' => $newStock]);

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
                'stok' => $newStock
            ]
        ]);
    }
}
