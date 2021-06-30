<?php

namespace app\modules\api\controllers;

use app\models\Card;
use app\models\User;
use app\modules\api\autorization\Basic;
use app\modules\api\autorization\Bearer;
use Yii;
use yii\web\Controller;

/**
 * Default controller for the `api` module
 */

class ApiController extends Controller
{
    // поведение для авторизации, переопределение ответа ошибки по токену в class Bearer, при пароле class Basic(прослойка response) логика тут
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $tokenType = $this->auntifTypeToken();

        $behaviors['authenticator'] = [
            'class' => Bearer::class,
        ];
        if ($tokenType['type_auth'] === 'Basic') {
            $behaviors['authenticator'] = [
                'class' => Basic::class,
                'auth' => function ($phone_number, $password) {
                    $user = User::find()->where(['phone_number' => $phone_number])->one();
                    if (password_verify($password, $user->password) && !empty($user)) {
                        return $user;
                    }
                    if (!password_verify($password, $user->password) && !empty($user)) {
                        Yii::$app->response->data = [
                            'success' => false,
                            'code' => 1002,
                            'message' => 'Неправильный пароль',
                        ];
                    } else {
                        Yii::$app->response->data = [
                            'success' => false,
                            'code' => 1001,
                            'message' => 'Пользователь с таким номером телефона не зарегистрирован',
                        ];
                    }
                },
            ];
        }
        return $behaviors;
    }

    // action отвечает за вход и обновление данных по методу отправки
    public function actionCustomer()
    {
        // return 'ad';
        if (Yii::$app->request->isGet) {
            $user = Yii::$app->user->identity;
            return $this->Login($user);
        }
        if (Yii::$app->request->isPost) {
            return $this->updataSingup();
        }
    }

    // return json ответ  и отправляем токен зашедшего юзера по методу аунтвефикации
    private function Login($user)
    {

        $card = Card::findOne([
            'user_id' => $user->id,
        ]);

        $typeAutorisation = $this->auntifTypeToken();
        if ($typeAutorisation['type_auth'] === 'Basic') {
            $headers = Yii::$app->response->headers;
            $headers->add('X-Auth-Token', $user->firebase_token);
            return $this->returnJsonUserIfPhone($user, $card);
        } else {
            $headers = Yii::$app->response->headers;
            $headers->add('X-Auth-Token', $user->firebase_token);
            return $this->returnJsonUserIfToken($user, $card);
        }
    }

    // return json ответ зарегистрированого user при валидации retorn спц error
    private function updataSingup()
    {
        $restRequestData = Yii::$app->request->getBodyParams();
        $reqvestDataUser = [
            'name' => $restRequestData['name'],
            'surname' => $restRequestData['surname'],
            'password' => $restRequestData['password'],
            'device_id' => $restRequestData['device_id'],
            'firebase_token' => $restRequestData['firebase_token'],
        ];
        $tokenCode = $reqvestDataUser['firebase_token'];

        $user = User::findOne([
            'firebase_token' => $tokenCode,
        ]);
        $card = Card::findOne([
            'user_id' => $user->id,
        ]);
        if (empty($card)) {
            return $this->returnJsonErorr(1023, 'Ошибка выдачи карты');
        }

        if ($reqvestDataUser['name'] === '' || $reqvestDataUser['name'] === null) {
            return $this->returnJsonErorr(1010, 'Не передано обязательное поле (имя) (при регистрации)');
        }

        if ($reqvestDataUser['surname'] === '' || $reqvestDataUser['surname'] === null) {
            return $this->returnJsonErorr(1011, 'Не передано обязательное поле (фамилия)');
        }
        if (strlen($reqvestDataUser['password']) < 4) {
            return $this->returnJsonErorr(1012, 'Пароль должен быть не менее 4-х символов');
        } else {
            $this->updateUser($user, $card, $reqvestDataUser);
            return $this->returnJsonUserSing($user, $card);
        }
    }

    // return arr[type_auth, token], возвращаем токен
    public function auntifTypeToken()
    {
        $authorization = Yii::$app->request->headers->get('Authorization');
        $token = strstr($authorization, ' ');
        $type_auth = substr($authorization, 0, -strlen($token));
        return $clearToken = [
            'type_auth' => $type_auth,
            'token' => $token,
        ];
    }
    // обновляем пользователя при взодных $reqvestDataUser, оновляем карту
    private function updateUser($user, $card, $reqvestDataUser)
    {
        $user->name = $reqvestDataUser['name'];
        $user->surname = $reqvestDataUser['surname'];
        $user->password = password_hash($reqvestDataUser['password'], PASSWORD_DEFAULT);
        $user->device_id = $reqvestDataUser['device_id'];
        $user->firebase_token = $reqvestDataUser['firebase_token'];
        $user->email = '';
        $user->gender = '0';
        $user->birthdate = '';
        // $card->barcode = $this->rendomNumber(13);
        $card->bonuses_available = '0';
        $card->status = '0';
        $card->bonuses_for_next_status = '5000';
        $card->save(false);
        $user->save(false);
        $headers = Yii::$app->response->headers;
        $headers->add('X-Auth-Token', $reqvestDataUser['firebase_token']);
    }
    // return arr[] спц ответ при успешной регистрации
    private function returnJsonUserSing($user, $card)
    {
        return ['success' => true,
            'data' =>
            [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'email' => $user->email,
                'gender' => (int) $user->gender,
                'phone_number' => $user->phone_number,
                'birthdate' => $user->birthdate,
                'card' => [
                    'barcode' => $card->barcode,
                    'bonuses_available' => (int) $card->bonuses_available,
                    'status' => (int) $card->status,
                    'bonuses_for_next_status' => (int) $card->bonuses_for_next_status,
                ],
            ],
        ];
    }

    // return arr[] спц ответ при успешной авторизации по токену
    private function returnJsonUserIfToken($user, $card)
    {
        return ['success' => true,
            'data' =>
            [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'email' => $user->email,
                'gender' => (int) $user->gender,
                'phone_number' => $user->phone_number,
                'birthdate' => $user->birthdate,
                'card' => [
                    'barcode' => (int) $card->barcode,
                    'bonuses_available' => (int) $card->bonuses_available,
                    'status' => (int) $card->status,
                    'bonuses_for_next_status' => (int) $card->bonuses_for_next_status,
                ],
            ],
        ];
    }

    // return arr[] спц ответ при успешной авторизации по номеру и pass
    private function returnJsonUserIfPhone($user, $card)
    {
        return ['success' => true,
            'data' =>
            [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'email' => $user->email,
                'gender' => (int) $user->gender,
                'phone_number' => $user->phone_number,
                'birthdate' => $user->birthdate,
                'card' => [
                    'barcode' => $card->barcode,
                    'bonuses_available' => (int) $card->bonuses_available,
                    'status' => (int) $card->status,
                    'bonuses_for_next_status' => (int) $card->bonuses_for_next_status,
                    'bonuses_total' => (int) $card->bonuses_total,
                    'current_status_minimum' => (int) $card->current_status_minimum,
                ],
            ],
        ];
    }

    // включаем поддержку json
    public function beforeAction($action)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }

    // return случ число по дэф 4 длина
    public function rendomNumber($digits = 4)
    {
        $didg = rand(pow(10, $digits - 1), pow(10, $digits) - 1);
        return $didg;
    }
    // return спц вывод для error валидации
    private function returnJsonErorr($code_eror, $text_eror)
    {
        return [
            'success' => false,
            'code' => $code_eror,
            'message' => $text_eror,
        ];
    }
}
