<?php

namespace frontend\models\service\profile\save;

use yii\helpers\HtmlPurifier;
use common\models\Board;
use common\models\ProfileManufacturerSettings;
use frontend\models\service\Updater;

class Manufacturer implements SaveInterface
{

    protected $profileId;
    protected $data;
    protected $errors;

    public $manufacturer;
    protected $profileManufacturerId;

    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;
        return $this;
    }

    public function setData($data)
    {
        foreach($data as $key => $value) {

            if(!preg_match("/^.+?_.+?_.+?$/i", $key)) {
                continue;
            }

            $baseName              = '';
            $profileManufacturerId = '';
            $manufacturerId        = '';
            list($baseName, $profileManufacturerId, $manufacturerId) = explode("_", $key);
            if(!$baseName || !$profileManufacturerId || !$manufacturerId) {
                continue;
            }

            $this->processValue($baseName, $profileManufacturerId, $manufacturerId, $value);

            if(!$this->profileManufacturerId) {
                $this->profileManufacturerId = $profileManufacturerId;
            }

            $this->manufacturer[$manufacturerId]['profile_manufacturer_id'] = $profileManufacturerId;
            $this->manufacturer[$manufacturerId]['manufacturer_id'] = $manufacturerId;
        }

        foreach($this->manufacturer as $key => $value) {
            if(!isset($value['show_in_chart'])) {
                $this->manufacturer[$key]['show_in_chart'] = 0;
            }

            if(!isset($value['show_in_table'])) {
                $this->manufacturer[$key]['show_in_table'] = 0;
            }

            if(!isset($value['comment'])) {
                $this->manufacturer[$key]['comment'] = '';
            }
        }

        return $this;
    }

    public function validate()
    {
        return true;
    }

    public function save()
    {
        $data = $this->manufacturer;
        $model = new ProfileManufacturerSettings();
        $whereForExists = ['in', 'profile_manufacturer_id', $this->profileManufacturerId];

        $updater = new Updater();
        $updater->setData($data)
                ->setModel($model)
                ->setWhereForExists($whereForExists)
                ->setInsertFields(['show_in_chart', 'comment', 'show_in_table', 'profile_manufacturer_id', 'manufacturer_id'])
                ->setTableName('profile_manufacturer_settings')
                ->update();
    }

    public function getSuccessMessage()
    {
        return 'Manufacturers were saved';
    }

    public function getErrors()
    {
        return $this->errors;
    }

    protected function processValue($baseName, $profileManufacturerId, $manufacturerId, $value)
    {
        switch($baseName) {
            case Board::BASE_NAME_PROFILE_MANUFACTURER_SHOW_IN_CHART:
                $this->manufacturer[$manufacturerId]['show_in_chart'] = $value;
                break;
            case Board::BASE_NAME_PROFILE_MANUFACTURER_SHOW_IN_TABLE:
                $this->manufacturer[$manufacturerId]['show_in_table'] = $value;
                break;
            case Board::BASE_NAME_PROFILE_MANUFACTURER_COMMENT:
                $value = HtmlPurifier::process($value);
                $value = addslashes($value);
                $this->manufacturer[$manufacturerId]['comment'] = $value;
                break;
        }
    }
}
