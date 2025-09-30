<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Venda;

class VendaModel extends Model
{
    protected $table            = 'vendas';
    protected $primaryKey       = 'id';
    protected $returnType       = Venda::class;
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;

    protected $allowedFields    = [
        'cliente_id',
        'produto',
        'valor',
        'status',
    ];

    protected $order            = ['created_at' => 'DESC'];

    protected $validationRules = [
        'cliente_id' => 'required|integer',
        'produto'    => 'required|min_length[2]|max_length[150]',
        'valor'      => 'required|decimal',
        'status'     => 'permit_empty|in_list[aberta,fechada,cancelada]',
    ];

    /**
     * Vendas por cliente
     */
    public function getByCliente(int $clienteId): array
    {
        return $this->where('cliente_id', $clienteId)->findAll();
    }

    /**
     * Total de vendas por status
     */
    public function getTotaisPorStatus(): array
    {
        return $this->select('status, COUNT(*) as total')
                    ->groupBy('status')
                    ->findAll();
    }
    /**
 * Retorna vendas com o nome do cliente
 */
public function getVendasComClientes()
{
    return $this->select('vendas.*, clientes.nome as cliente_nome')
                ->join('clientes', 'clientes.id = vendas.cliente_id')
                ->orderBy('vendas.created_at', 'DESC')
                ->findAll();
}

}