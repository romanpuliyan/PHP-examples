<?php

namespace frontend\models\service;

use Yii;

class Updater
{

    protected $data;
    protected $model;
    protected $whereForExists;
    protected $insertFields;
    protected $tableName;

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    public function setWhereForExists($whereForExists)
    {
        $this->whereForExists = $whereForExists;
        return $this;
    }

    public function setInsertFields($insertFields)
    {
        $this->insertFields = $insertFields;
        return $this;
    }

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    public function update()
    {

        // EXISTS ITEMS
        $rows = $this->model->find()->select(['id'])->where($this->whereForExists)->asArray()->all();
        if(!count($rows)) {
            $prepareArrayFunction = function($data) {
                $row = [];
                foreach($this->insertFields as $field) {
                    $row[] = $data[$field];
                }

                return $row;
            };

            Yii::$app->db->createCommand()->batchInsert(
                $this->tableName,
                $this->insertFields,
                array_map($prepareArrayFunction, $this->data)
            )->execute();

            return;
        }

        $oldIds = [];
        foreach($rows as $row) {
            $oldIds[] = $row['id'];
        }

        $insert = $this->data;
        $delete = $oldIds;
        $update = array();

        // IF NO ITEMS - DELETE FROM DB
        if(!$insert || !count($insert)) {
            foreach($delete as $id) {
                $row = $this->model->findOne($id);
                $row->delete();
            }

            return;
        }

        foreach($oldIds as $id) {

            $update[] = array(
                'data' => array_shift($insert),
                'id' => $id
            );

            array_shift($delete);

            if(!count($insert)) {
                break;
            }
        }

        // UPDATE
        if(count($update)) {
            foreach($update as $row) {

                $id = $row['id'];
                $model = $this->model->findOne($id);

                foreach($row['data'] as $field => $value) {
                    $model->$field = $value;
                }

                $model->save();
            }
        }

        // INSERT
        if(count($insert)) {
            $prepareArrayFunction = function($data) {
                $row = [];
                foreach($this->insertFields as $field) {
                    $row[] = $data[$field];
                }

                return $row;
            };

            Yii::$app->db->createCommand()->batchInsert(
                $this->tableName,
                $this->insertFields,
                array_map($prepareArrayFunction, $insert)
            )->execute();
        }

        // DELETE
        if(count($delete)) {
            foreach($delete as $id) {
                $row = $this->model->findOne($id);
                $row->delete();
            }
        }
    }
}
