<?php

namespace app\components\traits;

trait SyncTrait
{
    public function sync(string $relationName, array $ids): void
    {
        $relation = $this->getRelation($relationName);

        $this->unlinkAll($relationName, true);

        if (empty($ids)) {
            return;
        }

        $models = $relation->modelClass::findAll(['id' => $ids]);

        foreach ($models as $model) {
            $this->link($relationName, $model);
        }
    }
}