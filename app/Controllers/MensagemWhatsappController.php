<?php

namespace App\Controllers;

use App\Models\MensagemWhatsappModel;
use App\Entities\MensagemWhatsapp;

class MensagemWhatsappController extends BaseController
{
    protected $mensagemModel;

    public function __construct()
    {
        $this->mensagemModel = new MensagemWhatsappModel();
    }

    public function index()
    {
        $data['mensagens'] = $this->mensagemModel->findAll();
        return view('whatsapp/index', $data);
    }

    public function create()
    {
        return view('whatsapp/create');
    }

    public function store()
    {
        $mensagem = new MensagemWhatsapp($this->request->getPost());

        if (!$this->mensagemModel->save($mensagem)) {
            return redirect()->back()->with('errors', $this->mensagemModel->errors());
        }

        return redirect()->to('/whatsapp')->with('success', 'Mensagem registrada com sucesso.');
    }

    public function reenviarPendentes()
    {
        $pendentes = $this->mensagemModel->getPendentes();

        foreach ($pendentes as $msg) {
            // Aqui você poderia integrar com a API do WhatsApp
            // Exemplo: chamar um service -> WhatsAppService::send($msg)
        }

        return redirect()->to('/whatsapp')->with('success', 'Mensagens reenviadas.');
    }
}