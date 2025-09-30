<?php

namespace App\Controllers;

use App\Models\VendaModel;
use App\Entities\Venda;

class VendaController extends BaseController
{
    protected $vendaModel;

    public function __construct()
    {
        $this->vendaModel = new VendaModel();
    }

    public function index()
    {
        $data['vendas'] = $this->vendaModel->findAll();
        $data['totais'] = $this->vendaModel->getTotaisPorStatus();
        return view('vendas/index', $data);
    }

    public function create()
    {
        return view('vendas/create');
    }

    public function store()
    {
        $venda = new Venda($this->request->getPost());

        if (!$this->vendaModel->save($venda)) {
            return redirect()->back()->with('errors', $this->vendaModel->errors());
        }

        return redirect()->to('/vendas')->with('success', 'Venda registrada com sucesso.');
    }

    public function delete($id)
    {
        $this->vendaModel->delete($id);
        return redirect()->to('/vendas')->with('success', 'Venda removida.');
    }
}