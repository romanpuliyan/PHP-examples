<?php

namespace frontend\models\service\profile\save;

use common\models\Board;
use common\models\ProfileCharacteristicSettings;
use common\models\ProfileCharacteristicSettings2Value;
use frontend\models\service\Updater;
use frontend\models\service\color\ColorGradient;

class Characteristic implements SaveInterface
{

    protected $data;
    protected $profileId;
    protected $errors;

    public $characteristic;
    public $characteristicValue;
    protected $profileCharacteristicId;

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

            $baseName                = '';
            $profileCharacteristicId = '';
            $characteristicId        = '';
            list($baseName, $profileCharacteristicId, $characteristicId) = explode('_', $key);
            if(!$baseName || !$profileCharacteristicId || !$characteristicId) {
                continue;
            }

            $this->processValue($baseName, $profileCharacteristicId, $characteristicId, $value);

            if($this->isCharacteristic($baseName)) {
                $this->characteristic[$characteristicId]['profile_characteristic_id'] = $profileCharacteristicId;
                $this->characteristic[$characteristicId]['characteristic_id'] = $characteristicId;

                if(!$this->profileCharacteristicId) {
                    $this->profileCharacteristicId = $profileCharacteristicId;
                }
            }

            if($this->isCharacteristicValue($baseName)) {
                $this->characteristicValue[$characteristicId]['profile_characteristic_settings_id'] = $profileCharacteristicId;
                $this->characteristicValue[$characteristicId]['characteristic_value_id'] = $characteristicId;
            }
        }

        foreach($this->characteristic as $key => $value) {
            if(!isset($value['show_in_chart_circle'])) {
                $this->characteristic[$key]['show_in_chart_circle'] = 0;
            }

            if(!isset($value['chart_circle_percent'])) {
                $this->characteristic[$key]['chart_circle_percent'] = 0;
            }

            if(!isset($value['show_in_table'])) {
                $this->characteristic[$key]['show_in_table'] = 0;
            }
        }

        $colorGradient = new ColorGradient();
        foreach($this->characteristicValue as $key => $value) {
            if(!isset($value['is_filter'])) {
                $this->characteristicValue[$key]['is_filter'] = 0;
            }           

            if(!isset($value['background_color_value']) || empty($value['background_color_value'])) {
                $this->characteristicValue[$key]['background_color_value'] = 0;
            }

            $color = $colorGradient->setValue($this->characteristicValue[$key]['background_color_value'])
                    ->getColor();
            $this->characteristicValue[$key]['background_color'] = $color;
        }

        return $this;
    }

    public function validate()
    {
        $totalPercent = 0;
        foreach($this->characteristic as $row) {
            $totalPercent += (int) $row['chart_circle_percent'];
        }

        if($totalPercent > 100) {
            $this->errors[] = 'Total percent amount more then 100';
            return false;
        }

        return true;
    }

    public function save()
    {
        $data = $this->characteristic;
        $model = new ProfileCharacteristicSettings();
        $whereForExists = ['in', 'profile_characteristic_id', $this->profileCharacteristicId];

        $updater = new Updater();
        $updater->setData($data)
                ->setModel($model)
                ->setWhereForExists($whereForExists)
                ->setInsertFields(['show_in_chart_circle', 'chart_circle_percent', 'show_in_table', 'profile_characteristic_id', 'characteristic_id'])
                ->setTableName('profile_characteristic_settings')
                ->update();

        $data = $this->characteristicValue;
        $model = new ProfileCharacteristicSettings2Value();
        $whereForExists = ['in', 'profile_characteristic_settings_id', $this->getSettingIds($this->profileCharacteristicId)];
        $updater->setData($data)
                ->setModel($model)
                ->setWhereForExists($whereForExists)
                ->setInsertFields(['background_color', 'background_color_value', 'characteristic_value_id', 'profile_characteristic_settings_id', 'is_filter'])
                ->setTableName('profile_characteristic_settings_2_value')
                ->update();
    }

    public function getSuccessMessage()
    {
        return 'Characteristics saved';
    }

    public function getErrors()
    {
        return $this->errors;
    }

    protected function processValue($baseName, $profileCharacteristicId, $characteristicId, $value)
    {
        switch($baseName) {
            case Board::BASE_NAME_PROFILE_CHARACTERISTIC_SHOW_IN_CIRCLE:
                $this->characteristic[$characteristicId]['show_in_chart_circle'] = $value;
                break;
            case Board::BASE_NAME_PROFILE_CHARACTERISTIC_CIRCLE_PERCENT:
                $this->characteristic[$characteristicId]['chart_circle_percent'] = $value;
                break;
            case Board::BASE_NAME_PROFILE_CHARACTERISTIC_SHOW_IN_TABLE:
                $this->characteristic[$characteristicId]['show_in_table'] = $value;
                break;
            case Board::BASE_NAME_PROFILE_FILTER_CHARACTERISTIC_VALUE:
                $this->characteristicValue[$characteristicId]['is_filter'] = $value;
                break;            
            case Board::BASE_NAME_PROFILE_CHARACTERISTIC_VALUE_COLOR_VALUE:
                $this->characteristicValue[$characteristicId]['background_color_value'] = $value;
                break;
        }
    }

    public function getSettingIds($profileCharacteristicId)
    {
        $rows = ProfileCharacteristicSettings::find()
                ->select([
                    'id'
                ])
                ->andFilterWhere(['profile_characteristic_id' => $profileCharacteristicId])
                ->asArray()
                ->all();

        $ids = [];
        foreach($rows as $row) {
            $ids[] = $row['id'];
        }

        return $ids;
    }

    protected function isCharacteristic($baseName)
    {
        $validNames = [
            Board::BASE_NAME_PROFILE_CHARACTERISTIC_SHOW_IN_CIRCLE,
            Board::BASE_NAME_PROFILE_CHARACTERISTIC_CIRCLE_PERCENT,
            Board::BASE_NAME_PROFILE_CHARACTERISTIC_SHOW_IN_TABLE
        ];

        if(in_array($baseName, $validNames)) {
            return true;
        }

        return false;
    }

    protected function isCharacteristicValue($baseName)
    {
        $validNames = [
            Board::BASE_NAME_PROFILE_FILTER_CHARACTERISTIC_VALUE,            
            Board::BASE_NAME_PROFILE_CHARACTERISTIC_VALUE_COLOR_VALUE
        ];

        if(in_array($baseName, $validNames)) {
            return true;
        }

        return false;
    }
}
