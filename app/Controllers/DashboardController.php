<?php

namespace App\Controllers;

use App\Models\VendaModel;
use App\Models\FinanceiroModel;
use App\Models\MensagemWhatsappModel;

class DashboardController extends BaseController
{
    protected $vendaModel;
    protected $financeiroModel;
    protected $mensagemModel;

    public function __construct()
    {
        $this->vendaModel = new VendaModel();
        $this->financeiroModel = new FinanceiroModel();
        $this->mensagemModel = new MensagemWhatsappModel();
    }

    public function index()
    {
        // Totais
        $totalVendas   = $this->vendaModel->countAllResults();
        $totalFinanceiro = $this->financeiroModel->countAllResults();
        $pendentes     = $this->mensagemModel->where('status', 'pendente')->countAllResults();

        // Gráfico de vendas por status
        $statusData = $this->vendaModel
            ->select("status, COUNT(*) as total")
            ->groupBy("status")
            ->findAll();

        $vendasLabels = array_column($statusData, 'status');
        $vendasValues = array_column($statusData, 'total');

        // Gráfico financeiro (entrada x saída)
        $financeiroData = $this->financeiroModel
            ->select("tipo, SUM(valor) as total")
            ->groupBy("tipo")
            ->findAll();

        $financeiroLabels = array_column($financeiroData, 'tipo');
        $financeiroValues = array_column($financeiroData, 'total');

        $data = [
            'totalVendas' => $totalVendas,
            'totalFinanceiro' => $totalFinanceiro,
            'pendentes' => $pendentes,
            'ultimasVendas' => $this->vendaModel->orderBy('created_at', 'DESC')->findAll(5),
            'ultimosLancamentos' => $this->financeiroModel->orderBy('created_at', 'DESC')->findAll(5),
            'ultimasMensagens' => $this->mensagemModel->orderBy('created_at', 'DESC')->findAll(5),
            'vendasLabels' => json_encode($vendasLabels),
            'vendasValues' => json_encode($vendasValues),
            'financeiroLabels' => json_encode($financeiroLabels),
            'financeiroValues' => json_encode($financeiroValues),
        ];

        return view('admin/dashboard/index', $data);
    }
}