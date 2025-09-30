<?php

namespace App\Controllers;

use App\Models\ClienteModel;

class ClienteController extends BaseController
{
    protected $clienteModel;

    public function __construct()
    {
        $this->clienteModel = new ClienteModel();
    }

    public function index()
    {
        $data['clientes'] = $this->clienteModel->findAll();
        return view('admin/clientes/index', $data);
    }

    public function create()
    {
        return view('admin/clientes/create');
    }

    public function store()
    {
        $data = $this->request->getPost();

        if (!$this->clienteModel->save($data)) {
            return redirect()->back()->with('errors', $this->clienteModel->errors());
        }

        return redirect()->to('/clientes')->with('success', 'Cliente cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $data['cliente'] = $this->clienteModel->find($id);
        return view('admin/clientes/edit', $data);
    }

    public function update($id)
    {
        $data = $this->request->getPost();

        $this->clienteModel->update($id, $data);

        return redirect()->to('/clientes')->with('success', 'Cliente atualizado.');
    }

    public function delete($id)
    {
        $this->clienteModel->delete($id);
        return redirect()->to('/clientes')->with('success', 'Cliente removido.');
    }
}