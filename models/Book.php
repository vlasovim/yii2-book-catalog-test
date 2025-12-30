<?php

namespace app\models;

use Yii;
use app\components\jobs\NotifySubscribersJob;
use app\components\traits\SyncTrait;
use app\components\UploadFileBehavior;
use Throwable;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "book".
 *
 * @property int $id
 * @property string $title
 * @property int $year
 * @property string|null $description
 * @property string|null $isbn
 * @property string|null $cover_photo
 * @property int $created_by
 * @property int $updated_by
 * @property int|null $updated_at
 * @property int|null $created_at
 *
 * @property Author[] $authors
 * @property User $createdBy
 */
class Book extends ActiveRecord
{
    use SyncTrait;

    public array $authorIds = [];

    public static function tableName(): string
    {
        return 'book';
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
            ],
            BlameableBehavior::class,
            'uploadCover' => [
                'class' => UploadFileBehavior::class,
                'attribute' => 'cover_photo',
                'uploadDir' => 'uploads/cover-photos/'
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['description', 'isbn'], 'default', 'value' => null],
            [['title', 'year'], 'required'],
            [['authorIds'], 'required', 'message' => 'Authors cannot be blank.'],
            [['year'], 'integer'],
            [['description'], 'string'],
            [['title', 'isbn'], 'string', 'max' => 255],
            [['year'], 'integer', 'min' => 1000],
            [['cover_photo'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg'],
            [['authorIds'], 'each', 'rule' => ['exist', 'targetClass' => Author::class, 'targetAttribute' => 'id']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'year' => 'Year',
            'description' => 'Description',
            'isbn' => 'ISBN',
            'cover_photo' => 'Cover Photo',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }

    public function afterSave($insert, $changedAttributes): void
    {
        parent::afterSave($insert, $changedAttributes);

        if (!empty($this->authorIds)) {
            $this->sync('authors', $this->authorIds);

            if ($insert) {
                $this->notifySubscribers();
            }
        }
    }

    public function beforeDelete(): bool
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        $this->unlinkAll('authors', true);

        return true;
    }

    public function afterFind(): void
    {
        parent::afterFind();

        if ($this->isRelationPopulated('authors')) {
            $this->authorIds = ArrayHelper::getColumn($this->authors, 'id');
        }
    }

    public function getAuthors(): ActiveQuery
    {
        return $this->hasMany(Author::class, ['id' => 'author_id'])
            ->viaTable('book_author', ['book_id' => 'id']);
    }

    public function getCoverPhotoUrl(): string
    {
        return Yii::getAlias('@web/' . $this->cover_photo);
    }

    private function notifySubscribers(): void
    {
        try {
            Yii::$app->queue->push(new NotifySubscribersJob([
                'bookId' => $this->id,
            ]));

            Yii::info("Notification job queued for book {$this->id}");
        } catch (Throwable $e) {
            Yii::error("Failed to queue notification: " . $e->getMessage());
        }
    }
}
