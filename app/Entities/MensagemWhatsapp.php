<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class MensagemWhatsapp extends Entity
{
    protected $attributes = [
        'id'                 => null,
        'numero'             => null,
        'mensagem'           => null,
        'status'             => 'enviado',
        'provider_message_id'=> null,
        'sent_at'            => null,
        'received_at'        => null,
    ];

    protected $dates = ['created_at', 'updated_at', 'sent_at', 'received_at'];

    protected $casts = [
        'id' => 'integer',
    ];
}