<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Product extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'stock' => 'integer',
        'minimum_stock' => 'integer'
    ];
}
