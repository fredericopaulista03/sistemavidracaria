<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Venda extends Entity
{
    protected $attributes = [
        'id'         => null,
        'cliente_id' => null,
        'produto'    => null,
        'valor'      => null,
        'status'     => 'aberta',
    ];

    protected $dates = ['created_at', 'updated_at'];

    protected $casts = [
        'id'         => 'integer',
        'cliente_id' => 'integer',
        'valor'      => 'float',
    ];
}