<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user}}`.
 */
class m210621_143852_create_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(80),
            'surname' => $this->string(80),
            'password' => $this->string(80),
            'email' => $this->string(11), 
            'gender' => $this->string(1),
            'phone_number' => $this->string(255),
            'birthdate' => $this->string(50),
            'device_id' => $this->string(255),
            'firebase_token' => $this->string(255),
        ]);
    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}
