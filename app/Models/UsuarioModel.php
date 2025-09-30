<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Usuario;

class UsuarioModel extends Model
{
    protected $table            = 'usuarios';
    protected $primaryKey       = 'id';
    protected $returnType       = Usuario::class;
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;

    protected $allowedFields    = [
        'nome',
        'email',
        'senha',
        'telefone',
        'perfil',
        'status'
    ];

    // Ordenação padrão
    protected $order            = ['nome' => 'ASC'];

    // Regras de validação
    protected $validationRules = [
        'nome'     => 'required|min_length[3]|max_length[100]',
        'email'    => 'required|valid_email|is_unique[usuarios.email,id,{id}]',
        'senha'    => 'permit_empty|min_length[6]',
        'telefone' => 'permit_empty|max_length[20]',
        'perfil'   => 'required|in_list[admin,financeiro,vendedor]',
    ];

    protected $validationMessages = [
        'email' => [
            'is_unique' => 'Este e-mail já está cadastrado.',
        ],
        'perfil' => [
            'in_list' => 'O perfil deve ser admin, financeiro ou vendedor.',
        ],
    ];

    /**
     * Buscar usuário pelo email
     */
    public function getByEmail(string $email): ?Usuario
    {
        return $this->where('email', $email)->first();
    }
}