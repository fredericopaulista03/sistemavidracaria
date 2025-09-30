<?php

namespace App\Services;

use CodeIgniter\HTTP\CURLRequest;
use Config\Services;

class EvolutionApiService
{
    protected $client;
    protected $baseUrl;
    protected $apiKey;
    protected $instanceName;

    // No EvolutionApiService, adicione este método:
public function debugInstance()
{
    $instances = $this->getInstanceInfo();
    
    log_message('debug', 'Instâncias disponíveis: ' . print_r($instances, true));
    log_message('debug', 'Instância configurada: ' . $this->instanceName);
    
    return $instances;
}

// E atualize o construtor para log:
public function __construct()
{
    $this->client = Services::curlrequest();
    $this->baseUrl = getenv('EVOLUTION_API_BASE_URL') ?: 'http://localhost:8080';
    $this->apiKey = getenv('EVOLUTION_API_KEY') ?: 'sua-api-key-aqui';
    $this->instanceName = getenv('EVOLUTION_API_INSTANCE_NAME') ?: 'vidracariabh'; // Mudei para vidracariabh
    
    // Remove barras finais da URL base
    $this->baseUrl = rtrim($this->baseUrl, '/');
    
    log_message('debug', "Evolution API Config - URL: {$this->baseUrl}, Instance: {$this->instanceName}");
}

    /**
     * Get instance information
     */
    public function getInstanceInfo()
    {
        return $this->request('GET', "/instance/fetchInstances");
    }

    /**
     * Get connection state
     */
   /**
 * Get connection state - ENDPOINT CORRIGIDO
 */
public function getConnectionState()
{
    // Tente este endpoint primeiro (mais comum)
    $result1 = $this->request('GET', "/instance/connectionState/{$this->instanceName}");
    
    if ($result1['success']) {
        return $result1;
    }
    
    // Se falhar, tente endpoint alternativo
    log_message('debug', 'Tentando endpoint alternativo para connection state...');
    return $this->request('GET', "/instance/show/{$this->instanceName}");
}

    /**
     * Send text message
     */
    public function sendTextMessage(string $number, string $message, bool $delay = true)
    {
        $data = [
            'number' => $this->formatNumber($number),
            'text' => $message,
            'delay' => $delay ? 1200 : 100
        ];

        return $this->request('POST', "/message/sendText/{$this->instanceName}", $data);
    }

    /**
     * Get all chats
     */
    public function getAllChats()
    {
        return $this->request('GET', "/chat/findAllChats/{$this->instanceName}");
    }

    /**
     * Get chat messages
     */
    public function getChatMessages(string $number, int $limit = 50)
    {
        $number = $this->formatNumber($number);
        return $this->request('GET', "/message/findAllMessages/{$this->instanceName}?limit={$limit}&chatId={$number}@c.us");
    }

    /**
     * Get all messages
     */
    public function getAllMessages(int $limit = 100)
    {
        return $this->request('GET', "/message/findAllMessages/{$this->instanceName}?limit={$limit}");
    }

    /**
     * Check if number exists on WhatsApp
     */
    public function checkNumber(string $number)
    {
        $number = $this->formatNumber($number);
        return $this->request('POST', "/chat/checkNumber/{$this->instanceName}", [
            'number' => $number
        ]);
    }

    /**
     * Get profile picture
     */
    public function getProfilePicture(string $number)
    {
        $number = $this->formatNumber($number);
        return $this->request('GET', "/chat/getProfilePicture/{$this->instanceName}?number={$number}");
    }

    /**
     * Start instance
     */
    public function startInstance()
    {
        return $this->request('POST', "/instance/start/{$this->instanceName}");
    }

    /**
     * Restart instance
     */
    public function restartInstance()
    {
        return $this->request('POST', "/instance/restart/{$this->instanceName}");
    }

    /**
     * Logout instance
     */
    public function logoutInstance()
    {
        return $this->request('DELETE', "/instance/logout/{$this->instanceName}");
    }

    /**
     * Delete instance
     */
    public function deleteInstance()
    {
        return $this->request('DELETE', "/instance/delete/{$this->instanceName}");
    }

    /**
     * Create instance
     */
    public function createInstance(string $instanceName, string $qrcode = 'true')
    {
        return $this->request('POST', "/instance/create", [
            'instanceName' => $instanceName,
            'qrcode' => $qrcode
        ]);
    }

    /**
     * Make request to Evolution API
     */
    private function request(string $method, string $endpoint, array $data = [])
    {
        try {
            $url = $this->baseUrl . $endpoint;
            
            $options = [
                'headers' => [
                    'apikey' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
                'http_errors' => false,
                'verify' => false // Desativa verificação SSL para desenvolvimento
            ];

            if (!empty($data)) {
                $options['json'] = $data;
                $options['headers']['Content-Type'] = 'application/json';
            }

            log_message('debug', "Evolution API Request: {$method} {$url}");

            $response = $this->client->request($method, $url, $options);
            
            $body = $response->getBody();
            $statusCode = $response->getStatusCode();

            log_message('debug', "Evolution API Response: {$statusCode} - {$body}");

            $result = json_decode($body, true) ?? $body;

            return [
                'success' => $statusCode >= 200 && $statusCode < 300,
                'status' => $statusCode,
                'data' => $result,
                'error' => $statusCode >= 400 ? ($result['message'] ?? $result['error'] ?? 'Unknown error') : null
            ];

        } catch (\Exception $e) {
            log_message('error', 'Evolution API Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'status' => 500,
                'data' => null,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Format phone number for WhatsApp
     */
    private function formatNumber(string $number): string
    {
        // Remove todos os caracteres não numéricos
        $number = preg_replace('/\D/', '', $number);
        
        // Se não tiver código do país, adiciona 55 (Brasil)
        if (strlen($number) <= 11 && !str_starts_with($number, '55')) {
            $number = '55' . $number;
        }
        
        return $number;
    }

    /**
     * Test API connection
     */
    public function testConnection(): array
    {
        $result = $this->getInstanceInfo();
        
        if ($result['success']) {
            return [
                'success' => true,
                'message' => 'Conexão com Evolution API estabelecida com sucesso',
                'instances' => $result['data']
            ];
        }
        
        return [
            'success' => false,
            'error' => 'Não foi possível conectar à Evolution API: ' . ($result['error'] ?? 'Unknown error')
        ];
    }
    
}