<?php
/**
 * Created by Treschelet.
 * Date: 10.07.14
 */

namespace treschelet\tourvisor;

use yii\base\BootstrapInterface;

class Module extends \yii\base\Module implements BootstrapInterface
{
    public $controllerNamespace = 'treschelet\tourvisor\controllers';

    public $defaultRoute = 'api';

    public $login;

    public $password;

    public function init()
    {
        parent::init();
    }

    public function bootstrap($app)
    {
        $app->getUrlManager()->addRules([
            ['class' => 'yii\rest\UrlRule', 'controller' => 'tourvisor/api'],
        ], false);
    }

} 