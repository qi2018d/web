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

        $request = json_decode($this->getApp()->request->getBody());

        $user = new UserModel();

        try{
            // user pdo inserts a temporary record
            // and generates verification code and insert the record.
            $temp_user_id = $user->saveTemporaryUser($request);

            // insert a verification code record
            $_SESSION['signup_ver_id'] = $user->makeSignupVerificationCode($temp_user_id, $_SERVER['REQUEST_TIME']);
        }
        catch (\Exception $e){
            $response = array('status' => false, 'code' => $e->getCode(), 'message' => $e->getMessage());
            echo json_encode($response);
            return;
        }

        $verification = $user->getSignupVerificationCode();
        if(!is_bool($verification)){
            try {
                $this->actionSendVerificationCode($verification);

                $response = array('status' => true, 'code' => 1000);
                echo json_encode($response);
            }
            catch (\Exception $e){
                $response = array('status' => false, 'code' => 103, 'message' => $e->getMessage());
                echo json_encode($response);
            }
        }
        else {
            $response = array( 'status' => false, 'code' => 104);
            echo json_encode($response);
        }
    }
    public function actionPostUserSignupVerifyCode(){

        $input_code = json_decode($this->getApp()->request->getBody()) -> {'code'};

        $user = new UserModel();

        $verification = $user->getSignupVerificationCode();

        // check code and time validation
        if ($verification['code'] == trim($input_code)
            && strtotime($verification['valid_date']) > $_SERVER['REQUEST_TIME']) {
            //
            // delete validation record, update user status
            $user->deleteSignupVerificationCode();

            unset($_SESSION['signup_ver_id']);

            $user->updateUserStatusAuth($verification['user_id']);

            $_SESSION['user_id'] = $verification['user_id'];

            $response = array(
                'status' => true,
                'code' => 1000
            );
        }
        else {
            $response = array(
                'status' => false,
                'code' => 2004
            );
        }
        echo json_encode($response);
    }

    /* sign in & sign out */
    public function actionPostUserSignin(){

        $req = json_decode($this->getApp()->request->getBody());

        $identifier = $req->identifier;
        $password = $req->password;

        $user = (new UserModel())->getUserByID($identifier);

        if (is_array($user)) {
            if (password_verify($password, $user['passwd_hash'])) {
                $_SESSION['user_id'] = $user['user_id'];

                echo json_encode(array('status' => true, 'code'=> 1000));
                return;
            } else {
                echo json_encode(array('status' => false, 'code' => 2102));
            }
        } else {
            echo json_encode(array('status' => false, 'code' => 2101));
        }
    }
    public function actionPostUserSignout(){
        unset($_SESSION['user_id']);
        echo json_encode(array('status' => true, 'code'=> 1000));
    }

    /* additional user menu */
    public function actionPostUserExist(){
        //check that user exists
        $this->getApp()->contentType('application/json');
        $request = json_decode($this->getApp()->request->getBody());

        $user = new UserModel();

        $user_record = $user->getUserByID($request);

        if (is_array($user_record)){
            if(count($user_record) > 0){
                // user exist
                $data = array(
                    'status' => true,
                    'code'=> 1000
                );
            }
            else {
                // user not exist
                $data = array(
                    'status' => false,
                    'code'=> 112
                );
            }

        }
        else {
            //  PDO ERROR
            $data = array(
                'status' => false,
                'code'=> 113
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

        $isSuccess = (new UserModel())->changePassword($current_pw, $new_pw);

        if ($isSuccess){
            $data = array(
                "status" => true,
                "code" => 1000
            );
        }
        else {
            $data = array(
                'status' => false,
                'code' => 121
            );
        }
        echo json_encode($data);
    }
    public function actionGetUserCancelID(){

        // change user password
        $this->getApp()->contentType('application/json');

        (new UserModel())->cancelID();
        unset($_SESSION['user_id']);

        session_destroy();
    }

    /* forgot password */
    public function actionPostUserForgotpw(){
        // insert user data with status_auth 0
        // verification code submit to actionPostUserVerifyCode

        $user = new UserModel();

        $this->getApp()->contentType('application/json');

        $email = json_decode($this->getApp()->request->getBody())->email;
        $user_record = $user->getUserByID($email);

        if(is_bool($user_record)){
            $response = array('status' => false, 'code' => 2301);
            echo json_encode($response);
            return;
        }

        $user_id = $user_record['user_id'];
        if (!is_bool($verification_record = $user->isVerificationExist($user_id))){
            $user->deleteForgotpwVerificationCode($verification_record['ver_id']);
        }

        $_SESSION['forgotpw_ver_id'] = $user->makeForgotpwVerificationCode($user_id, $_SERVER['REQUEST_TIME']);
        $verification_record = $user->getForgotpwVerificationCode();

        //echo $_SESSION['forgotpw_ver_id'];

        if(!is_bool($verification_record)){
            //
            try {
                $this->actionSendVerificationCode($verification_record);

                $response = array('status' => true, 'code' => 1000);
            }
            catch (\Exception $e){
                $response = array('status' => false, 'code' => 103, 'message' => $e->getMessage());
            }

        }
        else
            $response = array('status' => false, 'code' => 104);

        echo json_encode($response);
    }
    public function actionPostUserForgotpwVerifyCode(){

        $this->getApp()->contentType('application/json');
        $input_code = json_decode($this->getApp()->request->getBody()) -> {'code'};

        $user = new UserModel();

        $verification_record = $user->getForgotpwVerificationCode();

        // check code validation
        if ($verification_record['code'] == trim($input_code)
            && strtotime($verification_record['valid_date']) > $_SERVER['REQUEST_TIME']) {

            $_SESSION['user_id'] = $verification_record['user_id'];

            // delete validation record, update user status
            $user->deleteForgotpwVerificationCode($_SESSION['forgotpw_ver_id']);

            $response = array(
                'status' => true,
                'code' => 1000
            );
        }
        else {
            $response = array(
                'status' => false,
                'code' => 2302
            );
        }
        echo json_encode($response);
    }
    public function actionPostUserForgotpwChange(){
        // change user password
        $this->getApp()->contentType('application/json');

        $json = json_decode($this->getApp()->request->getBody());
        $new_pw = $json->new_pw;

        $isSuccess = (new UserModel())->changeForgotPassword($_SESSION['user_id'], $new_pw);
        unset($_SESSION['forgotpw_ver_id']);
        unset($_SESSION['user_id']);

        if ($isSuccess){
            $response = array(
                "status" => true,
                "code" => 1000
            );
        }
        else {
            $response = array(
                'status' => false,
                'code' => 121
            );
        }
        echo json_encode($response);
    }

    /* user data */
    public function actionGetCurrentUserInfo(){

        $user_info = (new UserModel())->getCurrentUser();
        $response = array(
            'status' => true,
            'user_id' => $user_info['user_id'],
            'email' => $user_info['email'],
            'username' => $user_info['username']
        );
        echo json_encode($response);
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
                'code' => 1000,
                'message' => array()
            );
            foreach($record as $r){
                array_push($data['message'], $r);
            }
        }
        else {
            $data = array(
                'status' => false,
                'code' => 131
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
                    'code' =>1000
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
                'code' => 132
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
                    'code' => 1000
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
                'code' => 203
            );
        }

        echo json_encode($data);
    }

    public function actionPostUserUpdateSensor($user_id, $reg_id){
        $this->getApp()->contentType('application/json');
        $request = json_decode($this->getApp()->request->getBody());

        if($_SESSION['user_id'] == $user_id) {
            $isSuccess = (new RegistrationModel())->updateRegistrationName($reg_id, $request->{'name'});

            if ($isSuccess)
                $response = array('status' => true, 'code' => 1000);
            else
                $response = array('status' => false, 'code' => 207);

        } else
            $response = array('status' => false, 'code' => 401);

        echo json_encode($response);

    }


    // /api/app/

    public function actionAppPostUserSignup(){

        $request = json_decode($this->getApp()->request->getBody());

        $user = new UserModel();

        try{
            // user pdo inserts a temporary record
            // and generates verification code and insert the record.
            $temp_user_id = $user->saveTemporaryUser($request);

            // insert a verification code record
            $signup_ver_id = $user->makeSignupVerificationCode($temp_user_id, $_SERVER['REQUEST_TIME']);
        }
        catch (\Exception $e){
            $response = array('status' => false, 'code' => $e->getCode(), 'message' => $e->getMessage());
            echo json_encode($response);
            return;
        }

        $verification = $user->getAppSignupVerificationCode($signup_ver_id);
        if(!is_bool($verification)){
            try {
                $this->actionSendVerificationCode($verification);

                $response = array('status' => true, 'code' => 1000, 'message' => array('signup_ver_id' => $signup_ver_id));
                echo json_encode($response);
            }
            catch (\Exception $e){
                $response = array('status' => false, 'code' => 103, 'message' => $e->getMessage());
                echo json_encode($response);
            }
        }
        else {
            $response = array( 'status' => false, 'code' => 104);
            echo json_encode($response);
        }
    }

    public function actionAppPostUserSignupVerifyCode(){

        $input_code = json_decode($this->getApp()->request->getBody()) -> {'code'};
        $signup_ver_id = json_decode($this->getApp()->request->getBody()) -> {'signup_ver_id'};

        $user = new UserModel();

        $verification = $user->getAppSignupVerificationCode($signup_ver_id);

        // check code and time validation
        if ($verification['code'] == trim($input_code)
            && strtotime($verification['valid_date']) > $_SERVER['REQUEST_TIME']) {
            //
            // delete validation record, update user status
            $user->deleteAppSignupVerificationCode($signup_ver_id);


            $user->updateUserStatusAuth($verification['user_id']);

            $user_id = $verification['user_id'];

            $response = array(
                'status' => true,
                'code' => 1000,
                'message' => array('user_id' => $user_id)
            );
        }
        else {
            $response = array(
                'status' => false,
                'code' => 2004
            );
        }
        echo json_encode($response);
    }

    public function actionAppPostUserSignin(){

        $req = json_decode($this->getApp()->request->getBody());

        $identifier = $req->identifier;
        $password = $req->password;

        $user = (new UserModel())->getUserByID($identifier);

        if (is_array($user)) {
            if (password_verify($password, $user['passwd_hash'])) {
                echo json_encode(array('status' => true, 'code'=> 1000, 'message' => array('user_id' => $user['user_id'])));
                return;
            } else {
                echo json_encode(array('status' => false, 'code' => 2102));
            }
        } else {
            echo json_encode(array('status' => false, 'code' => 2101));
        }
    }

    public function actionAppGetUserCancelID($user_id){
        // change user password
        $this->getApp()->contentType('application/json');
        (new UserModel())->appCancelID($user_id);
        unset($_SESSION['user_id']);
        session_destroy();
    }


    public function actionAppPostUserChangePassword(){
        // change user password
        $this->getApp()->contentType('application/json');

        $json = json_decode($this->getApp()->request->getBody());
        $current_pw = $json->current_pw;
        $new_pw = $json->new_pw;
        $user_id = $json->user_id;

        $isSuccess = (new UserModel())->appChangePassword($user_id, $current_pw, $new_pw);

        if ($isSuccess){
            $data = array(
                "status" => true,
                "code" => 1000
            );
        }
        else {
            $data = array(
                'status' => false,
                'code' => 121
            );
        }
        echo json_encode($data);
    }

    public function actionAppPostUserForgotpw(){
        // insert user data with status_auth 0
        // verification code submit to actionPostUserVerifyCode

        $user = new UserModel();

        $this->getApp()->contentType('application/json');

        $email = json_decode($this->getApp()->request->getBody())->email;
        $user_record = $user->getUserByID($email);
        $user_id = $user_record['user_id'];

        $forgotpw_ver_id = $user->makeForgotpwVerificationCode($user_id, $_SERVER['REQUEST_TIME']);
        $verification_record = $user->getAppForgotpwVerificationCode($forgotpw_ver_id);

        if(!is_bool($verification_record)){
            //
            try {
                $this->actionSendVerificationCode($verification_record);

                $response = array('status' => true, 'code' => 1000, 'message' => array('forgotpw_ver_id' => $forgotpw_ver_id));
            }
            catch (\Exception $e){
                $response = array('status' => false, 'code' => 103, 'message' => $e->getMessage());
            }

        }
        else
            $response = array('status' => false, 'code' => 104);

        echo json_encode($response);
    }

    public function actionAppPostUserForgotpwVerifyCode(){

        $this->getApp()->contentType('application/json');
        $input_code = json_decode($this->getApp()->request->getBody()) -> {'code'};
        $forgotpw_ver_id = json_decode($this->getApp()->request->getBody()) -> {'forgotpw_ver_id'};

        $user = new UserModel();

        $verification_record = $user->getAppForgotpwVerificationCode($forgotpw_ver_id);

        // check code validation
        if ($verification_record['code'] == trim($input_code)
            && strtotime($verification_record['valid_date']) > $_SERVER['REQUEST_TIME']) {

            $user_id = $verification_record['user_id'];

            // delete validation record, update user status
            $user->deleteAppForgotpwVerificationCode($forgotpw_ver_id);

            $response = array(
                'status' => true,
                'code' => 1000,
                'message' => array('user_id' => $user_id)
            );
        }
        else {
            $response = array(
                'status' => false,
                'code' => 2302
            );
        }
        echo json_encode($response);
    }

    public function actionAppPostUserForgotpwChange(){
        // change user password
        $this->getApp()->contentType('application/json');

        $json = json_decode($this->getApp()->request->getBody());

        $user_id = $json->user_id;
        $new_pw = $json->new_pw;

        $isSuccess = (new UserModel())->changeForgotPassword($user_id, $new_pw);
        unset($_SESSION['forgotpw_ver_id']);
        unset($_SESSION['user_id']);

        if ($isSuccess){
            $response = array(
                "status" => true,
                "code" => 1000
            );


        }
        else {
            $response = array(
                'status' => false,
                'code' => 121
            );
        }
        echo json_encode($response);
    }

    public function actionAppPostUserUpdateSensor($user_id, $reg_id){
        $this->getApp()->contentType('application/json');
        $request = json_decode($this->getApp()->request->getBody());

//        if($_SESSION['user_id'] == $user_id) {
            $isSuccess = (new RegistrationModel())->updateRegistrationName($reg_id, $request->{'name'});

            if ($isSuccess)
                $response = array('status' => true, 'code' => 1000);
            else
                $response = array('status' => false, 'code' => 207);

//        } else
//            $response = array('status' => false, 'code' => 401);

        echo json_encode($response);

    }

    public function actionAppGetCurrentUserInfo($user_id){
        $user_info = (new UserModel())->getAppCurrentUser($user_id);
        $response = array(
            'status' => true,
            'code' => 1000,
            'message' => array('user_id' => $user_info['user_id'],
                'email' => $user_info['email'],
                'username' => $user_info['username'])
        );
        echo json_encode($response);
    }



}