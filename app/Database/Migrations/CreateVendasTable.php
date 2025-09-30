<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVendasTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'cliente_id' => ['type' => 'INT', 'unsigned' => true],
            'produto'    => ['type' => 'VARCHAR', 'constraint' => '150'],
            'valor'      => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'status'     => ['type' => 'VARCHAR', 'constraint' => '50', 'default' => 'aberta'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('cliente_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('vendas');
    }

    public function down()
    {
        $this->forge->dropTable('vendas');
    }
}