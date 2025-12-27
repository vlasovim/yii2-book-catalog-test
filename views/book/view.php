<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Book $model */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Books', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="book-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                        'confirm' => 'Are you sure you want to delete this item?',
                        'method' => 'post',
                ],
        ]) ?>
    </p>

    <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                    'title',
                    'year',
                    'description:ntext',
                    'isbn',
                    [
                            'label' => 'Authors',
                            'value' => function ($model) {
                                $authors = $model->authors;
                                if (empty($authors)) {
                                    return '<span class="text-muted">No authors</span>';
                                }

                                $links = [];
                                foreach ($authors as $author) {
                                    $links[] = Html::a(
                                            Html::encode($author->full_name),
                                            ['/author/view', 'id' => $author->id],
                                            ['class' => 'badge bg-primary me-1']
                                    );
                                }

                                return implode(' ', $links);
                            },
                            'format' => 'raw',
                    ],
                    [
                            'attribute' => 'cover_photo',
                            'format' => 'raw',
                            'value' => function ($model) {
                                if ($model->cover_photo) {
                                    return Html::img($model->getCoverPhotoUrl(), [
                                            'class' => 'img-thumbnail',
                                            'style' => 'max-width: 200px; max-height: 300px;'
                                    ]);
                                }
                                return Html::tag('span', 'Not found', ['class' => 'text-muted']);
                            },
                    ],
            ],
    ]) ?>

</div>
