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
        $this->getApp()->contentType('application/json');
        $req = json_decode($this->getApp()->request->getBody());
        $user = new UserModel();
        //
        try{
            $user_id = $user->saveTemporaryUser($req);
        }
        catch (\Exception $e){
            $data = array(
                'status' => 0,
                'message' => $e->getMessage()
            );
            echo json_encode($data);
            exit;
        }

        $ver_record = $user->getVerificationCode($user_id);
        if(!is_bool($ver_record)){
            try {
                $this->actionSendVerificationCode($ver_record['code']);
            }
            catch (\Exception $e){
                $data = array(
                    'status' => 0,
                    'message' => 'Something wrong to send a mail. '
                );
            }
            $data = array(
                'status' => 1,
                'message' => 'Success'
            );
            echo json_encode($data);
        }
        else {
            $data = array(
                'status' => 0,
                'message' => 'ver_code is not exist.'
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
        // show all sensors of users.
        $this->getApp()->contentType('text/html');

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
    public function actionPostUserDeregisterSensor($user_id){
        // deregister the sensor with user_id, json_data
        $this->getApp()->contentType('application/json');
        $req = json_decode($this->getApp()->request->getBody());

        if(isset($req->{'bd_addr'})){
            $reg = new RegistrationModel();
            try{
                $reg->deleteRegistration($user_id, $req);
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
                'message' => 'Submitted form is invalid. '
            );
        }

        echo json_encode($data);
    }

    public function actionSendVerificationCode($ver_code){
        //SMTP needs accurate times, and the PHP time zone MUST be set
        //This should be done in your php.ini, but this is how to do it if you don't have access to that
        date_default_timezone_set('Etc/UTC');

        //Create a new PHPMailer instance
        $mail = new PHPMailer();
        //Tell PHPMailer to use SMTP
        $mail->isSMTP();

        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $mail->SMTPDebug = 0;

        //Ask for HTML-friendly debug output
        $mail->Debugoutput = 'json';

        //Set the hostname of the mail server
        $mail->Host = 'smtp.gmail.com';
        // use
        // $mail->Host = gethostbyname('smtp.gmail.com');
        // if your network does not support SMTP over IPv6

        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $mail->Port = 587;

        //Set the encryption system to use - ssl (deprecated) or tls
        $mail->SMTPSecure = 'tls';

        //Whether to use SMTP authentication
        $mail->SMTPAuth = true;
        //Username to use for SMTP authentication - use full email address for gmail
        $mail->Username = "unsecureleo@gmail.com";
        //Password to use for SMTP authentication
        $mail->Password = "le0park!";
        //Set who the message is to be sent from
        $mail->setFrom('unsecureleo@gmail.com', 'Leo Park');

        //Set who the message is to be sent to
        $mail->addAddress('carpe0308@naver.com', 'Leo PARK');
        //$mail->addAddress($record['email'], $record['username']);

        //Set the subject line
        $mail->Subject = 'Verification Code For Registering Account ';

        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body
//        $mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
        include '../../public/iot/mail_contents.php';
        $mail->msgHTML(getMailContents($ver_code));
        //Replace the plain text body with one created manually
        //$mail->AltBody = 'This is a plain-text message body';

        //send the message, check for errors
        if (!$mail->send()) {
            throw new \Exception($mail->ErrorInfo);
        } else {
            return true;
        }
        //Attach an image file
//        $mail->addAttachment('images/phpmailer_mini.png');
    }
}