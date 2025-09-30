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
        // Busca mensagens em tempo real
        $realtimeMessages = $this->whatsappModel->getRealtimeMessages($numero);
        
        // Se não encontrou mensagens em tempo real, busca do banco
        if (empty($realtimeMessages)) {
            $realtimeMessages = $this->whatsappModel->getConversaByNumero($numero);
        }

        return $this->response->setJSON($realtimeMessages);
    }

    public function sendMessage()
    {
        $numero = $this->request->getPost('numero');
        $mensagem = $this->request->getPost('mensagem');

        $result = $this->whatsappModel->sendMessage($numero, $mensagem);

        if ($result['success']) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Mensagem enviada com sucesso',
                'message_id' => $result['message_id']
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'error' => $result['error']
        ]);
    }

    public function syncConversas()
    {
        $result = $this->whatsappModel->syncConversas();

        return $this->response->setJSON($result);
    }

    public function checkConnection()
    {
        $result = $this->whatsappModel->checkConnection();
        return $this->response->setJSON($result);
    }
}