<?php

namespace frontend\models\service\category;

use Yii;
use common\models\Category2ManufacturerSort;

class SortManufacturer
{

    protected $categoryId;
    protected $data;

    public function setCategoryId($categoryId)
    {
        $this->categoryId = (int) $categoryId;
        return $this;
    }

    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }

    public function process()
    {

        $categoryId = $this->categoryId;

        $rows = Category2ManufacturerSort::find()
                ->select([
                    'sort'
                ])
                ->andFilterWhere(['in', 'manufacturer_id', $this->data])
                ->andFilterWhere(['category_id' => $categoryId])
                ->orderBy("sort ASC")
                ->asArray()
                ->all();

        $data = [];
        foreach($rows as $row) {
            $data[] = $row['sort'];
        }

        $where = implode(", ", $this->data);
        $data  = implode(", ", $data);

        $db = Yii::$app->db;
        $query = "UPDATE category_2_manufacturer_sort SET sort = ELT(FIELD(manufacturer_id, $where), $data) WHERE manufacturer_id IN ($where) AND category_id = $categoryId";
        $db->createCommand($query)->query();
    }
}
