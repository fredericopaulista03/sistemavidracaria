<?php

namespace App\Controllers;

use App\Models\MensagemWhatsappModel;

class WhatsappController extends BaseController
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

    public function getConversa($numero)
    {
        $mensagens = $this->whatsappModel->getConversaByNumero($numero);
        return $this->response->setJSON($mensagens);
    }
}