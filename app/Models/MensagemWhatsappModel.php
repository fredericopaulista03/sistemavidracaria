<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\MensagemWhatsapp;

class MensagemWhatsappModel extends Model
{
    protected $table            = 'whatsapp_messages';
    protected $primaryKey       = 'id';
    protected $returnType       = MensagemWhatsapp::class;
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;

    protected $allowedFields    = [
        'numero',
        'mensagem',
        'status',
        'provider_message_id',
        'sent_at',
        'received_at',
    ];

    protected $order            = ['created_at' => 'DESC'];

    protected $validationRules = [
        'numero'   => 'required|min_length[8]|max_length[20]',
        'mensagem' => 'required',
        'status'   => 'permit_empty|in_list[enviado,recebido,erro]',
    ];

    /**
     * Buscar mensagens de um número
     */
    public function getByNumero(string $numero): array
    {
        return $this->where('numero', $numero)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Buscar mensagens não entregues
     */
    public function getPendentes(): array
    {
        return $this->where('status', 'enviado')->findAll();
    }
}