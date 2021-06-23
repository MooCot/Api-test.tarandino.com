<?php
/* @var $this yii\web\View */

$this->title = 'My Yii Application';
use yii\helpers\Html;
?>
 <table border="1">
   <tr>
    <th>id</th>
    <th>phone</th>
    <th>code</th>
   </tr>
   <tr><td><?= Html::encode($sms['id']) ?></td><td><?= Html::encode($sms['code']) ?></td><td><?= Html::encode($sms['phone_number']) ?></td></tr>
  </table>


