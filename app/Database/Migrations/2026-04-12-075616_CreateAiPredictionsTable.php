<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration: ai_predictions
 *
 * Caches AI prediction results so we don't hammer the Python
 * service on every page load. Cache expires after TTL minutes.
 */
class CreateAiPredictionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            // Which feature: sales_forecast | product_forecast | customer_insights
            'prediction_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            // JSON blob of the full AI response
            'payload' => [
                'type' => 'LONGTEXT',
            ],
            // When this cache entry expires (default 6 hours)
            'expires_at' => [
                'type' => 'DATETIME',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('prediction_type');
        $this->forge->createTable('ai_predictions');
    }

    public function down()
    {
        $this->forge->dropTable('ai_predictions');
    }
}