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
        // Sincroniza conversas ao carregar a página
        $syncResult = $this->whatsappModel->syncConversas();

        $data = [
            'totalConversas' => $this->whatsappModel->getTotalConversas(),
            'conversasAtivas' => $this->whatsappModel->getConversasAtivas(),
            'conversas' => $this->whatsappModel->getConversas(),
            'connectionStatus' => $this->whatsappModel->checkConnection(),
            'syncResult' => $syncResult
        ];

        return view('admin/whatsapp/conversas', $data);
    }

    public function getConversa($numero)
    {
        // Busca mensagens em tempo real e sincroniza
        $realtimeMessages = $this->whatsappModel->getRealtimeMessages($numero);
        
        return $this->response->setJSON([
            'success' => true,
            'messages' => $realtimeMessages
        ]);
    }

    public function sendMessage()
    {
        $numero = $this->request->getPost('numero');
        $mensagem = $this->request->getPost('mensagem');

        $result = $this->whatsappModel->sendMessage($numero, $mensagem);

        return $this->response->setJSON($result);
    }

    public function syncConversas()
    {
        $result = $this->whatsappModel->syncConversas();

         // Log para debug
        log_message('debug', 'Resultado da sincronização: ' . print_r($result, true));

        return $this->response->setJSON($result);
    }

    public function checkConnection()
    {
        $result = $this->whatsappModel->checkConnection();
        return $this->response->setJSON($result);
    }

    public function getChats()
    {
        $result = $this->whatsappModel->getChatsFromApi();
        return $this->response->setJSON($result);
    }
}