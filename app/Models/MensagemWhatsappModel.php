<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\MensagemWhatsapp;
use App\Services\EvolutionApiService;

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
        'type',
        'from_me',
        'chat_id'
    ];

    protected $order            = ['created_at' => 'DESC'];

    protected $validationRules = [
        'numero'   => 'required|min_length[8]|max_length[20]',
        'mensagem' => 'required',
        'status'   => 'permit_empty|in_list[enviado,recebido,erro,pending,aguardando,lida,entregue]',
    ];

    protected $evolutionApi;

    public function __construct()
    {
        parent::__construct();
        $this->evolutionApi = new EvolutionApiService();
    }

    /**
     * Sincronizar conversas da Evolution API
     */
    public function syncConversas(): array
    {
        try {
            // Primeiro, busca todos os chats
            $chatsResult = $this->evolutionApi->getAllChats();
            
            if (!$chatsResult['success']) {
                return [
                    'success' => false, 
                    'error' => 'Falha ao buscar chats: ' . ($chatsResult['error'] ?? 'Unknown error')
                ];
            }

            $chats = $chatsResult['data'];
            $syncedCount = 0;
            $totalMessages = 0;

            log_message('info', 'Iniciando sincronização de ' . count($chats) . ' chats');

            foreach ($chats as $chat) {
                // Extrai o número do chat ID
                $numero = $this->extractNumberFromChatId($chat['id']);
                
                if (!$numero) continue;

                // Busca mensagens do chat específico
                $messagesResult = $this->evolutionApi->getChatMessages($numero, 100);
                
                if ($messagesResult['success'] && is_array($messagesResult['data'])) {
                    foreach ($messagesResult['data'] as $message) {
                        if ($this->syncMessage($message)) {
                            $syncedCount++;
                        }
                        $totalMessages++;
                    }
                    log_message('info', "Chat {$numero}: " . count($messagesResult['data']) . " mensagens processadas");
                } else {
                    log_message('warning', "Falha ao buscar mensagens do chat: {$numero}");
                }
            }

            return [
                'success' => true, 
                'synced' => $syncedCount,
                'total_messages' => $totalMessages,
                'total_chats' => count($chats),
                'message' => "Sincronização concluída: {$syncedCount} novas mensagens de " . count($chats) . " chats"
            ];

        } catch (\Exception $e) {
            log_message('error', 'Erro na sincronização: ' . $e->getMessage());
            return [
                'success' => false, 
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Buscar chats diretamente da API
     */
    public function getChatsFromApi(): array
    {
        $result = $this->evolutionApi->getAllChats();
        
        if ($result['success']) {
            $chats = [];
            foreach ($result['data'] as $chat) {
                $numero = $this->extractNumberFromChatId($chat['id']);
                $chats[] = [
                    'id' => $chat['id'],
                    'numero' => $numero,
                    'nome' => $this->formatarNome($numero),
                    'unread_count' => $chat['unreadCount'] ?? 0,
                    'is_group' => strpos($chat['id'], '@g.us') !== false
                ];
            }
            return ['success' => true, 'chats' => $chats];
        }
        
        return ['success' => false, 'error' => $result['error']];
    }

    /**
     * Sincronizar mensagem individual
     */
    private function syncMessage(array $message): bool
    {
        try {
            // Verifica se a mensagem tem a estrutura básica
            if (!isset($message['key']['id']) || !isset($message['key']['remoteJid'])) {
                return false;
            }

            $messageId = $message['key']['id'];
            $chatId = $message['key']['remoteJid'];

            // Verifica se a mensagem já existe
            $existing = $this->where('provider_message_id', $messageId)->first();

            if ($existing) {
                return false; // Já existe, não precisa sincronizar
            }

            // Prepara os dados da mensagem
            $messageData = [
                'numero' => $this->extractNumberFromChatId($chatId),
                'mensagem' => $this->extractMessageText($message),
                'provider_message_id' => $messageId,
                'chat_id' => $chatId,
                'type' => $this->getMessageType($message),
                'from_me' => $message['key']['fromMe'] ? 1 : 0,
                'status' => $this->mapMessageStatus($message),
                'sent_at' => date('Y-m-d H:i:s', $message['messageTimestamp']),
                'received_at' => date('Y-m-d H:i:s')
            ];

            // Insere no banco
            return $this->insert($messageData) !== false;

        } catch (\Exception $e) {
            log_message('error', 'Erro ao sincronizar mensagem: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Extrai número do ID do chat
     */
    private function extractNumberFromChatId(string $chatId): string
    {
        $parts = explode('@', $chatId);
        return $parts[0] ?? $chatId;
    }

    /**
     * Extrai texto da mensagem
     */
    private function extractMessageText(array $message): string
    {
        if (isset($message['message']['conversation'])) {
            return $message['message']['conversation'];
        }
        
        if (isset($message['message']['extendedTextMessage']['text'])) {
            return $message['message']['extendedTextMessage']['text'];
        }
        
        if (isset($message['message']['imageMessage']['caption'])) {
            return '[Imagem] ' . $message['message']['imageMessage']['caption'];
        }
        
        if (isset($message['message']['videoMessage']['caption'])) {
            return '[Vídeo] ' . $message['message']['videoMessage']['caption'];
        }
        
        if (isset($message['message']['documentMessage']['fileName'])) {
            return '[Arquivo] ' . $message['message']['documentMessage']['fileName'];
        }
        
        return '[Mídia]';
    }

    /**
     * Obtém tipo da mensagem
     */
    private function getMessageType(array $message): string
    {
        if (isset($message['message']['conversation'])) return 'text';
        if (isset($message['message']['extendedTextMessage'])) return 'text';
        if (isset($message['message']['imageMessage'])) return 'image';
        if (isset($message['message']['videoMessage'])) return 'video';
        if (isset($message['message']['audioMessage'])) return 'audio';
        if (isset($message['message']['documentMessage'])) return 'document';
        if (isset($message['message']['stickerMessage'])) return 'sticker';
        return 'unknown';
    }

    /**
     * Mapeia status da mensagem
     */
    private function mapMessageStatus(array $message): string
    {
        $status = $message['status'] ?? 'PENDING';
        
        switch ($status) {
            case 'READ': return 'lida';
            case 'DELIVERED': return 'entregue';
            case 'SENT': return 'enviado';
            case 'PENDING': return 'pending';
            default: return 'recebido';
        }
    }

    // Mantenha os outros métodos existentes (getTotalConversas, getConversas, etc.)
    // ... [seus métodos anteriores aqui]
}