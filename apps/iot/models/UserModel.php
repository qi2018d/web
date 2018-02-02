<?php
/**
 * Created by PhpStorm.
 * User: leo
 * Date: 2018-01-29
 * Time: 오전 10:28
 */

namespace Iot\Model;
use Slimvc\Core\Model;

class UserModel extends Model
{

    /* sign up */
    public function saveTemporaryUser($req){

        $username = $req->{'username'};
        $email = $req->{'email'};
        $password = $req->{'password'};
        $birth = $req->{'birthdate'};
        $gender = $req->{'gender'};

        // check username
        $sql = 'SELECT user_id, username
                FROM user
                WHERE username = ?';
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array($username));

        if ($stmt->rowCount() != 0){
            throw new \Exception('duplicated username', 101);
        }

        // check that email duplicated
        $sql = 'SELECT user_id, email
                FROM user
                WHERE email = ?';
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array($email));
        if ($stmt->rowCount() != 0){
            throw new \Exception('duplicated email', 102);
        }

        $passwd_hash = password_hash($password, PASSWORD_DEFAULT);
        $reg_timestamp = $_SERVER['REQUEST_TIME'];
        $reg_datetime = date('Y-m-d H:i:s', $reg_timestamp);
        // =============================================================================
        $this->getReadConnection()->beginTransaction();
        // query for insert user record
        $sql = "INSERT INTO user (username, email, passwd_hash, birth, gender, reg_time)
                VALUES (:username, :email, :passwd_hash, :birth, :gender, :reg_time)";
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->bindParam(':username', $username, FILTER_SANITIZE_STRING);
        $stmt->bindParam(':email', $email, FILTER_SANITIZE_EMAIL);
        $stmt->bindParam(':passwd_hash', $passwd_hash, PASSWORD_BCRYPT);
        $stmt->bindParam(':birth', $birth, \PDO::PARAM_STR);
        $stmt->bindParam(':gender', $gender, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam(':reg_time', $reg_datetime,FILTER_SANITIZE_STRING);
        $isSuccess = $stmt->execute();
        $user_id = $this->getReadConnection()->lastInsertId();
        if (!$isSuccess){
            $this->getReadConnection()->rollBack();
            throw new \Exception($stmt->errorInfo(), $stmt->errorCode());
        }
        // insert a verification code record
        $this->makeSignupVerificationCode($this->getReadConnection()->lastInsertId(), $reg_timestamp);

        $this->getReadConnection()->commit();
        // =============================================================================
        return $user_id;
    }
    private function makeSignupVerificationCode($user_id, $reg_timestamp){
        srand($_SERVER['REQUEST_TIME']);
        $code = chr(rand(65,90)) . chr(rand(65,90)) . chr(rand(65,90)) . chr(rand(65,90)) . chr(rand(65,90));
        $valid_timestamp = $reg_timestamp + 3600;
        $valid_datetime = date('Y-m-d H:i:s', $valid_timestamp);

        $sql = "INSERT INTO ver_code (user_id, code, valid_date)
                VALUES (:user_id, :code, :valid_date)";

        $pdo = $this->getReadConnection();
        $stmt = $pdo ->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam(':code', $code, FILTER_SANITIZE_STRING);
        $stmt->bindParam(':valid_date', $valid_datetime, FILTER_SANITIZE_STRING);
        $isSuccess = $stmt->execute();

        if(!$isSuccess){
            $this->getReadConnection()->rollBack();
            throw new \Exception($stmt->errorInfo(), $stmt->errorCode());
        }
        $_SESSION['signup_ver_id'] = $this->getReadConnection()->lastInsertId();
    }
    public function getSignupVerificationCode(){

        $sql = 'SELECT ver_id, user_id, email, username, code, valid_date
                FROM ver_code NATURAL JOIN user 
                WHERE ver_id = ?';

        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array($_SESSION['signup_ver_id']));
        if ($stmt->rowCount() == 0){
            return false;
        }
        else {
            return $stmt->fetch();
        }
    }
    public function deleteSignupVerificationCode(){
        $sql = 'DELETE FROM ver_code            
                WHERE ver_id = ?';
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->execute(array($_SESSION['signup_ver_id']));

        if ($stmt->rowCount() == 0){
            return false;
        }
        else {
            return true;
        }
    }
    public function updateUserStatusAuth($user_id){
        $sql = 'UPDATE user
                SET status_auth = 1
                WHERE user_id = ?';
        $stmt = $this->getReadConnection()->prepare($sql);
        $isSuccess = $stmt->execute(array($user_id));

        if($isSuccess){
            return true;
        }
        else {
            return false;
        }
    }

    /*forgot pw */
    public function makeForgotpwVerificationCode($user_id, $reg_timestamp){
        srand($_SERVER['REQUEST_TIME']);
        $code = chr(rand(65,90)) . chr(rand(65,90)) . chr(rand(65,90)) . chr(rand(65,90)) . chr(rand(65,90));
        $valid_timestamp = $reg_timestamp + 3600;
        $valid_datetime = date('Y-m-d H:i:s', $valid_timestamp);

        $sql = "INSERT INTO ver_code (user_id, code, valid_date)
                VALUES (:user_id, :code, :valid_date)";

        $pdo = $this->getReadConnection();
        $stmt = $pdo ->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam(':code', $code, FILTER_SANITIZE_STRING);
        $stmt->bindParam(':valid_date', $valid_datetime, FILTER_SANITIZE_STRING);
        $stmt->execute();

        $_SESSION['forgotpw_ver_id'] = $this->getReadConnection()->lastInsertId();
    }

    public function getForgotpwVerificationCode(){

        $sql = 'SELECT ver_id, user_id, email, username, code, valid_date
                FROM ver_code NATURAL JOIN user 
                WHERE ver_id = ?';

        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array($_SESSION['forgotpw_ver_id']));
        if ($stmt->rowCount() == 0){
            return false;
        }
        else {
            return $stmt->fetch();
        }
    }
    public function deleteForgotpwVerificationCode(){
        $sql = 'DELETE FROM ver_code            
                WHERE ver_id = ?';
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->execute(array($_SESSION['forgotpw_ver_id']));
        if ($stmt->rowCount() == 0){
            return false;
        }
        else {
            return true;
        }
    }
    public function changeForgotPassword($new_pw){

        $user_id = $_SESSION['user_id'];

        $sql = "UPDATE user 
                SET passwd_hash = ?
                WHERE user_id = ?";
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        return $stmt->execute(array(password_hash($new_pw, PASSWORD_DEFAULT), $user_id));
    }

    /* change pw */
    public function changePassword($current_pw, $new_pw){
        // if req has email

        $user_id = $_SESSION['user_id'];

        // check old-password is correct
        $sql = "SELECT user_id, email, username, passwd_hash
                FROM user
                WHERE user_id = ?";
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array($user_id));
        $user = $stmt->fetch();

        if ($stmt->rowCount() > 0){
            if (password_verify($current_pw, $user['passwd_hash'])){
                // if correct, update to new-password
                $sql = "UPDATE user 
                        SET passwd_hash = ?
                        WHERE user_id = ?";
                $stmt = $this->getReadConnection()->prepare($sql);
                $stmt->setFetchMode(\PDO::FETCH_ASSOC);
                return $stmt->execute(array(password_hash($new_pw, PASSWORD_DEFAULT), $user_id));
            }
            else {
                // if not correct
                return false;
            }
        }
        return false;
    }


    public function cancelID(){
        $user_id = $_SESSION['user_id'];

        $sql = "DELETE 
                from user
                WHERE user_id = ?";
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array($user_id));
    }

    // get a user record with email or username
    public function getCurrentUser(){
        $sql = "SELECT user_id, email, username, passwd_hash
                FROM user
                WHERE user_id = ?";
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array(strval($_SESSION['user_id'])));

        return $stmt->fetch();
    }
    public function getUser($identifier){

        $sql = "SELECT user_id, email, username, passwd_hash
                FROM user
                WHERE email = ? OR username = ?";
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array(strval($identifier), strval($identifier)));

        return $stmt->fetch();
    }


}

