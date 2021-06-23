<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%card}}`.
 */
class m210621_143830_create_card_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%card}}', [
            'id' => $this->primaryKey(),
            'barcode' => $this->string(),
            'bonuses_available' => $this->string(),
            'bonuses_total' => $this->string(),
            'status' => $this->string(),
            'bonuses_for_next_status' => $this->string(),
            'current_status_minimum' => $this->string(),
            'user_id' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%card}}');
    }
}
