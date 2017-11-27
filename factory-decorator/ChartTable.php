<?php

namespace frontend\models\service\profile\save;

use common\models\ProfileChartTable;

class ChartTable implements SaveInterface
{

    protected $data;
    protected $profileId;
    protected $errors;
    protected $model;

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;
        $this->model = ProfileChartTable::findOne(['id' => $profileId]);

        return $this;
    }

    public function validate()
    {
        if($this->model->load($this->data) && $this->model->validate()) {
            return true;
        }

        $this->errors = $this->model->getErrors();

        return false;
    }

    public function save()
    {
        $this->model->save();
    }

    public function getSuccessMessage()
    {
        return 'Common settings were saved';
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
