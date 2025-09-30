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
        // Primeiro, verifica a conexão
        $connectionResult = $this->evolutionApi->getConnectionState();
        
        if (!$connectionResult['success']) {
            return [
                'success' => false, 
                'error' => 'Instância não conectada: ' . ($connectionResult['error'] ?? 'Verifique o QR Code')
            ];
        }

        log_message('info', 'Conexão com Evolution API verificada, iniciando sincronização...');

        // Busca todos os chats
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

        log_message('info', 'Encontrados ' . count($chats) . ' chats para sincronizar');

        // Limita para os primeiros 10 chats para teste
        $testChats = array_slice($chats, 0, 10);
        
        foreach ($testChats as $chat) {
            $chatId = $chat['id'] ?? '';
            
            if (!$chatId) continue;

            // Extrai o número do chat ID
            $numero = $this->extractNumberFromChatId($chatId);
            
            if (!$numero) continue;

            log_message('info', "Sincronizando chat: {$numero}");

            // Busca mensagens do chat específico (limita para 20 mensagens por chat)
            $messagesResult = $this->evolutionApi->getChatMessages($numero, 20);
            
            if ($messagesResult['success'] && is_array($messagesResult['data'])) {
                $messageCount = count($messagesResult['data']);
                log_message('info', "Chat {$numero}: {$messageCount} mensagens encontradas");
                
                foreach ($messagesResult['data'] as $message) {
                    if ($this->syncMessage($message)) {
                        $syncedCount++;
                    }
                    $totalMessages++;
                }
            } else {
                log_message('warning', "Falha ao buscar mensagens do chat: {$numero} - " . ($messagesResult['error'] ?? ''));
            }
        }

        return [
            'success' => true, 
            'synced' => $syncedCount,
            'total_messages' => $totalMessages,
            'total_chats' => count($chats),
            'message' => "Sincronização concluída: {$syncedCount} novas mensagens de " . count($testChats) . " chats (total: " . count($chats) . " chats)"
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

    /**
     * Total de conversas únicas
     */
    public function getTotalConversas(): int
    {
        return $this->distinct()
                    ->select('numero')
                    ->countAllResults();
    }

    /**
     * Conversas aguardando resposta
     */
    public function getConversasAtivas(): int
    {
        return $this->distinct()
                    ->select('numero')
                    ->where('status', 'aguardando')
                    ->orWhere('status', 'pending')
                    ->countAllResults();
    }

    /**
     * Lista de conversas com última mensagem
     */
    public function getConversas(): array
    {
        // Busca o último registro de cada número
        $subquery = $this->db->table($this->table)
            ->select('numero, MAX(created_at) as ultima_atualizacao')
            ->groupBy('numero')
            ->get()
            ->getResultArray();

        $conversas = [];
        
        foreach ($subquery as $row) {
            // Busca a última mensagem de cada número
            $ultimaMensagem = $this->where('numero', $row['numero'])
                                  ->orderBy('created_at', 'DESC')
                                  ->first();

            if ($ultimaMensagem) {
                // Conta mensagens não lidas
                $nao_lidas = $this->where('numero', $row['numero'])
                                 ->where('from_me', 0)
                                 ->where('status', 'recebido')
                                 ->countAllResults();

                $conversas[] = (object)[
                    'id' => $ultimaMensagem->id,
                    'numero' => $ultimaMensagem->numero,
                    'nome' => $this->formatarNome($ultimaMensagem->numero),
                    'ultima_mensagem' => $this->truncarMensagem($ultimaMensagem->mensagem),
                    'ultima_atualizacao' => $ultimaMensagem->created_at,
                    'nao_lidas' => $nao_lidas,
                    'status' => $ultimaMensagem->status
                ];
            }
        }

        // Ordena por data mais recente
        usort($conversas, function($a, $b) {
            return strtotime($b->ultima_atualizacao) - strtotime($a->ultima_atualizacao);
        });

        return $conversas;
    }

    /**
     * Formatar número para nome
     */
    private function formatarNome(string $numero): string
    {
        // Remove caracteres não numéricos
        $numeroLimpo = preg_replace('/\D/', '', $numero);
        
        // Se for número brasileiro, formata
        if (strlen($numeroLimpo) === 11 || strlen($numeroLimpo) === 10) {
            if (strlen($numeroLimpo) === 11) {
                return 'Contato (' . substr($numeroLimpo, 0, 2) . ') ' . 
                       substr($numeroLimpo, 2, 5) . '-' . 
                       substr($numeroLimpo, 7);
            } else {
                return 'Contato (' . substr($numeroLimpo, 0, 2) . ') ' . 
                       substr($numeroLimpo, 2, 4) . '-' . 
                       substr($numeroLimpo, 6);
            }
        }
        
        return 'Contato ' . $numeroLimpo;
    }

    /**
     * Truncar mensagem para preview
     */
    private function truncarMensagem(string $mensagem, int $length = 50): string
    {
        if (strlen($mensagem) <= $length) {
            return $mensagem;
        }
        return substr($mensagem, 0, $length) . '...';
    }

    /**
     * Buscar histórico de uma conversa
     */
    public function getConversaByNumero(string $numero): array
    {
        return $this->where('numero', $numero)
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }

    /**
     * Buscar mensagens em tempo real de um número
     */
    public function getRealtimeMessages(string $numero): array
    {
        $result = $this->evolutionApi->getChatMessages($numero);
        
        if ($result['success']) {
            $messages = [];
            foreach ($result['data'] as $message) {
                $this->syncMessage($message);
                $messages[] = $this->formatMessageForDisplay($message);
            }
            return $messages;
        }

        // Se falhar na API, busca do banco
        return $this->getConversaByNumero($numero);
    }

    /**
     * Formata mensagem para exibição
     */
    private function formatMessageForDisplay(array $message): array
    {
        return [
            'id' => $message['key']['id'],
            'numero' => $this->extractNumberFromChatId($message['key']['remoteJid']),
            'mensagem' => $this->extractMessageText($message),
            'type' => $this->getMessageType($message),
            'from_me' => (bool)$message['key']['fromMe'],
            'timestamp' => $message['messageTimestamp'],
            'status' => $this->mapMessageStatus($message),
            'formatted_time' => date('d/m/Y H:i', $message['messageTimestamp'])
        ];
    }

    /**
     * Enviar mensagem via Evolution API
     */
    public function sendMessage(string $numero, string $mensagem): array
    {
        $result = $this->evolutionApi->sendTextMessage($numero, $mensagem);

        if ($result['success']) {
            // Salva no banco de dados
            $messageData = [
                'numero' => $numero,
                'mensagem' => $mensagem,
                'provider_message_id' => $result['data']['key']['id'] ?? null,
                'type' => 'text',
                'from_me' => 1,
                'status' => 'enviado',
                'sent_at' => date('Y-m-d H:i:s')
            ];

            $this->insert($messageData);

            return [
                'success' => true, 
                'message_id' => $this->getInsertID(),
                'provider_message_id' => $result['data']['key']['id'] ?? null
            ];
        }

        return [
            'success' => false, 
            'error' => $result['error'] ?? 'Erro desconhecido ao enviar mensagem'
        ];
    }

    /**
     * Verificar estado da conexão
     */
    public function checkConnection(): array
    {
        return $this->evolutionApi->getConnectionState();
    }

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

    /**
     * Marcar mensagens como lidas
     */
    public function markAsRead(string $numero): bool
    {
        return $this->where('numero', $numero)
                   ->where('from_me', 0)
                   ->where('status', 'recebido')
                   ->set(['status' => 'lida'])
                   ->update();
    }

    /**
     * Buscar conversas não lidas
     */
    public function getConversasNaoLidas(): array
    {
        $subquery = $this->db->table($this->table)
            ->select('numero, COUNT(*) as nao_lidas')
            ->where('from_me', 0)
            ->where('status', 'recebido')
            ->groupBy('numero')
            ->get()
            ->getResultArray();

        $conversas = [];
        foreach ($subquery as $row) {
            $conversas[] = (object)[
                'numero' => $row['numero'],
                'nao_lidas' => $row['nao_lidas'],
                'nome' => $this->formatarNome($row['numero'])
            ];
        }

        return $conversas;
    }
}