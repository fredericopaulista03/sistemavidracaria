<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Entities\Usuario;

class UsuarioController extends BaseController
{
    protected $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new UsuarioModel();
    }

    public function index()
    {
        $data['usuarios'] = $this->usuarioModel->findAll();
        return view('usuarios/index', $data);
    }

    public function create()
    {
        return view('usuarios/create');
    }

    public function store()
    {
        $usuario = new Usuario($this->request->getPost());

        if (!$this->usuarioModel->save($usuario)) {
            return redirect()->back()->with('errors', $this->usuarioModel->errors());
        }

        return redirect()->to('/usuarios')->with('success', 'Usuário cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $data['usuario'] = $this->usuarioModel->find($id);
        return view('usuarios/edit', $data);
    }

    public function update($id)
    {
        $usuario = new Usuario($this->request->getPost());
        $usuario->id = $id;

        if (!$this->usuarioModel->save($usuario)) {
            return redirect()->back()->with('errors', $this->usuarioModel->errors());
        }

        return redirect()->to('/usuarios')->with('success', 'Usuário atualizado com sucesso.');
    }

    public function delete($id)
    {
        $this->usuarioModel->delete($id);
        return redirect()->to('/usuarios')->with('success', 'Usuário removido com sucesso.');
    }
}