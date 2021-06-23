<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\db\Query;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\User;
use app\models\Card;
use app\models\Sms;

class SiteController extends Controller
{
		// отображает таблицу пользователей
		public function actionIndex()
    {
				$query = new Query();
				$query->select('*')->from('user')->rightJoin('card', 'user.id = card.user_id');
				$command = $query->createCommand();
				$user = $command->queryAll();

				return $this->render('index', [
					'user' => $user,
				]);
    }
		// отображает отправленую смс
		public function actionSms()
    {
			// $smsCode = Sms::select('*')->where('code != null');
			$rows = (new \yii\db\Query())
						// ->select(['min(sms.id)'])
						->select(['*'])
						->from('sms')
						->where(['<>','code', 'null'])
						->all();

			$min = max($rows);	
				return $this->render('sms', [
					'sms' => $min,
				]);
    }
}
