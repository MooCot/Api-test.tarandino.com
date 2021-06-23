<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sms".
 *
 * @property int $id
 * @property string|null $code
 * @property string|null $count_sms
 * @property string|null $sms_timer
 * @property string|null $phone_number
 * @property int|null $user_id
 */
class Sms extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sms';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sms_timer'], 'integer'],
            [['user_id'], 'integer'],
            [['code'], 'string', 'max' => 10],
            [['count_sms'], 'string', 'max' => 1],
            [['phone_number'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'count_sms' => 'Count Sms',
            'sms_timer' => 'Sms Timer',
            'phone_number' => 'Phone Number',
            'user_id' => 'User ID',
        ];
    }
}
