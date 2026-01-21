<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

class UserAuth extends User implements IdentityInterface
{
    public static function findIdentity($id): ?IdentityInterface
    {
        return static::findOne(['id' => $id]);
    }

    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        return null;
    }

    public function getId(): int|string
    {
        return $this->id;
    }

    public function getAuthKey(): ?string
    {
        return null;
    }

    public function validateAuthKey($authKey): ?bool
    {
        return null;
    }

    public function validatePassword($password): bool
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    public static function findByUsername($username): ?User
    {
        return static::findOne(['username' => $username]);
    }
}
