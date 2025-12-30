<?php

namespace app\models;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "author".
 *
 * @property int $id
 * @property string $full_name
 * @property int $created_by
 * @property int $updated_by
 * @property int|null $updated_at
 * @property int|null $created_at
 *
 * @property Book[] $books
 * @property Subscription[] $subscriptions
 */
class Author extends ActiveRecord
{
    public int $books_count;

    public static function tableName(): string
    {
        return 'author';
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
            ],
            BlameableBehavior::class,
        ];
    }

    public function rules(): array
    {
        return [
            [['full_name'], 'required'],
            [['full_name'], 'string', 'max' => 255],
            [['full_name'], 'unique'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'full_name' => 'Full Name',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }

    public function getBooks(): ActiveQuery
    {
        return $this->hasMany(Book::class, ['id' => 'book_id'])
            ->viaTable('book_author', ['author_id' => 'id']);
    }

    public function getSubscriptions(): ActiveQuery
    {
        return $this->hasMany(Subscription::class, ['author_id' => 'id']);
    }

    public static function topByYear($year, $limit = 10): ActiveQuery
    {
        return self::find()
            ->addSelect(['author.*'])
            ->addSelect(['books_count' => 'COUNT(DISTINCT book.id)'])
            ->innerJoinWith([
                'books' => function ($query) use ($year) {
                    $query->andWhere(['book.year' => $year]);
                }
            ])
            ->groupBy('author.id')
            ->orderBy(['books_count' => SORT_DESC])
            ->limit($limit);
    }
}
