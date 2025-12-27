<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Author $model */
/** @var app\models\Subscription $subscriptionModel */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Books', 'url' => ['book/index']];
\yii\web\YiiAsset::register($this);
?>
<div class="author-view">
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Subscribe to the author <?= $model->full_name ?></h3>
        </div>
        <div class="card-body">
            <?php if (Yii::$app->session->hasFlash('subscriptionSuccess')): ?>
                <div class="alert alert-success">
                    <?= Yii::$app->session->getFlash('subscriptionSuccess') ?>
                </div>
            <?php elseif (Yii::$app->session->hasFlash('subscriptionError')): ?>
                <div class="alert alert-danger">
                    <?= Yii::$app->session->getFlash('subscriptionError') ?>
                </div>
            <?php endif; ?>

            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($subscriptionModel, 'subscriber_phone')->textInput([
                    'maxlength' => true,
                    'placeholder' => '+7 (999) 123-45-67'
            ])->label('Phone number') ?>

            <div class="form-group">
                <?= Html::submitButton('Subscribe', ['class' => 'btn btn-success']) ?>
            </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
