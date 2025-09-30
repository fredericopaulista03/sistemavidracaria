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
            'type'              => ['type' => 'VARCHAR', 'constraint' => '20', 'default' => 'text'],
            'from_me'           => ['type' => 'TINYINT', 'default' => 0],
            'chat_id'           => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'direction'         => ['type' => 'ENUM', 'constraint' => ['incoming', 'outgoing'], 'default' => 'incoming'],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('numero');
        $this->forge->addKey('provider_message_id');
        $this->forge->addKey('created_at');
        $this->forge->createTable('whatsapp_messages');
    }

    public function down()
    {
        $this->forge->dropTable('whatsapp_messages');
    }
}<?php

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
            'type'              => ['type' => 'VARCHAR', 'constraint' => '20', 'default' => 'text'],
            'from_me'           => ['type' => 'TINYINT', 'default' => 0],
            'chat_id'           => ['type' => 'VARCHAR', 'constraint' => '100', 'null' => true],
            'direction'         => ['type' => 'ENUM', 'constraint' => ['incoming', 'outgoing'], 'default' => 'incoming'],
            'created_at'        => ['type' => 'DATETIME', 'null' => true],
            'updated_at'        => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('numero');
        $this->forge->addKey('provider_message_id');
        $this->forge->addKey('created_at');
        $this->forge->createTable('whatsapp_messages');
    }

    public function down()
    {
        $this->forge->dropTable('whatsapp_messages');
    }
}