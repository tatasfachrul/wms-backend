<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class TransactionEntity extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'product_stock' => 'integer',
    ];
}
