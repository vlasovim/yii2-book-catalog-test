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
 * @property int|null $updated_at
 * @property int|null $created_at
 *
 * @property Author[] $authors
 * @property User $createdBy
 */
class Book extends ActiveRecord
{
    use SyncTrait;

    private array $authorIds = [];

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
            [['year'], 'integer'],
            [['description'], 'string'],
            [['title', 'isbn'], 'string', 'max' => 255],
            [['year'], 'integer', 'min' => 1000],
            [['cover_photo'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg'],
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
            $this->syncAuthors($this->authorIds);

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

    public function getAuthors(): ActiveQuery
    {
        return $this->hasMany(Author::class, ['id' => 'author_id'])
            ->viaTable('book_author', ['book_id' => 'id']);
    }

    public function getCreator(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function syncAuthors(array $authorIds): void
    {
        $this->sync('authors', $authorIds);
    }

    public function getCoverPhotoUrl(): string
    {
        return Yii::getAlias('@web/' . $this->cover_photo);
    }

    public function saveBook(array $authorIds = []): bool
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $this->authorIds = $authorIds;

            if (!$this->save()) {
                $transaction->rollBack();
                return false;
            }

            $transaction->commit();
            return true;
        } catch (Throwable $e) {
            $transaction->rollBack();

            return false;
        }
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
