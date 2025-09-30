<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFinanceiroTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'tipo'       => ['type' => 'ENUM', 'constraint' => ['entrada','saida']],
            'descricao'  => ['type' => 'VARCHAR', 'constraint' => '255'],
            'valor'      => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'data'       => ['type' => 'DATE'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('financeiro');
    }

    public function down()
    {
        $this->forge->dropTable('financeiro');
    }
}