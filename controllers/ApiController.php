<?php
/**
 * Created by Treschelet.
 * Date: 05.08.14
 */

namespace treschelet\tourvisor\controllers;

use yii\rest\Controller;

class ApiController extends Controller
{
    public function actionList()
    {
        return ['response' => 'ok'];
    }
} 