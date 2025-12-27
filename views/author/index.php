<?php

use app\models\Book;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var int $year */

$this->title = 'Top Authors for ' . $year;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="book-index">

    <h1>Top authors in <?= $year ?></h1>

    <div class="row mb-3">
        <div class="col-md-4">
            <?= Html::beginForm(['author/index'], 'get', ['class' => 'form-inline']) ?>
            <div class="input-group">
                <?= Html::dropDownList(
                        'year',
                        $year,
                        array_combine(range(date('Y'), 1900), range(date('Y'), 1900)),
                        ['class' => 'form-control']
                ) ?>
                <div class="input-group-append">
                    <?= Html::submitButton('Go', ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
            <?= Html::endForm() ?>
        </div>
    </div>

    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'full_name',
                    [
                            'attribute' => 'books_count',
                            'label' => 'Books in ' . $year,
                            'format' => 'raw',
                    ],
            ],
    ]); ?>

</div>