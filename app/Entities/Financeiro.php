<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Financeiro extends Entity
{
    protected $attributes = [
        'id'        => null,
        'tipo'      => null,
        'descricao' => null,
        'valor'     => null,
        'data'      => null,
    ];

    protected $dates = ['created_at', 'updated_at', 'data'];

    protected $casts = [
        'id'    => 'integer',
        'valor' => 'float',
    ];
}