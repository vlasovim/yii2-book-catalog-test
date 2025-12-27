<?php

namespace app\components;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class UploadFileBehavior extends Behavior
{
    public string $attribute = '';
    public string $uploadDir = '@webroot/uploads';
    private ?UploadedFile $_uploadFile;

    public function events(): array
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    public function init(): void
    {
        parent::init();

        $this->uploadDir = Yii::getAlias($this->uploadDir);
    }

    public function beforeValidate(): void
    {
        $this->setUploadFile();

        if ($this->uploadFile
            && $this->uploadFile instanceof UploadedFile
        ) {
            $this->owner->setAttribute($this->attribute, $this->uploadFile);
        }
    }

    public function beforeInsert(): void
    {
        $this->saveFile();
    }

    public function beforeUpdate(): void
    {
        if ($this->saveFile()) {
            $this->deleteFile();
        }
    }

    public function beforeDelete(): void
    {
        $this->deleteFile();
    }

    public function getFilePath($fileName): string
    {
        return rtrim($this->uploadDir, DIRECTORY_SEPARATOR . '/') . DIRECTORY_SEPARATOR . $fileName;
    }

    public function setUploadFile(): void
    {
        if (Yii::$app->request->isPost) {
            $this->_uploadFile = UploadedFile::getInstanceByName($this->attribute);
        }
    }

    public function getUploadFile(): ?UploadedFile
    {
        return $this->_uploadFile;
    }

    protected function saveFile(): bool
    {
        if ($this->uploadFile && $this->uploadFile instanceof UploadedFile) {
            if (!file_exists($this->uploadDir)) {
                FileHelper::createDirectory($this->uploadDir);
            }

            $fileName = uniqid() . '.' . $this->uploadFile->extension;
            $filePath = $this->getFilePath($fileName);

            if ($this->uploadFile->saveAs($filePath)) {
                $this->owner->setAttribute($this->attribute, $filePath);
            }

            return true;
        }

        $this->owner->setAttribute(
            $this->attribute,
            $this->owner->getOldAttribute($this->attribute)
        );

        return false;
    }

    protected function deleteFile(): void
    {
        $oldFilePath = $this->owner->getOldAttribute($this->attribute);

        if ($oldFilePath && file_exists($oldFilePath)) {
            unlink($oldFilePath);
        }
    }
}