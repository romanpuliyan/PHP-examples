<?php

namespace frontend\models\service\profile\save\decorator;

use common\models\ProfileManufacturerSettings;
use frontend\models\service\Updater;
use frontend\models\service\profile\save\SaveInterface;

class ManufacturerInsert implements SaveInterface
{

    protected $component;

    protected $profileId;
    protected $manufacturer;

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

        $manufacturer = $this->component->manufacturer;
        foreach($manufacturer as $key => $value) {
            $manufacturer[$key]['profile_manufacturer_id'] = $this->profileId;
        }

        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function validate()
    {
        return $this->component->validate();
    }

    public function save()
    {
        $data = $this->manufacturer;
        $model = new ProfileManufacturerSettings();
        $whereForExists = ['in', 'profile_manufacturer_id', $this->profileId];

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
        return $this->component->getSuccessMessage();
    }

    public function getErrors()
    {
        return $this->component->getErrors();
    }
}
