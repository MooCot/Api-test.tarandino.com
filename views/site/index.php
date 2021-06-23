<?php
/* @var $this yii\web\View */

$this->title = 'My Yii Application';
use yii\helpers\Html;
?>


<div class="row">
  <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Users</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>name</th>
                      <th>surname</th>
                      <th>email</th>
                      <th>phone_number</th>
                      <th>birthdate</th>
                      <th>barcode</th>
                    </tr>
                  </thead>
                  <tbody>
									<?php foreach ($user as $usersList): ?>
                    <tr>
                      <td><?=Html::encode($usersList['id'])?></td>
                      <td><?=Html::encode($usersList['name'])?></td>
                      <td><?=Html::encode($usersList['surname'])?></td>
                      <td><span class="tag tag-success"><?=Html::encode($usersList['email'])?></span></td>
                      <td><?=Html::encode($usersList['phone_number'])?></td>
                      <td><?=Html::encode($usersList['birthdate'])?></td>
                      <td><?=Html::encode($usersList['barcode'])?></td>
                    </tr>
									<?php endforeach;?>
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
  </div>
</div>

