<?php

namespace app\components\traits;

use Yii;
use Throwable;

trait SyncTrait
{
    public function sync(string $relationName, array $ids): void
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $relation = $this->getRelation($relationName);
            $currentIds = $relation->select('id')->column();

            $toUnlink = array_diff($currentIds, $ids);
            $toLink = array_diff($ids, $currentIds);

            if (!empty($toUnlink)) {
                $models = $relation->modelClass::findAll(['id' => $toUnlink]);

                foreach ($models as $model) {
                    $this->unlink($relationName, $model, true);
                }
            }

            if (!empty($toLink)) {
                $models = $relation->modelClass::findAll(['id' => $toLink]);

                foreach ($models as $model) {
                    $this->link($relationName, $model);
                }
            }

            $transaction->commit();
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}