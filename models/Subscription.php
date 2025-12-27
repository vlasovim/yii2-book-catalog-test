<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "subscription".
 *
 * @property int $id
 * @property int $author_id
 * @property string $subscriber_phone
 * @property int|null $updated_at
 * @property int|null $created_at
 *
 * @property Author $author
 */
class Subscription extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'subscription';
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['author_id', 'subscriber_phone'], 'required'],
            [['author_id'], 'integer'],
            [['subscriber_phone'], 'string', 'max' => 255],
            [['author_id', 'subscriber_phone'], 'unique', 'targetAttribute' => ['author_id', 'subscriber_phone']],
            [['author_id'], 'exist', 'skipOnError' => true, 'targetClass' => Author::class, 'targetAttribute' => ['author_id' => 'id']],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'id' => 'ID',
            'author_id' => 'Author ID',
            'subscriber_phone' => 'Subscriber Phone',
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
        ];
    }

    public function getAuthor(): ActiveQuery
    {
        return $this->hasOne(Author::class, ['id' => 'author_id']);
    }
}
