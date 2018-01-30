<?php
/**
 * Created by PhpStorm.
 * User: leo
 * Date: 2018-01-28
 * Time: 오후 4:14
 */

namespace Iot\Controller;
use Slimvc\Core\Controller;

class UserController extends Controller
{

    public function actionPostUserSignup(){
        //
        $this->getApp()->contentType('application/json');
        $data = array(
            'title' => 'It works!',
            'content' => 'Have fun with Slim framework in MVC way!'
        );
        echo json_encode($data);
    }
    public function actionPostUserSignin(){
        //
        $this->getApp()->contentType('application/json');
        $data = array(
            'title' => 'It works!',
            'content' => 'Have fun with Slim framework in MVC way!'
        );
        echo json_encode($data);
    }
    public function actionPostUserExist(){
        //
        $this->getApp()->contentType('application/json');
        $data = array(
            'title' => 'It works!',
            'content' => 'Have fun with Slim framework in MVC way!'
        );
        echo json_encode($data);
    }
    public function actionPostUserChangePasswd(){
        //
        $this->getApp()->contentType('application/json');
        $data = array(
            'title' => 'It works!',
            'content' => 'Have fun with Slim framework in MVC way!'
        );
        return $data;
    }
    public function actionPostUserRegisterSensor(){
        //
        $this->getApp()->contentType('application/json');
        $data = array(
            'title' => 'It works!',
            'content' => 'Have fun with Slim framework in MVC way!'
        );
        echo json_encode($data);
    }
    public function actionPostUserDeregisterSensor(){
        //
        $this->getApp()->contentType('application/json');
        $data = array(
            'title' => 'It works!',
            'content' => 'Have fun with Slim framework in MVC way!'
        );
        echo json_encode($data);
    }
}