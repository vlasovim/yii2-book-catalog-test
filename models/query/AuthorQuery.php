<?php

namespace app\models\query;

use yii\db\ActiveQuery;

class AuthorQuery extends ActiveQuery
{
    public function topByYear(int $year, int $limit = 10): ActiveQuery
    {
        return $this->addSelect(['author.*'])
            ->addSelect(['booksCount' => 'COUNT(DISTINCT book.id)'])
            ->innerJoinWith([
                'books' => function ($query) use ($year) {
                    $query->andWhere(['book.year' => $year]);
                }
            ])
            ->groupBy('author.id')
            ->orderBy(['booksCount' => SORT_DESC])
            ->limit($limit);
    }
}
