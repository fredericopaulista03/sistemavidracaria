<?php

namespace App\Controllers;

use App\Models\MensagemWhatsappModel;

class MensagemWhatsappController extends BaseController
{
    protected $whatsappModel;

    public function __construct()
    {
        $this->whatsappModel = new MensagemWhatsappModel();
    }

    public function conversas()
    {
        $data = [
            'totalConversas' => $this->whatsappModel->getTotalConversas(),
            'conversasAtivas' => $this->whatsappModel->getConversasAtivas(),
            'conversas' => $this->whatsappModel->getConversas()
        ];

        return view('admin/whatsapp/conversas', $data);
    }
}