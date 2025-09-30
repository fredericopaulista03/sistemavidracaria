<?php

namespace App\Services;

use CodeIgniter\HTTP\CURLRequest;
use Config\Services;

class EvolutionApiService
{
    protected $client;
    protected $baseUrl;
    protected $apiKey;
    protected $instanceId;

    public function __construct()
    {
        $this->client = Services::curlrequest();
        $this->baseUrl = getenv('EVOLUTION_API_BASE_URL') ?? 'http://localhost:8080';
        $this->apiKey = getenv('EVOLUTION_API_KEY') ?? '';
        $this->instanceId = getenv('EVOLUTION_API_INSTANCE_ID') ?? '';
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
    public function getConnectionState()
    {
        return $this->request('GET', "/instance/connectionState/{$this->instanceId}");
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

        return $this->request('POST', "/message/sendText/{$this->instanceId}", $data);
    }

    /**
     * Send media message
     */
    public function sendMediaMessage(string $number, string $mediaUrl, string $caption = '')
    {
        $data = [
            'number' => $this->formatNumber($number),
            'media' => $mediaUrl,
            'caption' => $caption
        ];

        return $this->request('POST', "/message/sendMedia/{$this->instanceId}", $data);
    }

    /**
     * Get all chats
     */
    public function getAllChats()
    {
        return $this->request('GET', "/chat/findAllChats/{$this->instanceId}");
    }

    /**
     * Get chat messages
     */
    public function getChatMessages(string $number, int $limit = 50)
    {
        $number = $this->formatNumber($number);
        return $this->request('GET', "/message/findAllMessages/{$this->instanceId}?limit={$limit}&chatId={$number}@c.us");
    }

    /**
     * Get all messages
     */
    public function getAllMessages(int $limit = 100)
    {
        return $this->request('GET', "/message/findAllMessages/{$this->instanceId}?limit={$limit}");
    }

    /**
     * Mark message as read
     */
    public function markAsRead(string $messageId)
    {
        return $this->request('PUT', "/message/markAsRead/{$this->instanceId}", [
            'messageId' => $messageId
        ]);
    }

    /**
     * Check if number exists on WhatsApp
     */
    public function checkNumber(string $number)
    {
        $number = $this->formatNumber($number);
        return $this->request('POST', "/chat/checkNumber/{$this->instanceId}", [
            'number' => $number
        ]);
    }

    /**
     * Get profile picture
     */
    public function getProfilePicture(string $number)
    {
        $number = $this->formatNumber($number);
        return $this->request('GET', "/chat/getProfilePicture/{$this->instanceId}?number={$number}");
    }

    /**
     * Start instance
     */
    public function startInstance()
    {
        return $this->request('POST', "/instance/start/{$this->instanceId}");
    }

    /**
     * Restart instance
     */
    public function restartInstance()
    {
        return $this->request('POST', "/instance/restart/{$this->instanceId}");
    }

    /**
     * Logout instance
     */
    public function logoutInstance()
    {
        return $this->request('DELETE', "/instance/logout/{$this->instanceId}");
    }

    /**
     * Delete instance
     */
    public function deleteInstance()
    {
        return $this->request('DELETE', "/instance/delete/{$this->instanceId}");
    }

    /**
     * Make request to Evolution API
     */
    private function request(string $method, string $endpoint, array $data = [])
    {
        try {
            $options = [
                'headers' => [
                    'apikey' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'timeout' => 30,
                'http_errors' => false
            ];

            if (!empty($data)) {
                $options['json'] = $data;
            }

            $response = $this->client->request($method, $this->baseUrl . $endpoint, $options);
            
            $body = $response->getBody();
            $statusCode = $response->getStatusCode();

            $result = json_decode($body, true) ?? $body;

            return [
                'success' => $statusCode >= 200 && $statusCode < 300,
                'status' => $statusCode,
                'data' => $result,
                'error' => $statusCode >= 400 ? $result : null
            ];

        } catch (\Exception $e) {
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
}