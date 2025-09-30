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
    public function testConnection()
{
    $evolutionApi = new \App\Services\EvolutionApiService();
    $result = $evolutionApi->testConnection();
    
    return $this->response->setJSON($result);
}

/**
 * Sincronização mínima para teste
 */
public function syncTest()
{
    try {
        log_message('debug', 'syncTest method called');
        $result = $this->whatsappModel->syncTest();
        
        log_message('debug', 'Sync test result: ' . print_r($result, true));
        return $this->response->setJSON($result);
        
    } catch (\Exception $e) {
        log_message('error', 'Exception in syncTest: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}
public function testEndpoints()
{
    $evolutionApi = new \App\Services\EvolutionApiService();
    
    $results = [];
    
    // Teste 1: Connection State
    $results['connection_state'] = $evolutionApi->getConnectionState();
    
    // Teste 2: Listar chats
    $results['all_chats'] = $evolutionApi->getAllChats();
    
    // Teste 3: Se tiver chats, teste um específico
    if ($results['all_chats']['success'] && !empty($results['all_chats']['data'])) {
        $firstChat = $results['all_chats']['data'][0];
        $numero = $evolutionApi->extractNumberFromChatId($firstChat['id'] ?? '');
        if ($numero) {
            $results['chat_messages'] = $evolutionApi->getChatMessages($numero, 1);
        }
    }
    
    return $this->response->setJSON($results);
}

}