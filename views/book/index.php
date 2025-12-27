<?php

use app\models\Book;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Books';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="book-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (!Yii::$app->user->isGuest): ?>
        <p>
            <?= Html::a('Create Book', ['create'], ['class' => 'btn btn-success']) ?>
        </p>
    <?php endif; ?>

    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    
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
                            'class' => ActionColumn::class,
                            'visible' => !Yii::$app->user->isGuest,
                            'urlCreator' => function ($action, Book $model, $key, $index, $column) {
                                return Url::toRoute([$action, 'id' => $model->id]);
                            }
                    ],
            ],
    ]); ?>


</div>
