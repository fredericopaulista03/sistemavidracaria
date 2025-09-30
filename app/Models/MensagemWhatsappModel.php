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
        'type',
        'from_me',
        'chat_id',
        'direction'
    ];

    protected $order            = ['created_at' => 'DESC'];

    protected $validationRules = [
        'numero'   => 'required|min_length[8]|max_length[20]',
        'mensagem' => 'required',
        'status'   => 'permit_empty|in_list[enviado,recebido,entregue,lida,erro]',
    ];

    /**
     * Salvar mensagem recebida via webhook
     */
    public function saveWebhookMessage(array $webhookData): array
    {
        try {
            $messageData = $this->parseWebhookData($webhookData);
            
            // Verifica se a mensagem já existe
            if ($messageData['provider_message_id']) {
                $existing = $this->where('provider_message_id', $messageData['provider_message_id'])->first();
                if ($existing) {
                    return ['success' => false, 'error' => 'Mensagem já existe'];
                }
            }

            if ($this->insert($messageData)) {
                return ['success' => true, 'message_id' => $this->getInsertID()];
            }

            return ['success' => false, 'error' => 'Erro ao salvar mensagem'];

        } catch (\Exception $e) {
            log_message('error', 'Erro ao salvar webhook: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Parse dos dados do webhook
     */
    private function parseWebhookData(array $data): array
    {
        $messageData = [
            'numero' => $this->extractNumber($data['from'] ?? ''),
            'mensagem' => $this->extractMessageText($data),
            'provider_message_id' => $data['id'] ?? null,
            'type' => $data['type'] ?? 'text',
            'from_me' => isset($data['fromMe']) ? ($data['fromMe'] ? 1 : 0) : 0,
            'chat_id' => $data['chatId'] ?? null,
            'direction' => ($data['fromMe'] ?? false) ? 'outgoing' : 'incoming',
            'status' => 'recebido',
            'received_at' => date('Y-m-d H:i:s')
        ];

        if ($messageData['from_me']) {
            $messageData['status'] = 'enviado';
            $messageData['sent_at'] = date('Y-m-d H:i:s');
        }

        return $messageData;
    }

    /**
     * Extrair número do formato do webhook
     */
    private function extractNumber(string $from): string
    {
        // Remove @c.us do final se existir
        return preg_replace('/@c\.us$/', '', $from);
    }

    /**
     * Extrair texto da mensagem
     */
    private function extractMessageText(array $data): string
    {
        if ($data['type'] === 'text' && isset($data['body'])) {
            return $data['body'];
        }

        if ($data['type'] === 'image' && isset($data['caption'])) {
            return '[Imagem] ' . $data['caption'];
        }

        if ($data['type'] === 'video' && isset($data['caption'])) {
            return '[Vídeo] ' . $data['caption'];
        }

        if ($data['type'] === 'document' && isset($data['fileName'])) {
            return '[Arquivo] ' . $data['fileName'];
        }

        return '[Mensagem ' . ($data['type'] ?? 'desconhecida') . ']';
    }

    /**
     * Enviar mensagem via webhook externo
     */
    public function sendViaWebhook(string $numero, string $mensagem, string $webhookUrl): array
    {
        try {
            $messageData = [
                'number' => $this->formatNumber($numero),
                'text' => $mensagem,
                'type' => 'text'
            ];

            $client = \Config\Services::curlrequest();
            
            $response = $client->post($webhookUrl, [
                'json' => $messageData,
                'timeout' => 30,
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                // Salva a mensagem enviada no banco
                $saved = $this->insert([
                    'numero' => $numero,
                    'mensagem' => $mensagem,
                    'status' => 'enviado',
                    'type' => 'text',
                    'from_me' => 1,
                    'direction' => 'outgoing',
                    'sent_at' => date('Y-m-d H:i:s')
                ]);

                return [
                    'success' => true,
                    'message_id' => $this->getInsertID(),
                    'response' => json_decode($response->getBody(), true)
                ];
            }

            return [
                'success' => false,
                'error' => 'Erro no webhook: ' . $response->getStatusCode()
            ];

        } catch (\Exception $e) {
            log_message('error', 'Erro ao enviar via webhook: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Formatar número para envio
     */
    private function formatNumber(string $number): string
    {
        $number = preg_replace('/\D/', '', $number);
        
        if (strlen($number) <= 11 && !str_starts_with($number, '55')) {
            $number = '55' . $number;
        }
        
        return $number . '@c.us';
    }

    // Mantenha os métodos existentes para compatibilidade
    public function getTotalConversas(): int
    {
        return $this->distinct()->select('numero')->countAllResults();
    }

    public function getConversasAtivas(): int
    {
        return $this->distinct()
                    ->select('numero')
                    ->where('status', 'recebido')
                    ->countAllResults();
    }

    public function getConversas(): array
    {
        $subquery = $this->db->table($this->table)
            ->select('numero, MAX(created_at) as ultima_atualizacao')
            ->groupBy('numero')
            ->get()
            ->getResultArray();

        $conversas = [];
        
        foreach ($subquery as $row) {
            $ultimaMensagem = $this->where('numero', $row['numero'])
                                  ->orderBy('created_at', 'DESC')
                                  ->first();

            if ($ultimaMensagem) {
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

        usort($conversas, function($a, $b) {
            return strtotime($b->ultima_atualizacao) - strtotime($a->ultima_atualizacao);
        });

        return $conversas;
    }

    private function formatarNome(string $numero): string
    {
        $numeroLimpo = preg_replace('/\D/', '', $numero);
        
        if (strlen($numeroLimpo) === 11) {
            return 'Contato (' . substr($numeroLimpo, 0, 2) . ') ' . 
                   substr($numeroLimpo, 2, 5) . '-' . 
                   substr($numeroLimpo, 7);
        }
        
        return 'Contato ' . $numeroLimpo;
    }

    private function truncarMensagem(string $mensagem, int $length = 50): string
    {
        if (strlen($mensagem) <= $length) {
            return $mensagem;
        }
        return substr($mensagem, 0, $length) . '...';
    }
}