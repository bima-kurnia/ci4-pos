<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransactionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'invoice_number' => ['type' => 'VARCHAR', 'constraint' => 50],
            'user_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'customer_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'total_amount'   => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => '0.00'],
            'discount'       => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => '0.00'],
            'tax'            => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => '0.00'],
            'grand_total'    => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => '0.00'],
            'status'         => ['type' => 'ENUM', 'constraint' => ['pending', 'completed', 'cancelled'], 'default' => 'completed'],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
            'updated_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('invoice_number');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('customer_id', 'customers', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('transactions');
    }

    public function down()
    {
        $this->forge->dropTable('transactions');
    }
}