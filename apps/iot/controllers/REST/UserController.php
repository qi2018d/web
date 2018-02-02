<?php
/**
 * Created by PhpStorm.
 * User: leo
 * Date: 2018-01-28
 * Time: 오후 4:14
 */

namespace Iot\Controller\REST;
use Iot\Model\RegistrationModel;
use Iot\Model\UserModel;

use PHPMailer\PHPMailer\PHPMailer;
use Slimvc\Core\Controller;

class UserController extends Controller
{
    /* sign up */
    public function actionPostUserSignup(){
        // insert user data with status_auth 0
        // verification code submit to actionPostUserVerifyCode

        $this->getApp()->contentType('application/json');
        $req = json_decode($this->getApp()->request->getBody());
        $user = new UserModel();
        //
        try{
            // user pdo inserts a temporary record
            // and generates verification code and insert the record.
            $user->saveTemporaryUser($req);
        }
        catch (\Exception $e){
            $res = array(
                'status' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            );
            echo json_encode($res);
            exit;
        }

        $verification = $user->getSignupVerificationCode();
        if(!is_bool($verification)){
            try {
                $this->actionSendVerificationCode($verification);

                $res = array(
                    'status' => true,
                    'code' => 100,
                    'message' => 'Success'
                );
                echo json_encode($res);
            }
            catch (\Exception $e){
                $res = array(
                    'status' => false,
                    'code' => 103,
                    'message' => 'Something wrong to send a mail. ' . $e->getMessage()
                );
                echo json_encode($res);
            }
        }
        else {
            $res = array(
                'status' => false,
                'code' => 104,
                'message' => 'ver_code is not generated.'
            );
            echo json_encode($res);
        }
    }
    public function actionPostUserSignupVerifyCode(){

        $this->getApp()->contentType('application/json');
        $input_code = json_decode($this->getApp()->request->getBody()) -> {'code'};

        $user = new UserModel();

        $verification = $user->getSignupVerificationCode();

        // check code validation
        if ($verification['code'] == trim($input_code)
            && strtotime($verification['valid_date']) > $_SERVER['REQUEST_TIME']) {

            // delete validation record, update user status
            $user->deleteSignupVerificationCode();
            unset($_SESSION['signup_ver_id']);

            $user->updateUserStatusAuth($verification['user_id']);

            $_SESSION['user_id'] = $verification['user_id'];

            $res = array(
                'status' => true,
                'code' => 100,
                'message' => 'Success'
            );
        }
        else {
            $res = array(
                'status' => false,
                'code' => 0,
                'message' => 'Code is invalid. '
            );
        }
        echo json_encode($res);
    }

    /* sign in & sign out */
    public function actionPostUserSignin(){
        // check email(or username) and password
        $this->getApp()->contentType('application/json');
        $req = json_decode($this->getApp()->request->getBody());

        $user = new UserModel();
        $result = $user->getUser($req->{'identifier'});

        if (is_array($result)){
            if (password_verify($req->{'password'}, $result['passwd_hash'])){

                $_SESSION['user_id'] = $result['user_id'];

                $data = array(
                    'status' => true,
                    'code'=> 100,
                    'message'=> 'Success'
                );
            }
            else {
                $data = array(
                    'status' => false,
                    'code'=> 111,
                    'message'=> 'password is incorrect. '
                );
            }
        }
        else {
            $data = array(
                'status' => false,
                'code'=> 111,
                'message'=> 'user name is incorrect. '
            );
        }
        echo json_encode($data);
    }
    public function actionPostUserSignout(){
        unset($_SESSION['user_id']);
        //$this->getApp()->redirect('/');
    }

    /* additional user menu */
    public function actionPostUserExist(){
        //check that user exists
        $this->getApp()->contentType('application/json');
        $req = json_decode($this->getApp()->request->getBody());
        $user = new UserModel();
        $result = $user->getUser($req);
        if (is_array($result)){
            if(count($result) > 0){
                $data = array(
                    'status' => true,
                    'code'=> 100,
                    'message'=> 'Success'
                );
            }
            else {
                $data = array(
                    'status' => false,
                    'code'=> 112,
                    'message'=> 'Not Exist'
                );
            }

        }
        else {
            $data = array(
                'status' => false,
                'code'=> 113,
                'message'=> 'Request form error'
            );
        }
        echo json_encode($data);
    }
    public function actionPostUserChangePassword(){
        // change user password
        $this->getApp()->contentType('application/json');


        $json = json_decode($this->getApp()->request->getBody());
        $current_pw = $json->current_pw;
        $new_pw = $json->new_pw;

        $result = (new UserModel())->changePassword($current_pw, $new_pw);

        if ($result){
            $data = array(
                "status" => true,
                "code" => 100,
                "message" => "Success"
            );
            echo json_encode($data);

        }
        else {
            $data = array(
                'status' => false,
                'code' => 121,
                'message' => 'Current password is not correct'
            );
            echo json_encode($data);
        }

    }
    public function actionGetUserCancelID(){
        // change user password
        $this->getApp()->contentType('application/json');
        (new UserModel())->cancelID();
        unset($_SESSION['user_id']);
    }


    /* forgot password */
    public function actionPostUserForgotpw(){
        // insert user data with status_auth 0
        // verification code submit to actionPostUserVerifyCode

        $user = new UserModel();

        $this->getApp()->contentType('application/json');

        $email = json_decode($this->getApp()->request->getBody())->email;
        $record = $user->getUser($email);
        $user_id = $record['user_id'];


        $user->makeForgotpwVerificationCode($user_id, $_SERVER['REQUEST_TIME']);
        $verification = $user->getForgotpwVerificationCode();

        //echo $_SESSION['forgotpw_ver_id'];

        if(!is_bool($verification)){
            try {
                $this->actionSendVerificationCode($verification);

                $res = array(
                    'status' => true,
                    'code' => 100,
                    'message' => 'Success'
                );
                echo json_encode($res);
            }
            catch (\Exception $e){
                $res = array(
                    'status' => false,
                    'code' => 103,
                    'message' => 'Something wrong to send a mail. ' . $e->getMessage()
                );
                echo json_encode($res);
            }
        }
        else {
            $res = array(
                'status' => false,
                'code' => 104,
                'message' => 'ver_code is not generated.'
            );
            echo json_encode($res);
        }

    }
    public function actionPostUserForgotpwVerifyCode(){

        $this->getApp()->contentType('application/json');
        $input_code = json_decode($this->getApp()->request->getBody()) -> {'code'};

        $user = new UserModel();

        $verification = $user->getForgotpwVerificationCode();

        // check code validation
        if ($verification['code'] == trim($input_code)
            && strtotime($verification['valid_date']) > $_SERVER['REQUEST_TIME']) {

            $_SESSION['user_id'] = $verification['user_id'];

            // delete validation record, update user status
            $user->deleteForgotpwVerificationCode();

            $res = array(
                'status' => true,
                'code' => 100,
                'message' => 'Success'
            );
        }
        else {
            $res = array(
                'status' => false,
                'code' => 0,
                'message' => 'Code is invalid. '
            );
        }
        echo json_encode($res);
    }
    public function actionPostUserForgotpwChange(){
        // change user password
        $this->getApp()->contentType('application/json');

        $json = json_decode($this->getApp()->request->getBody());
        $new_pw = $json->new_pw;

        $result = (new UserModel())->changeForgotPassword($new_pw);
        unset($_SESSION['forgotpw_ver_id']);
        unset($_SESSION['user_id']);

        if ($result){
            $data = array(
                "status" => true,
                "code" => 100,
                "message" => "Success"
            );
            echo json_encode($data);

        }
        else {
            $data = array(
                'status' => false,
                'code' => 121,
                'message' => 'Current password is not correct'
            );
            echo json_encode($data);
        }

    }

    /* user data */
    public function actionGetCurrentUserInfo(){

        $user_info = (new UserModel())->getCurrentUser();
        $res = array(
            'status' => true,
            'user_id' => $user_info['user_id'],
            'email' => $user_info['email'],
            'username' => $user_info['username']
        );
        echo json_encode($res);
    }
    public function actionGetUserInfo(){
        //TODO
    }


    private function actionSendVerificationCode($ver_code_record){
        //SMTP needs accurate times, and the PHP time zone MUST be set
        //This should be done in your php.ini, but this is how to do it if you don't have access to that
        date_default_timezone_set('Etc/UTC');

        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->Debugoutput = 'json';
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 587;
        $mail->SMTPSecure = 'tls';
        $mail->SMTPAuth = true;
        $mail->Username = "unsecureleo@gmail.com";
        $mail->Password = "teambaam!";
        $mail->setFrom('unsecureleo@gmail.com', 'Sender');
        $mail->addAddress($ver_code_record['email'], $ver_code_record['username']);
        //$mail->addAddress($record['email'], $record['username']);

        //Set the subject line
        $mail->Subject = 'Verification Code For Registering Account ';

        include '../../public/iot/mail_contents.php';
        $mail->msgHTML(getMailContents($ver_code_record['code']));
        if (!$mail->send()) {
            throw new \Exception($mail->ErrorInfo);
        } else {
            return true;
        }
    }

    public function actionGetUserSensor($user_id){
        // show all sensors of users.
        $this->getApp()->contentType('application/json');

        $reg = new RegistrationModel();
        $record = $reg->getRegistration($user_id);
        if (is_array($record)){
            $data = array(
                'status' => true,
                'code' => 100,
                'message' => array()
            );
            foreach($record as $r){
                array_push($data['message'], $r);
            }
        }
        else {
            $data = array(
                'status' => false,
                'code' => 131,
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
                    'status' => true,
                    'code' =>100,
                    'message' => 'Success'
                );
            }
            catch(\Exception $e){
                $data = array(
                    'status' => false,
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                );
            }
        }
        else {
            $data = array(
                'status' => false,
                'code' => 132,
                'message' => 'Lack of required attributes. '
            );
        }
        echo json_encode($data);
    }
    public function actionPostUserDeregisterSensor($user_id){
        // deregister the sensor with user_id, json_data
        $this->getApp()->contentType('application/json');
        $req = json_decode($this->getApp()->request->getBody());

        if(isset($req->{'bd_addr'})){
            $reg = new RegistrationModel();
            try{
                $reg->deleteRegistration($user_id, $req);
                $data = array(
                    'status' => true,
                    'code' => 100,
                    'message' => 'Success'
                );
            }
            catch(\Exception $e){
                $data = array(
                    'status' => false,
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                );
            }
        }
        else {
            $data = array(
                'status' => false,
                'code' => 0,
                'message' => 'Submitted form is invalid. '
            );
        }

        echo json_encode($data);
    }
}