<?php

namespace app\modules\api\controllers;

use app\models\Card;
use app\models\Sms;
use app\models\User;
use Yii;
use yii\web\Controller;

/**
 * Default controller for the `api` module
 */
class SmsController extends Controller
{

    // return arr отправка сообщений зав от поля type телефон(activate) и пароль(restore)
    public function actionSend()
    {

        if (Yii::$app->request->isPost) {
            $restRequestData = Yii::$app->request->getBodyParams();
            $confirmSms = [
                'phoneNumber' => $restRequestData['phone_number'],
                'type' => $restRequestData['type'],
            ];
            if ($confirmSms['type'] === 'activate') {
                return $this->confirPhone($confirmSms['phoneNumber']);
            }
            if ($confirmSms['type'] === 'restore') {
                return $this->recovPassword($confirmSms['phoneNumber']);
            }
        }
    }

    // return arr подтверждение номера телефона
    public function actionCheck()
    {
        if (Yii::$app->request->isPost) {
            $restRequestData = Yii::$app->request->getBodyParams();
            $confirmSms = [
                'phoneNumber' => $restRequestData['phone_number'],
                'code' => $restRequestData['code'],
            ];

            $sms = Sms::findOne([
                'phone_number' => $confirmSms['phoneNumber'],
            ]);

            if ($sms->count_sms === '5' && !empty($sms)) {
                $sms->delete();
                return $this->returnJsonErorr(1009, 'Превышено количество попыток ввода кода (5 шт.)');
            }

            if ($sms->code === $confirmSms['code'] && !empty($sms)) {
                $this->acceptUser($sms, $confirmSms['phoneNumber']);
                return ['success' => true];
            } else {
                $this->smsCounter($sms);
                return $this->returnJsonErorr(1008, 'Неправильный код подтверждения');
            }
        }
    }

    // добавление телефона пользователя в таб user и генераци карты
    public function acceptUser($sms, $phone)
    {
        $user = User::findOne([
            'id' => $sms->user_id,
        ]);
        $card = new Card();
        $card->user_id = $user->id;
        $card->barcode = $this->rendomNumber(13);
        $card->bonuses_available = '500';
        $card->status = '0';
        $card->bonuses_for_next_status = '1500';
        $sms->delete();
        $token = Yii::$app->getRequest()->getCsrfToken();
        $user->firebase_token = $token;
        $user->phone_number = $phone;
        $user->save(false);
        $card->save(false);
        $headers = Yii::$app->response->headers;
        $headers->add('X-Auth-Token', $token);
    }

    // счетчик для поля count_sms (sms)
    public function smsCounter($sms)
    {
        if (!empty($sms)) {
            $sms->count_sms++;
            $sms->save(false);
        }
    }

    // return arr логика подтверждения телефона
    private function confirPhone($phone)
    {

        $user = $this->findUserByPhone($phone);
        $sms = $this->findSmsByPhone($phone);

        if ($user->phone_number === $phone) {
            return $this->returnJsonErorr(1004, 'Пользователь с таким номером телефона уже зарегистрирован');
        }

        if (!$this->phoneValidate($phone)) {
            return $this->returnJsonErorr(1005, 'Некорректный формат номера телефона');
        }

        if ($this->ifTimeOut($sms) && !empty($sms)) {
            $user = User::findOne([
                'id' => $sms->user_id,
            ]);
            $user->delete();
            $sms->delete();
            $this->createSms($phone);
            return ['success' => true];
        }
        if (!$this->ifTimeOut($sms) && !empty($sms)) {
            return $this->returnJsonErorr(1006, 'SMS уже была отправлена (не истёк период ожидания 2 мин.)');
        }

        if (empty($user)) {
            $this->createSms($phone);
            return ['success' => true];
        } else {
            return $this->returnJsonErorr(1007, 'Не удалось отправить SMS');
        }
    }

    // return arr логика сбросса пароля
    private function recovPassword($phone)
    {
        $user = $this->findUserByPhone($phone);
        $sms = $this->findSmsByPhone($phone);

        if ($user->phone_number !== $phone) {
            return $this->returnJsonErorr(1001, 'Пользователь с таким номером телефона не зарегистрирован');
        }

        if ($this->ifTimeOut($sms) && !empty($sms)) {
            $user = User::findOne([
                'id' => $sms->user_id,
            ]);
            $sms->delete();
            $user->password = password_hash($this->rendomNumber(5), PASSWORD_DEFAULT);
            $sms = new Sms();
            $sms->user_id = $user->id;
            $sms->phone_number = $phone;
            $sms->sms_timer = time();
            $sms->save(false);
            $user->save(false);
            return ['success' => true];
        }
        if (!$this->ifTimeOut($sms) && !empty($sms)) {
            return $this->returnJsonErorr(1006, 'SMS уже была отправлена (не истёк период ожидания 2 мин.)');
        }

        if ($user->phone_number === $phone) {
            $user->password = password_hash($this->rendomNumber(5), PASSWORD_DEFAULT);
            $sms = new Sms();
            $sms->user_id = $user->id;
            $sms->phone_number = $phone;
            $sms->sms_timer = time();
            $sms->save(false);
            $user->save(false);
            return ['success' => true];
        } else {
            return $this->returnJsonErorr(1007, 'Не удалось отправить SMS');
        }
    }

    // return bool, при > 2 минуты true
    private function ifTimeOut($sms)
    {
        $now_date = time();
        $temp_time = $now_date - $sms->sms_timer;
        if ($temp_time > 120) {
            return true;
        }

        return false;
    }

    // return arr спец error
    private function returnJsonErorr($codeEror, $textEror)
    {
        return [
            'success' => false,
            'code' => $codeEror,
            'message' => $textEror,
        ];
    }

    // return bool, true при + and lenght == 13
    public function phoneValidate($phoneNum)
    {
        if (stristr($phoneNum, '+', true) === '' && strlen($phoneNum) === 13) {
            return true;
        }
        return false;
    }

    // создаем user и sms
    public function createSms($phone)
    {
        $user = new User();
        $sms = new Sms();
        $user->save(false);
        $sms->user_id = $user->id;
        $sms->phone_number = $phone;
        $sms->code = $this->rendomNumber();
        $sms->sms_timer = time();
        $sms->save(false);
        $user->save(false);
    }

    // return obj (user)
    private function findUserByPhone($phone)
    {
        $user = User::findOne([
            'phone_number' => $phone,
        ]);
        return $user;
    }

    // return obj (sms)
    private function findSmsByPhone($phone)
    {
        $sms = Sms::findOne([
            'phone_number' => $phone,
        ]);
        return $sms;
    }

    // return случ число по дэф 4 длина
    public function rendomNumber($digits = 4)
    {
        $didg = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
        return $didg;
    }

    // включаем поддержку json
    public function beforeAction($action)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }
}
