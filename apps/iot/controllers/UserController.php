<?php
/**
 * Created by PhpStorm.
 * User: leo
 * Date: 2018-01-28
 * Time: 오후 4:14
 */

namespace Iot\Controller;
use Iot\Model\RegistrationModel;
use Iot\Model\UserModel;
use PHPMailer\PHPMailer\Exception;
use Slimvc\Core\Controller;

class UserController extends Controller
{
    public function actionPostUserSignup(){
        // insert user data with status_auth 0
        $this->getApp()->contentType('application/json');
        $req = json_decode($this->getApp()->request->getBody());
        $user = new UserModel();
        //
        try{
            $user->saveUser($req);
        }
        catch (\Exception $e){
            $data = array(
                'status' => 'fail',
                'content' => $e->getMessage()
            );
            echo json_encode($data);
            exit;
        }

        $data = array(
            'status' => 'success'
        );
        echo json_encode($data);
    }
    public function actionPostUserSignin(){
        // check email(or username) and password
        $this->getApp()->contentType('application/json');
        $req = json_decode($this->getApp()->request->getBody());
        $user = new UserModel();
        $result = $user->getUser($req);

        if (is_array($result)){
            if (password_verify($req->{'password'}, $result['passwd_hash'])){
                $data = array(
                    'status'=> 1,
                    'message'=> 'Success'
                );
            }
            else {
                $data = array(
                    'status'=> 0,
                    'message'=> 'username or password is incorrect. '
                );
            }
        }
        else {
            $data = array(
                'status'=> 0,
                'message'=> 'username or password is incorrect. '
            );
        }
        echo json_encode($data);
    }
    public function actionPostUserExist(){
        //check that user exists
        $this->getApp()->contentType('application/json');
        $req = json_decode($this->getApp()->request->getBody());
        $user = new UserModel();
        $result = $user->getUser($req);
        if (is_array($result)){
            if(count($result) > 0){
                $data = array(
                    'status'=> 1,
                    'message'=> 'Success'
                );
            }
            else {
                $data = array(
                    'status'=> 0,
                    'message'=> 'Not Exist'
                );
            }

        }
        else {
            $data = array(
                'status'=> 0,
                'message'=> 'Request form Error'
            );
        }
        echo json_encode($data);
    }
    public function actionPostUserChangePassword(){
        // change user password
        $req = json_decode($this->getApp()->request->getBody());
        $user = new UserModel();
        $result = $user->changePassword($req);
        if ($result){
            $data = array(
                'status' => 1,
                'message' => 'Success'
            );
        }
        else {
            $data = array(
                'status' => 0,
                'message' => 'Password is not correct. '
            );
        }
        echo json_encode($data);
    }
    public function actionGetUserSensor($user_id){
        $this->getApp()->contentType('application/json');

        $reg = new RegistrationModel();
        $record = $reg->getRegistration($user_id);
        if (is_array($record)){
            $data = array(
                'status' => 1,
                'message' => array()
            );
            foreach($record as $r){
                array_push($data['message'], $r);
            }
        }
        else {
            $data = array(
                'status' => 0,
                'message' => 'Request form is invalid. ',
            );
        }

        echo json_encode($data);
    }
    public function actionPostUserRegisterSensor($user_id){
        // register user's sensor
        $this->getApp()->contentType('application/json');
        $req = json_decode($this->getApp()->request->getBody());
        if(isset($req->{'bd_addr'}) && isset($req->{'name'})){
            $reg = new RegistrationModel();
            try{
                $reg->saveRegistration($user_id, $req);
                $data = array(
                    'status' => 1,
                    'message' => 'Success'
                );
            }
            catch(\Exception $e){
                $data = array(
                    'status' => 0,
                    'message' => $e->getMessage()
                );
            }
        }
        else {
            $data = array(
                'status' => 0,
                'message' => 'Lack of required attributes. '
            );
        }
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