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
public function syncTest(): array
{
    try {
        // Busca apenas os primeiros 5 chats
        $chatsResult = $this->evolutionApi->getAllChats();
        
        if (!$chatsResult['success']) {
            return $chatsResult;
        }

        $chats = array_slice($chatsResult['data'], 0, 5);
        $syncedCount = 0;

        foreach ($chats as $chat) {
            $chatId = $chat['id'] ?? '';
            $numero = $this->extractNumberFromChatId($chatId);
            
            if (!$numero) continue;

            // Busca apenas as últimas 5 mensagens de cada chat
            $messagesResult = $this->evolutionApi->getChatMessages($numero, 5);
            
            if ($messagesResult['success'] && is_array($messagesResult['data'])) {
                foreach ($messagesResult['data'] as $message) {
                    if ($this->syncMessage($message)) {
                        $syncedCount++;
                    }
                }
            }
        }

        return [
            'success' => true,
            'synced' => $syncedCount,
            'message' => "Sincronização teste: {$syncedCount} mensagens de " . count($chats) . " chats"
        ];

    } catch (\Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}
}