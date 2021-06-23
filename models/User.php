<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $surname
 * @property string|null $password
 * @property string|null $email
 * @property string|null $gender
 * @property string|null $phone_number
 * @property string|null $birthdate
 * @property string|null $device_id
 * @property string|null $firebase_token
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'surname', 'password'], 'string', 'max' => 80],
            [['email'], 'string', 'max' => 11],
            [['gender'], 'string', 'max' => 1],
            [['phone_number', 'device_id', 'firebase_token'], 'string', 'max' => 255],
            [['birthdate'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'surname' => 'Surname',
            'password' => 'Password',
            'email' => 'Email',
            'gender' => 'Gender',
            'phone_number' => 'Phone Number',
            'birthdate' => 'Birthdate',
            'device_id' => 'Device ID',
            'firebase_token' => 'Firebase Token',
        ];
    }

		// public function validatePassword($password)
    // {
    //     return ($this->password == $password) ? true : false;
    // }
		
		public static function findIdentity($id)
    {
        // return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['firebase_token' => $token]);
    }

    public function getId()
    {
        // return $this->id;
    }

    public function getAuthKey()
    {
        // return $this->authKey;
    }

    public function validateAuthKey($authKey)
    {
        // return $this->authKey === $authKey;
    }
}
