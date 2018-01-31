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
    // get a user record with email or username
    public function getUser($req){
        if(isset($req->{'email'})){
            // if req has email
            $email = $req->{'email'};
            $sql = "SELECT user_id, email, username, passwd_hash
                    FROM user
                    WHERE email = ?";
            $stmt = $this->getReadConnection()->prepare($sql);
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
            $stmt->execute(array(strval($email)));
        }
        else {
            // if req has username
            $username = $req->{'username'};
            $sql = "SELECT user_id, email, username, passwd_hash
                    FROM user
                    WHERE username = ?";
            $stmt = $this->getReadConnection()->prepare($sql);
            $stmt->setFetchMode(\PDO::FETCH_ASSOC);
            $stmt->execute(array(strval($username)));
        }

        return $stmt->fetch();
    }


    // inserts a user record temporarily (status_auth = 0)
    // if success, return user_id of record
    // else return false;
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
        $this->makeVerificationCode($this->getReadConnection()->lastInsertId(), $reg_timestamp);

        $this->getReadConnection()->commit();
        // =============================================================================
        return $user_id;
    }

    public function changePassword($req){
        // if req has email
        $user_id = $req->{'user-id'};
        $old_passwd = $req->{'old-password'};
        $new_passwd = $req->{'new-password'};

        // check old-password is correct
        $sql = "SELECT user_id, email, username, passwd_hash
                FROM user
                WHERE user_id = ?";
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array($user_id));
        $user = $stmt->fetch();
        if ($stmt->rowCount() > 0){
            if (password_verify($old_passwd, $user['passwd_hash'])){
                // if correct, update to new-password
                $sql = "UPDATE user
                SET passwd_hash = ?
                WHERE user_id = ?";
                $stmt = $this->getReadConnection()->prepare($sql);
                $stmt->setFetchMode(\PDO::FETCH_ASSOC);
                return $stmt->execute(array(password_hash($new_passwd, PASSWORD_DEFAULT), $user_id));
            }
            else {
                // if not correct
                return false;
            }
        }
    }

    // if success, return ver_id of record
    // else return false;
    private function makeVerificationCode($user_id, $reg_timestamp){
        srand($_SERVER['REQUEST_TIME']);
        $code = chr(rand(65,90)) . chr(rand(65,90)) . chr(rand(65,90)) . chr(rand(65,90)) . chr(rand(65,90));
        $valid_timestamp = $reg_timestamp + 3600;
        $valid_datetime = date('Y-m-d H:i:s', $valid_timestamp);

        $sql = "INSERT INTO ver_code (user_id, code, valid_date)
                VALUES (:user_id, :code, :valid_date)";
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam(':code', $code, FILTER_SANITIZE_STRING);
        $stmt->bindParam(':valid_date', $valid_datetime, FILTER_SANITIZE_STRING);
        $isSuccess = $stmt->execute();

        if(!$isSuccess){
            $this->getReadConnection()->rollBack();
            throw new \Exception($stmt->errorInfo(), $stmt->errorCode());
        }
        return $this->getReadConnection()->lastInsertId();
    }

    // return array of ver_code record
    public function getVerificationCode($user_id){
        $sql = 'SELECT email, username, code, valid_date
                FROM ver_code NATURAL JOIN user 
                WHERE user_id = ?';
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array($user_id));
        if ($stmt->rowCount() == 0){
            return false;
        }
        else {
            return $stmt->fetch();
        }

    }
    public function deleteVerificationCode($user_id){
        $sql = 'DELETE FROM ver_code            
                WHERE user_id = ?';
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->execute(array($user_id));
        if ($stmt->rowCount() == 0){
            return false;
        }
        else {
            return true;
        }
    }
}

