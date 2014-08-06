<?php
/**
 * Created by Treschelet.
 * Date: 05.08.14
 */

namespace treschelet\tourvisor\controllers;

use Yii;
use yii\rest\Controller;
use treschelet\tourvisor\components\Tourvisor;

class ApiController extends Controller
{
    /** @var $TV Tourvisor */
    protected $TV;

    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $this->TV = new Tourvisor([
                'login' => $this->module->login,
                'password' => $this->module->password,
            ]);
            return true;
        } else {
            return false;
        }
    }

    public function actionIndex()
    {
        return ['version' => $this->module->version];
    }

    public function actionList($type)
    {
        return $this->TV->getList($type, Yii::$app->request->queryParams);
    }

    public function actionResult($requestid, $type = 'result')
    {
        return $this->TV->getResult($requestid, $type, Yii::$app->request->queryParams);
    }

    public function actionSearch()
    {
        return $this->TV->search(Yii::$app->request->queryParams);
    }
} 