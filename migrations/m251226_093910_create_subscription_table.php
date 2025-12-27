<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%subscription}}`.
 */
class m251226_093910_create_subscription_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%subscription}}', [
            'id' => $this->primaryKey(),
            'author_id' => $this->integer()->notNull(),
            'subscriber_phone' => $this->string()->notNull(),
            'updated_at' => $this->integer(),
            'created_at' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk-subscription-author_id',
            'subscription',
            'author_id',
            'author',
            'id'
        );

        $this->createIndex(
            'idx-unique-subscription-subscriber_phone',
            '{{%subscription}}',
            ['author_id', 'subscriber_phone'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%subscription}}');
    }
}
