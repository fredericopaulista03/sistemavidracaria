<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Financeiro;

class FinanceiroModel extends Model
{
    protected $table            = 'financeiro';
    protected $primaryKey       = 'id';
    protected $returnType       = Financeiro::class;
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;

    protected $allowedFields    = [
        'tipo',
        'descricao',
        'valor',
        'data',
    ];

    protected $order            = ['data' => 'DESC'];

    protected $validationRules = [
        'tipo'      => 'required|in_list[entrada,saida]',
        'descricao' => 'required|min_length[3]|max_length[255]',
        'valor'     => 'required|decimal',
        'data'      => 'required|valid_date',
    ];

    /**
     * Totalizar entradas/saídas
     */
    public function getResumo(): array
{
    return [
        'entradas' => $this->where('tipo', 'entrada')->selectSum('valor')->first()->valor ?? 0,
        'saidas'   => $this->where('tipo', 'saida')->selectSum('valor')->first()->valor ?? 0,
    ];
}
}