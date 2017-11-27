<?php

namespace frontend\models\service\profile\save\decorator;

use common\models\ProfileCharacteristicSettings;
use common\models\ProfileCharacteristicSettings2Value;
use frontend\models\service\Updater;
use frontend\models\service\profile\save\SaveInterface;

class CharacteristicInsert implements SaveInterface
{

    protected $component;

    protected $profileId;
    protected $oldProfileId;
    protected $characteristicIds;

    protected $characteristic;
    protected $characteristicValue;
    protected $data;

    public function __construct(SaveInterface $component)
    {
        $this->component = $component;
    }

    public function setProfileId($profileId)
    {
        $this->component->setProfileId($profileId);
        $this->profileId = $profileId;
        return $this;
    }

    public function setData($data)
    {
        $this->component->setData($data);

        $characteristic = $this->component->characteristic;
        foreach($characteristic as $key => $value) {
            if(!$this->oldProfileId) {
                $this->oldProfileId = $value['profile_characteristic_id'];
            }

            $characteristic[$key]['profile_characteristic_id'] = $this->profileId;
        }

        $this->characteristic      = $characteristic;
        $this->characteristicValue = $this->component->characteristicValue;

        return $this;
    }

    public function validate()
    {
        return $this->component->validate();
    }

    public function save()
    {

        // PROCESS CHARACTERISTIC
        $data = $this->characteristic;
        $model = new ProfileCharacteristicSettings();
        $whereForExists = ['in', 'profile_characteristic_id', $this->profileId];

        $updater = new Updater();
        $updater->setData($data)
                ->setModel($model)
                ->setWhereForExists($whereForExists)
                ->setInsertFields(['show_in_chart_circle', 'chart_circle_percent', 'show_in_table', 'profile_characteristic_id', 'characteristic_id'])
                ->setTableName('profile_characteristic_settings')
                ->update();

        // OLD IDS 2 NEW IDS
        $rows = $this->getCharacteristicByProfileId($this->oldProfileId);

        $oldIds = [];
        foreach($rows as $row) {
            $oldIds[$row['characteristic_id']] = $row['id'];
        }

        $rows = $this->getCharacteristicByProfileId($this->profileId);

        $newIds = [];
        foreach($rows as $row) {
            $newIds[$row['characteristic_id']] = $row['id'];
        }

        $oldIds2newIds = [];
        foreach($oldIds as $key => $value) {
            $oldIds2newIds[$value] = $newIds[$key];
        }

        // PROCESS CHARACTERISTIC VALUES
        foreach($this->characteristicValue as $key => $value) {
            $this->characteristicValue[$key]['profile_characteristic_settings_id'] = $oldIds2newIds[$value['profile_characteristic_settings_id']];
        }

        $data = $this->characteristicValue;
        $model = new ProfileCharacteristicSettings2Value();
        $whereForExists = ['in', 'profile_characteristic_settings_id', $this->component->getSettingIds($this->profileId)];
        $updater->setData($data)
                ->setModel($model)
                ->setWhereForExists($whereForExists)
                ->setInsertFields(['background_color', 'background_color_value', 'characteristic_value_id', 'profile_characteristic_settings_id', 'is_filter'])
                ->setTableName('profile_characteristic_settings_2_value')
                ->update();
    }

    public function getSuccessMessage()
    {
        return $this->component->getSuccessMessage();
    }

    public function getErrors()
    {
        return $this->component->getErrors();
    }

    protected function getCharacteristicByProfileId($profileId)
    {
        $rows = ProfileCharacteristicSettings::find()
                ->select([
                    'id',
                    'characteristic_id'
                ])
                ->andFilterWhere(['profile_characteristic_id' => $profileId])
                ->asArray()
                ->all();

        return $rows;
    }
}
