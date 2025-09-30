<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWhatsappMessagesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'numero'            => ['type' => 'VARCHAR', 'constraint' => '20'],
            'mensagem'          => ['type' => 'TEXT'],
            'status'            => ['type' => 'VARCHAR', 'constraint' => '50', 'default' => 'enviado'],
            'provider_message_id'=> ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'sent_at'           => ['type' => 'DATETIME', 'null' => true],
            'received_at'       => ['type' => 'DATETIME', 'null' => true],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('whatsapp_messages');
    }

    public function down()
    {
        $this->forge->dropTable('whatsapp_messages');
    }
}