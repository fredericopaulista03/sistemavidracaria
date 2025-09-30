<?php

namespace App\Controllers;

use App\Models\FinanceiroModel;
use App\Entities\Financeiro;

class FinanceiroController extends BaseController
{
    protected $financeiroModel;

    public function __construct()
    {
        $this->financeiroModel = new FinanceiroModel();
    }

    public function index()
    {
        $data['lancamentos'] = $this->financeiroModel->findAll();
        $data['resumo'] = $this->financeiroModel->getResumo();
        return view('financeiro/index', $data);
    }

    public function create()
    {
        return view('financeiro/create');
    }

    public function store()
    {
        $lancamento = new Financeiro($this->request->getPost());

        if (!$this->financeiroModel->save($lancamento)) {
            return redirect()->back()->with('errors', $this->financeiroModel->errors());
        }

        return redirect()->to('/financeiro')->with('success', 'Lançamento registrado com sucesso.');
    }

    public function delete($id)
    {
        $this->financeiroModel->delete($id);
        return redirect()->to('/financeiro')->with('success', 'Lançamento excluído.');
    }
}