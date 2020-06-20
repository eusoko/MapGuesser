<?php namespace MapGuesser\PersistentData;

use MapGuesser\Database\Query\Modify;
use MapGuesser\Database\Query\Select;
use MapGuesser\Interfaces\Database\IResultSet;
use MapGuesser\PersistentData\Model\Model;

class PersistentDataManager
{
    public function selectFromDb(Select $select, string $type, bool $withRelations = false): ?Model
    {
        $table = call_user_func([$type, 'getTable']);
        $fields = call_user_func([$type, 'getFields']);

        $select->from($table);

        //TODO: only with some relations?
        if ($withRelations) {
            $relations = call_user_func([$type, 'getRelations']);

            $columns = [];

            foreach ($fields as $field) {
                $columns[] = [$table, $field];
            }

            $columns = array_merge($columns, $this->getRelationColumns($relations));

            $this->leftJoinRelations($select, $table, $relations);
            $select->columns($columns);
        } else {
            $select->columns($fields);
        }

        //TODO: return with array?
        $data = $select->execute()->fetch(IResultSet::FETCH_ASSOC);

        if ($data === null) {
            return null;
        }

        $model = new $type();
        $this->fillWithData($data, $model);

        return $model;
    }

    public function selectFromDbById($id, string $type, bool $withRelations = false): ?Model
    {
        $select = new Select(\Container::$dbConnection);
        $select->whereId($id);

        return $this->selectFromDb($select, $type, $withRelations);
    }

    public function fillWithData(array $data, Model $model): void
    {
        $relations = $model::getRelations();
        $relationData = [];

        foreach ($data as $key => $value) {
            if ($this->extractRelationData($key, $value, $relationData, $relations)) {
                continue;
            }

            $method = 'set' . str_replace('_', '', ucwords($key, '_'));

            if (method_exists($model, $method)) {
                $model->$method($value);
            }
        }

        $this->setRelations($model, $relationData);

        $model->saveSnapshot();
    }

    public function loadRelationsFromDb(Model $model, bool $recursive): void
    {
        foreach ($model::getRelations() as $relation => $relationType) {
            $camel = str_replace('_', '', ucwords($relation, '_'));

            $methodGet = 'get' . $camel . 'Id';
            $methodSet = 'set' . $camel;

            $relationId = $model->$methodGet();

            if ($relationId !== null) {
                $relationModel = $this->selectFromDbById($relationId, $relationType, $recursive);

                $model->$methodSet($relationModel);
            }
        }
    }

    public function saveToDb(Model $model): void
    {
        $this->syncRelations($model);

        $modified = $model->toArray();
        $id = $model->getId();

        $modify = new Modify(\Container::$dbConnection, $model::getTable());

        if ($id !== null) {
            $original = $model->getSnapshot();

            foreach ($original as $key => $value) {
                if ($value === $modified[$key]) {
                    unset($modified[$key]);
                }
            }

            if (count($modified) > 0) {
                $modify->setId($id);
                $modify->fill($modified);
                $modify->save();
            }
        } else {
            $modify->fill($modified);
            $modify->save();

            $model->setId($modify->getId());
        }

        $model->saveSnapshot();
    }

    public function deleteFromDb(Model $model): void
    {
        $modify = new Modify(\Container::$dbConnection, $model::getTable());
        $modify->setId($model->getId());
        $modify->delete();

        $model->setId(null);
        $model->resetSnapshot();
    }

    private function getRelationColumns(array $relations): array
    {
        $columns = [];

        foreach ($relations as $relation => $relationType) {
            $relationTable = call_user_func([$relationType, 'getTable']);
            foreach (call_user_func([$relationType, 'getFields']) as $relationField) {
                $columns[] = [$relationTable, $relationField, $relation . '__' . $relationField];
            }
        }

        return $columns;
    }

    private function leftJoinRelations(Select $select, string $table, array $relations): void
    {
        foreach ($relations as $relation => $relationType) {
            $relationTable = call_user_func([$relationType, 'getTable']);
            $select->leftJoin($relationTable, [$relationTable, 'id'], '=', [$table, $relation . '_id']);
        }
    }

    private function extractRelationData(string $key, $value, array &$relationData, array $relations): bool
    {
        $found = false;

        foreach ($relations as $relation => $relationType) {
            if (substr($key, 0, strlen($relation . '__')) === $relation . '__') {
                $found = true;
                $relationData[$relation][substr($key, strlen($relation . '__'))] = $value;
                break;
            }
        }

        return $found;
    }

    private function setRelations(Model $model, array &$relations): void
    {
        foreach ($model::getRelations() as $relation => $relationType) {
            if (isset($relations[$relation])) {
                $object = new $relationType();

                $this->fillWithData($relations[$relation], $object);

                $method = 'set' . str_replace('_', '', ucwords($relation, '_'));

                $model->$method($object);
            }
        }
    }

    private function syncRelations(Model $model): void
    {
        foreach ($model::getRelations() as $relation => $relationType) {
            $camel = str_replace('_', '', ucwords($relation, '_'));

            $methodGet = 'get' . $camel;
            $methodSet = 'set' . $camel . 'Id';

            $relationModel = $model->$methodGet();

            if ($relationModel !== null) {
                $model->$methodSet($relationModel->getId());
            }
        }
    }
}
