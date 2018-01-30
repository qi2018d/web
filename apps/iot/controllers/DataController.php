<?php
/**
 * Created by PhpStorm.
 * User: leo
 * Date: 2018-01-28
 * Time: 오후 5:21
 */

namespace Iot\Controller;


use Slimvc\Core\Controller;

class DataController extends Controller
{
    public function actionPostDataSendAir(){
        //
        $this->getApp()->contentType('application/json');
        $data = array(
            'title' => 'It works!',
            'content' => 'Have fun with Slim framework in MVC way!'
        );
        echo json_encode($data);
    }
    public function actionPostDataSendHeart(){
        //
        $this->getApp()->contentType('application/json');
        $data = array(
            'title' => 'It works!',
            'content' => 'Have fun with Slim framework in MVC way!'
        );
        echo json_encode($data);
    }
}