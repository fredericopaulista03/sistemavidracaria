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
        $data = [
            'totalVendas'   => $this->vendaModel->countAllResults(),
            'totalFinanceiro' => $this->financeiroModel->countAllResults(),
            'pendentes'     => $this->mensagemModel->where('status', 'pendente')->countAllResults(),
            'ultimasVendas' => $this->vendaModel->orderBy('created_at', 'DESC')->findAll(5),
            'ultimosLancamentos' => $this->financeiroModel->orderBy('created_at', 'DESC')->findAll(5),
            'ultimasMensagens' => $this->mensagemModel->orderBy('created_at', 'DESC')->findAll(5),
        ];

        return view('admin/dashboard/index', $data);
    }
}