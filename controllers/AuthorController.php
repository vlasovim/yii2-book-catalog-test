<?php

namespace app\controllers;

use app\models\Author;
use app\models\Book;
use app\models\Subscription;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class AuthorController extends Controller
{
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    public function actionIndex(?int $year = null): string
    {
        $year = $year ?: date('Y');

        $dataProvider = new ActiveDataProvider([
            'query' => Author::topByYear($year),
            'pagination' => false,
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'year' => $year,
        ]);
    }

    public function actionCreate(): Response|string
    {
        $model = new Author();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['book/index']);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionView(int $id): Response|string
    {
        $model = $this->findModel($id);
        $subscriptionModel = new Subscription();

        if ($subscriptionModel->load(Yii::$app->request->post())) {
            $subscriptionModel->author_id = $model->id;

            if ($subscriptionModel->save()) {
                Yii::$app->session->setFlash(
                    'subscriptionSuccess',
                    'You have successfully subscribed to the author ' . $model->full_name . '!'
                );
            } else {
                Yii::$app->session->setFlash(
                    'subscriptionError',
                    'Subscription error. You may already be subscribed with this phone number.'
                );
            }

            return $this->refresh();
        }

        return $this->render('view', [
            'model' => $model,
            'subscriptionModel' => $subscriptionModel,
        ]);
    }

    protected function findModel(int $id): Author
    {
        $model = Author::find()
            ->where(['id' => $id])
            ->one();

        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
