<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "card".
 *
 * @property int $id
 * @property string|null $barcode
 * @property string|null $bonuses_available
 * @property string|null $bonuses_total
 * @property string|null $status
 * @property string|null $bonuses_for_next_status
 * @property string|null $current_status_minimum
 * @property int|null $user_id
 */
class Card extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'card';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['barcode', 'bonuses_available', 'bonuses_total', 'status', 'bonuses_for_next_status', 'current_status_minimum'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'barcode' => 'Barcode',
            'bonuses_available' => 'Bonuses Available',
            'bonuses_total' => 'Bonuses Total',
            'status' => 'Status',
            'bonuses_for_next_status' => 'Bonuses For Next Status',
            'current_status_minimum' => 'Current Status Minimum',
            'user_id' => 'User ID',
        ];
    }
}
