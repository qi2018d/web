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
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Slimvc\Core\Controller;

class UserController extends Controller
{
    public function actionPostUserSignup(){
        // insert user data with status_auth 0
        // verification code submit to actionPostUserVerifyCode

        $this->getApp()->contentType('text/html');
        $req = json_decode($this->getApp()->request->getBody());
        $user = new UserModel();
        //
        try{
            // user pdo inserts a temporary record
            // and generates verification code and insert the record.
            $user_id = $user->saveTemporaryUser($req);
        }
        catch (\Exception $e){
            $data = array(
                'status' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            );
            echo json_encode($data);
            exit;
        }

        $ver_record = $user->getVerificationCode($user_id);
        if(!is_bool($ver_record)){
            try {
                $this->actionSendVerificationCode($ver_record);
                $data = array(
                    'status' => true,
                    'code' => 100,
                    'message' => 'Success'
                );
                echo json_encode($data);
            }
            catch (\Exception $e){
                $data = array(
                    'status' => false,
                    'code' => 103,
                    'message' => 'Something wrong to send a mail. '
                );
                echo json_encode($data);
            }
        }
        else {
            $data = array(
                'status' => false,
                'code' => 104,
                'message' => 'ver_code is not generated.'
            );
            echo json_encode($data);
        }


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
                    'status' => true,
                    'code'=> 100,
                    'message'=> 'Success'
                );
            }
            else {
                $data = array(
                    'status' => false,
                    'code'=> 111,
                    'message'=> 'username or password is incorrect. '
                );
            }
        }
        else {
            $data = array(
                'status' => false,
                'code'=> 111,
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
        $req = json_decode($this->getApp()->request->getBody());
        $user = new UserModel();
        $result = $user->changePassword($req);
        if ($result){
            $data = array(
                'status' => true,
                'code' => 100,
                'message' => 'Success'
            );
        }
        else {
            $data = array(
                'status' => false,
                'code' => 121,
                'message' => 'Password is not correct. '
            );
        }
        echo json_encode($data);
    }
    public function actionGetUserSensor($user_id){
        // show all sensors of users.
        $this->getApp()->contentType('text/html');

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
    public function actionPostUserVerifyCode(){
        $this->getApp()->contentType('text/html');
        $req = json_decode($this->getApp()->request->getBody());
        $user = new UserModel();
        $record = $user->getVerificationCode($req->{'user-id'});
        // check code validation
        if ($record['code'] == trim($req->{'code'}) &&
            strtotime($record['valid_date']) > $_SERVER['REQUEST_TIME']) {
            // delete a ver_code record with user-id
            $user->deleteVerificationCode($req->{'user-id'});
            $data = array(
                'status' => true,
                'code' => 100,
                'message' => 'Success'
            );
        }
        else {
            $data = array(
                'status' => false,
                'code' => 0,
                'message' => 'Code is invalid. '
            );
        }
        echo json_encode($data);
    }
    private function actionSendVerificationCode($ver_code_record){
        //SMTP needs accurate times, and the PHP time zone MUST be set
        //This should be done in your php.ini, but this is how to do it if you don't have access to that
        date_default_timezone_set('PST');

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
}