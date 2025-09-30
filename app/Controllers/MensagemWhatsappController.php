<?php

namespace App\Controllers;

use App\Models\MensagemWhatsappModel;

class MensagemWhatsappController extends BaseController
{protected $whatsappModel;

    public function __construct()
    {
        $this->whatsappModel = new MensagemWhatsappModel();
    }

    /**
     * Webhook para receber mensagens
     */
    public function receive()
    {
        // Log do webhook recebido
        $rawInput = file_get_contents('php://input');
        $webhookData = json_decode($rawInput, true) ?? [];
        
        log_message('info', 'Webhook recebido: ' . print_r($webhookData, true));

        // Verifica se é uma mensagem válida
        if ($this->isValidMessage($webhookData)) {
            $result = $this->whatsappModel->saveWebhookMessage($webhookData);
            
            if ($result['success']) {
                log_message('info', 'Mensagem salva via webhook: ' . $result['message_id']);
                
                // Opcional: Processar mensagem recebida (IA, respostas automáticas, etc.)
                $this->processReceivedMessage($webhookData);
            } else {
                log_message('error', 'Erro ao salvar webhook: ' . $result['error']);
            }
        }

        return $this->response->setStatusCode(200)->setJSON(['status' => 'success']);
    }

    /**
     * Enviar mensagem via webhook externo
     */
    public function send()
    {
        $numero = $this->request->getPost('numero');
        $mensagem = $this->request->getPost('mensagem');
        $webhookUrl = getenv('WHATSAPP_SEND_WEBHOOK') ?? 'https://seu-webhook-envio.com/send';

        if (!$numero || !$mensagem) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'error' => 'Número e mensagem são obrigatórios'
            ]);
        }

        $result = $this->whatsappModel->sendViaWebhook($numero, $mensagem, $webhookUrl);

        return $this->response->setJSON($result);
    }

    /**
     * Verificar se é uma mensagem válida
     */
    private function isValidMessage(array $data): bool
    {
        return isset($data['type']) && 
               isset($data['from']) && 
               in_array($data['type'], ['text', 'image', 'video', 'audio', 'document']);
    }

    /**
     * Processar mensagem recebida (opcional)
     */
    private function processReceivedMessage(array $messageData)
    {
        // Exemplo: Resposta automática
        if ($messageData['type'] === 'text' && !($messageData['fromMe'] ?? false)) {
            $mensagem = strtolower($messageData['body'] ?? '');
            
            // Resposta automática para "ola" ou "oi"
            if (strpos($mensagem, 'ola') !== false || strpos($mensagem, 'oi') !== false) {
                $this->sendAutoReply(
                    $messageData['from'],
                    'Olá! Obrigado por entrar em contato. Em breve responderemos sua mensagem.'
                );
            }
        }
    }

    /**
     * Enviar resposta automática
     */
    private function sendAutoReply(string $to, string $message)
    {
        $webhookUrl = getenv('WHATSAPP_SEND_WEBHOOK') ?? 'https://seu-webhook-envio.com/send';
        $numero = $this->whatsappModel->extractNumber($to);
        
        $this->whatsappModel->sendViaWebhook($numero, $message, $webhookUrl);
    }

    /**
     * Listar conversas (para a interface)
     */
    public function conversas()
    {
        $data = [
            'totalConversas' => $this->whatsappModel->getTotalConversas(),
            'conversasAtivas' => $this->whatsappModel->getConversasAtivas(),
            'conversas' => $this->whatsappModel->getConversas(),
        ];

        return view('admin/whatsapp/conversas', $data);
    }

    /**
 * Buscar mensagens de uma conversa
 */
/**
 * Buscar mensagens de uma conversa
 */
public function getConversa($numero)
{
    try {
        $mensagens = $this->whatsappModel->where('numero', $numero)
                                        ->orderBy('created_at', 'ASC')
                                        ->findAll();

        // Converter as mensagens para array simples
        $mensagensArray = [];
        foreach ($mensagens as $msg) {
            $mensagensArray[] = [
                'id' => $msg->id,
                'numero' => $msg->numero,
                'mensagem' => $msg->mensagem,
                'status' => $msg->status,
                'provider_message_id' => $msg->provider_message_id,
                'sent_at' => $msg->sent_at,
                'received_at' => $msg->received_at,
                'type' => $msg->type,
                'from_me' => (bool)$msg->from_me,
                'chat_id' => $msg->chat_id,
                'direction' => $msg->direction,
                'created_at' => $msg->created_at,
                'updated_at' => $msg->updated_at,
                'nome' => $this->formatarNome($msg->numero)
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'messages' => $mensagensArray,
            'total' => count($mensagensArray)
        ]);
        
    } catch (\Exception $e) {
        log_message('error', 'Erro ao buscar conversa: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false,
            'error' => 'Erro interno ao carregar conversa'
        ]);
    }
}

/**
 * Formatar nome do contato
 */
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

    // No WhatsappWebhookController, adicione estes métodos:

/**
 * Testar recebimento de webhook
 */
public function testWebhook()
{
    // Dados de exemplo simulando uma mensagem recebida
    $testData = [
        'id' => 'test_' . time(),
        'from' => '5531999999999@c.us',
        'to' => '553191633453@c.us',
        'body' => 'Esta é uma mensagem de teste via webhook',
        'type' => 'text',
        'fromMe' => false,
        'chatId' => '5531999999999@c.us',
        'timestamp' => time()
    ];

    $result = $this->whatsappModel->saveWebhookMessage($testData);

    return $this->response->setJSON([
        'test_data' => $testData,
        'result' => $result,
        'message' => $result['success'] ? 'Webhook testado com sucesso!' : 'Erro no webhook: ' . $result['error']
    ]);
}

/**
 * Testar envio de mensagem
 */
public function testSend()
{
    $numero = '31999999999'; // Número de teste
    $mensagem = 'Esta é uma mensagem de teste enviada via webhook';
    $webhookUrl = getenv('WHATSAPP_SEND_WEBHOOK') ?? 'http://localhost:8080/message/sendText/vidracariabh';

    $result = $this->whatsappModel->sendViaWebhook($numero, $mensagem, $webhookUrl);

    return $this->response->setJSON([
        'test_data' => [
            'numero' => $numero,
            'mensagem' => $mensagem,
            'webhook_url' => $webhookUrl
        ],
        'result' => $result,
        'message' => $result['success'] ? 'Mensagem enviada com sucesso!' : 'Erro no envio: ' . $result['error']
    ]);
}

/**
 * Testar webhook manualmente via formulário
 */
public function testManual()
{
    return view('admin/whatsapp/test_webhook');
}
}