<?php

namespace frontend\models\service\profile\save;

use common\models\Board;

class ProfileSaveFactory
{
    public static function factory($type)
    {
        $service = null;
        switch($type) {
            case Board::TYPE_PROFILE_CHART_TABLE:
                $service = new ChartTable();
                break;
            case Board::TYPE_PROFILE_CHARACTERISTIC:
                $service = new Characteristic();
                break;
            case Board::TYPE_PROFILE_MANUFACTURER:
                $service = new Manufacturer();
                break;
        }

        return $service;
    }
}
