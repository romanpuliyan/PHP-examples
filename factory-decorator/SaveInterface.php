<?php

namespace frontend\models\service\profile\save;

interface SaveInterface
{

    public function setProfileId($profileId);

    public function setData($data);

    public function validate();

    public function save();

    public function getSuccessMessage();

    public function getErrors();
}
