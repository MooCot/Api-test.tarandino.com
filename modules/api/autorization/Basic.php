<?php

namespace app\modules\api\autorization;

use app\models\Card;
use app\models\User;
use Exception;
use Yii;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\web\Controller;



class Basic extends HttpBasicAuth
{
		// переобределяем ответ в случие неудачной аунтефикации по Basic
    public function handleFailure($response)
    {
    }
}
