<?php

namespace frontend\models\service\profile\save;

use common\models\Board;
use frontend\models\service\profile\save\Characteristic;
use frontend\models\service\profile\save\ChartTable;
use frontend\models\service\profile\save\Manufacturer;
use frontend\models\service\profile\save\decorator\CharacteristicInsert;
use frontend\models\service\profile\save\decorator\ManufacturerInsert;

class ProfileCreateFactory
{

    public static function factory($type)
    {
        $service = null;
        switch($type) {
            case Board::TYPE_PROFILE_CHART_TABLE:
                $service = new ChartTable();
                break;
            case Board::TYPE_PROFILE_CHARACTERISTIC:
                $service = new CharacteristicInsert(new Characteristic());
                break;
            case Board::TYPE_PROFILE_MANUFACTURER:
                $service = new ManufacturerInsert(new Manufacturer());
                break;
        }

        return $service;
    }
}
