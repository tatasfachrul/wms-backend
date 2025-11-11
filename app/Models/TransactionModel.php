<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends BaseModel
{
    protected $table = 'transactions';
    protected $primaryKey = 'id';
    protected $allowedFields = ['product_id', 'type', 'quantity', 'created_at'];
    public $timestamps = false;
}
