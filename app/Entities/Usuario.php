<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Usuario extends Entity
{
    protected $attributes = [
        'id'       => null,
        'nome'     => null,
        'email'    => null,
        'senha'    => null,
        'telefone' => null,
        'perfil'   => null,
        'status'   => 1,
    ];

    protected $datamap = []; // caso queira mapear nomes diferentes de campos/atributos
    protected $dates   = ['created_at', 'updated_at'];

    protected $casts   = [
        'id'     => 'integer',
        'status' => 'integer',
    ];

    // Ocultar a senha em retornos JSON
    protected $hidden = ['senha'];

    // Setter automático de senha com hash
    public function setSenha(string $senha)
    {
        $this->attributes['senha'] = password_hash($senha, PASSWORD_DEFAULT);
        return $this;
    }
}