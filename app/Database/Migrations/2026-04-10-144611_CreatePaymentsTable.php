<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'             => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'transaction_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'method'         => ['type' => 'ENUM', 'constraint' => ['cash', 'card', 'transfer', 'ewallet'], 'default' => 'cash'],
            'amount_paid'    => ['type' => 'DECIMAL', 'constraint' => '15,2'],
            'change_amount'  => ['type' => 'DECIMAL', 'constraint' => '15,2', 'default' => '0.00'],
            'payment_status' => ['type' => 'ENUM', 'constraint' => ['paid', 'unpaid', 'partial'], 'default' => 'paid'],
            'reference'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at'     => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('transaction_id', 'transactions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('payments');
    }

    public function down()
    {
        $this->forge->dropTable('payments');
    }
}