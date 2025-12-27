<?php

namespace app\commands;

use app\models\User;
use Yii;
use yii\base\Security;
use yii\console\Controller;
use yii\console\ExitCode;

class UserController extends Controller
{
    public function actionIndex()
    {
        $user = new User();
        $user->username = 'admin';
        $user->password_hash = Yii::$app->getSecurity()->generatePasswordHash('admin');

        if ($user->save()) {
            echo "User created: admin / admin\n";
        } else {
            echo "Error: " . print_r($user->errors, true) . "\n";
        }

        return ExitCode::OK;
    }
}
