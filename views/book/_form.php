<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Book $model */
/** @var yii\widgets\ActiveForm $form */
/** @var array $authorList */
?>

<div class="book-form">

    <?php $form = ActiveForm::begin([
            'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'year')->textInput() ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'isbn')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'authorIds')
            ->label('Authors')
            ->dropDownList(
                    $authorList,
                    ['multiple' => 'multiple']
            )->hint(
                    Html::a('Create new author', ['author/create'], [
                            'target' => '_blank',
                            'class' => 'btn btn-xs btn-link',
                    ])
            ) ?>


    <div class="form-group">
        <label class="control-label">Cover photo</label>

        <?php if ($model->cover_photo && file_exists($model->cover_photo)): ?>
            <div class="mb-2">
                <?= Html::img($model->getCoverPhotoUrl(), [
                        'class' => 'img-thumbnail',
                        'style' => 'max-width: 200px; max-height: 300px;'
                ]) ?>
            </div>
        <?php endif; ?>

        <input type="file"
               name="cover_photo"
               class="form-control"
               accept="image/*"/>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
