<?php

namespace app\modules\api\autorization;

use app\models\Card;
use app\models\User;
use Exception;
use Yii;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\web\Controller;



class Bearer extends HttpBearerAuth
{
		// переобределяем ответ в случие неудачной аунтефикации по Bearer
    public function handleFailure($response)
    {
        return Yii::$app->response->data = [
            'success' => false,
            'code' => 1003,
            'message' => 'Недействительный токен',
        ];
    }
}
