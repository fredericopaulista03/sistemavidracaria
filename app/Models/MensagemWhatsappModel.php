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
    ];

    protected $order            = ['created_at' => 'DESC'];

    protected $validationRules = [
        'numero'   => 'required|min_length[8]|max_length[20]',
        'mensagem' => 'required',
        'status'   => 'permit_empty|in_list[enviado,recebido,erro,pending,aguardando]',
    ];

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
                // Conta mensagens não lidas (ajuste conforme sua lógica)
                $nao_lidas = $this->where('numero', $row['numero'])
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
     * Formatar número para nome (exemplo simples)
     */
    private function formatarNome(string $numero): string
    {
        // Aqui você pode implementar uma lógica para buscar o nome do contato
        // Por enquanto, vamos usar o próprio número formatado
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