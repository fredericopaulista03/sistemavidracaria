<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsuariosTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nome'        => ['type' => 'VARCHAR', 'constraint' => '100'],
            'email'       => ['type' => 'VARCHAR', 'constraint' => '150', 'unique' => true],
            'senha'       => ['type' => 'VARCHAR', 'constraint' => '255'],
            'telefone'    => ['type' => 'VARCHAR', 'constraint' => '20', 'null' => true],
            'perfil'      => ['type' => 'VARCHAR', 'constraint' => '50'],
            'status'      => ['type' => 'TINYINT', 'default' => 1],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('usuarios');
    }

    public function down()
    {
        $this->forge->dropTable('usuarios');
    }
}