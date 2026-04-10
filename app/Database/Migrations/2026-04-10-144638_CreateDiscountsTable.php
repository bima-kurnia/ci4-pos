<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDiscountsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'type'  => ['type' => 'ENUM', 'constraint' => ['percentage', 'fixed'], 'default' => 'percentage'],
            'value' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00'],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('discounts');
    }

    public function down()
    {
        $this->forge->dropTable('discounts');
    }
}