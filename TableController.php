<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use frontend\models\service\Board;
use frontend\models\service\CurrentProfile;
use frontend\models\service\item\rows\ItemsTable;
use frontend\models\service\item\rows\ItemsTable\Columns;
use frontend\models\service\item\rows\ItemsTable\Colors;
use frontend\models\service\item\rows\ItemsTable\Rows;

class TableController extends Controller
{

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction'
            ]
        ];
    }

    public function actionIndex()
    {
        return $this->goHome();
    }

    public function actionColumns()
    {
        if(!Yii::$app->request->isAjax) {
            return $this->goHome();
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $params = Yii::$app->request->queryParams;

        // CATEGORY ID
        if(!isset($params['categoryId'])) {
            throw new NotFoundHttpException();
        }
        $categoryId = $params['categoryId'];

        // SELECTED BOARD
        $board = new Board();
        $selectedBoard = $board->getSelectedBoard($categoryId);
        $selectedBoardId = $selectedBoard['id'];
        if(!$selectedBoardId) {
            throw new NotFoundHttpException();
        }

        $profile = new CurrentProfile();
        $profile->setBoardId($selectedBoardId)
                ->prepare();

        // CHARACTERISTICS
        $profileCharacteristicModel = $profile->getProfileCharacteristicModel();
        list($profileSelectedCharacteristic, $profileSelectedCharacteristicValues) = $board->getProfileSelectedCharacteristics($profileCharacteristicModel->id);

        // COLUMNS
        $columnService = new Columns();
        $columns = $columnService->setProfileSelectedCharacteristic($profileSelectedCharacteristic)
                ->setProfileSelectedCharacteristicValues($profileSelectedCharacteristicValues)
                ->getColumns();

        // COLORS
        $colorService = new Colors();
        $colors = $colorService->setProfileSelectedCharacteristicValues($profileSelectedCharacteristicValues)
                ->getColors();

        return [
            'columns' => $columns,
            'colors'  => $colors
        ];
    }

    public function actionData()
    {
        if(!Yii::$app->request->isAjax) {
            return $this->goHome();
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $params = Yii::$app->request->queryParams;

        // CATEGORY ID
        if(!isset($params['categoryId'])) {
            throw new NotFoundHttpException();
        }
        $categoryId = $params['categoryId'];

        // PAGE AND PER_PAGE
        $options = Yii::$app->params;
        $page    = $options['firstPage'];
        $perPage = $options['perPage'];
        if(isset($params['page'])) {
            $page = $params['page'];
        }
        if(isset($params['pp'])) {
            $perPage = $params['pp'];
        }

        // SELECTED BOARD
        $board = new Board();
        $selectedBoard = $board->getSelectedBoard($categoryId);
        $selectedBoardId = $selectedBoard['id'];
        if(!$selectedBoardId) {
            throw new NotFoundHttpException();
        }

        $profile = new CurrentProfile();
        $profile->setBoardId($selectedBoardId)
                ->prepare();

        // PROFILE CHART TABLE
        $profileChartTableModel = $profile->getProfileChartTableModel();

        // CHARACTERISTICS
        $profileCharacteristicModel = $profile->getProfileCharacteristicModel();
        list($profileSelectedCharacteristic, $profileSelectedCharacteristicValues) = $board->getProfileSelectedCharacteristics($profileCharacteristicModel->id);

        // PROFILE MANUFACTURER
        $profileManufacturerModel = $profile->getProfileManufacturerModel();

        // ITEMS
        $itemService = new ItemsTable();
        $items = $itemService->setChartTable($profileChartTableModel)
                ->setCharacteristic($profileSelectedCharacteristic, $profileSelectedCharacteristicValues)
                ->setManufacturer($profileManufacturerModel)
                ->setPage($page)
                ->setPerPage($perPage)
                ->getRows();

        $rowsPrepareService = new Rows();
        $items = $rowsPrepareService->setItems($items)
                ->setProfileSelectedCharacteristic($profileSelectedCharacteristic)
                ->getRows();

        // PAGE COUNT
        $pageCount = $itemService->getPageCount();

        return [
            'data'      => $items,
            'last_page' => $pageCount
        ];
    }
}
