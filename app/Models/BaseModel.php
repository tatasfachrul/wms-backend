<?php
namespace App\Models;

use CodeIgniter\Model;

class BaseModel extends Model
{
    protected $castHandlers = [
        'integer' => \CodeIgniter\Entity\Cast\IntegerCast::class,
    ];
}
