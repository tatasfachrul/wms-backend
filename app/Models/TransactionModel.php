<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\TransactionEntity;

class TransactionModel extends BaseModel
{
    protected $table = 'transactions';
    protected $primaryKey = 'id';
    protected $allowedFields = ['product_id', 'type', 'quantity', 'created_at'];
    public $useTimestamps = false;
    protected $returnType = 'array';
    public function withProduct($limit = 10, $offset = 0)
    {
        $builder = $this->db->table($this->table)
            ->select('transactions.*, products.name as product_name, products.stock as product_stock')
            ->join('products', 'products.id = transactions.product_id', 'left')
            ->orderBy('transactions.created_at', 'DESC')
            ->limit($limit, $offset);
        
            $data = $builder->get()->getResultArray();

            $total = $this->db->table($this->table)->countAllResults();

            return [
                'data' => $data,
                'total' => $total,
            ];
    }

}
