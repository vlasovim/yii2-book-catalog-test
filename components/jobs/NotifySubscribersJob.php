<?php

namespace app\components\jobs;

use Throwable;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use app\models\Book;

class NotifySubscribersJob extends BaseObject implements JobInterface
{
    public int $bookId;

    public function execute($queue): void
    {
        $book = Book::find()
            ->with(['authors.subscriptions'])
            ->where(['id' => $this->bookId])
            ->one();

        if (!$book) {
            Yii::error("Book {$this->bookId} not found for notification");
            return;
        }

        $this->sendNotifications($book);
    }

    private function sendNotifications(Book $book): void
    {
        $subscribers = [];

        foreach ($book->authors as $author) {
            foreach ($author->subscriptions as $subscription) {
                $phone = $subscription->subscriber_phone;
                $subscribers[$phone] = [
                    'phone' => $phone,
                    'author_name' => $author->full_name,
                ];
            }
        }

        $count = count($subscribers);

        if ($count === 0) {
            Yii::info("No subscribers to notify for book {$book->id}");
            return;
        }

        $this->sendSms($book, $subscribers);

        Yii::info("Notified {$count} subscribers about book {$book->id}");
    }

    private function sendSms(Book $book, array $subscribers): void
    {
        $authors = implode(', ', array_map(fn($a) => $a->full_name, $book->authors));
        $message = "New book from {$authors}: {$book->title} ({$book->year})";

        foreach ($subscribers as $subscriber) {
            try {
                // Yii::$app->sms->send($subscriber['phone'], $message);

                Yii::info("SMS to {$subscriber['phone']}: {$message}");
            } catch (Throwable $e) {
                Yii::error("Failed to send SMS to {$subscriber['phone']}: " . $e->getMessage());
            }
        }
    }
}
