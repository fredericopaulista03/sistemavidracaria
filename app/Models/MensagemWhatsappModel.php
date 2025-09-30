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
        'from_me'
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
        $result = $this->evolutionApi->getAllChats();
        
        if (!$result['success']) {
            return ['success' => false, 'error' => $result['error']];
        }

        $chats = $result['data'];
        $syncedCount = 0;

        foreach ($chats as $chat) {
            // Busca mensagens do chat
            $messagesResult = $this->evolutionApi->getChatMessages($chat['id']);
            
            if ($messagesResult['success'] && is_array($messagesResult['data'])) {
                foreach ($messagesResult['data'] as $message) {
                    $this->syncMessage($message);
                    $syncedCount++;
                }
            }
        }

        return ['success' => true, 'synced' => $syncedCount];
    }

    /**
     * Sincronizar mensagem individual
     */
    private function syncMessage(array $message): void
    {
        // Verifica se a mensagem já existe
        $existing = $this->where('provider_message_id', $message['key']['id'])->first();

        if (!$existing) {
            $messageData = [
                'numero' => $this->extractNumber($message['key']['remoteJid']),
                'mensagem' => $message['message']['conversation'] ?? 
                             $message['message']['extendedTextMessage']['text'] ?? 
                             '[Mídia] ' . ($message['message']['imageMessage']['caption'] ?? ''),
                'provider_message_id' => $message['key']['id'],
                'type' => $this->getMessageType($message),
                'from_me' => $message['key']['fromMe'] ? 1 : 0,
                'status' => $this->mapMessageStatus($message),
                'sent_at' => date('Y-m-d H:i:s', $message['messageTimestamp']),
                'received_at' => date('Y-m-d H:i:s')
            ];

            $this->insert($messageData);
        }
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

            return ['success' => true, 'message_id' => $this->getInsertID()];
        }

        return ['success' => false, 'error' => $result['error']];
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

        return [];
    }

    /**
     * Verificar estado da conexão
     */
    public function checkConnection(): array
    {
        return $this->evolutionApi->getConnectionState();
    }

    /**
     * Extrai número do JID
     */
    private function extractNumber(string $jid): string
    {
        return explode('@', $jid)[0];
    }

    /**
     * Obtém tipo da mensagem
     */
    private function getMessageType(array $message): string
    {
        if (isset($message['message']['conversation'])) return 'text';
        if (isset($message['message']['imageMessage'])) return 'image';
        if (isset($message['message']['videoMessage'])) return 'video';
        if (isset($message['message']['audioMessage'])) return 'audio';
        if (isset($message['message']['documentMessage'])) return 'document';
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
     * Formata mensagem para exibição
     */
    private function formatMessageForDisplay(array $message): array
    {
        return [
            'id' => $message['key']['id'],
            'numero' => $this->extractNumber($message['key']['remoteJid']),
            'mensagem' => $message['message']['conversation'] ?? 
                         $message['message']['extendedTextMessage']['text'] ?? 
                         '[Mídia]',
            'type' => $this->getMessageType($message),
            'from_me' => $message['key']['fromMe'],
            'timestamp' => $message['messageTimestamp'],
            'status' => $this->mapMessageStatus($message)
        ];
    }

    // Mantenha os métodos existentes (getTotalConversas, getConversasAtivas, etc.)
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
        // Aqui você pode integrar com uma API de busca de contatos
        return 'Contato ' . preg_replace('/\D/', '', $numero);
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
}