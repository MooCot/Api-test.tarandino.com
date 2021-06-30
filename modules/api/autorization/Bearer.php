<?php

namespace app\modules\api\autorization;

use Yii;
use yii\filters\auth\HttpBearerAuth;

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
