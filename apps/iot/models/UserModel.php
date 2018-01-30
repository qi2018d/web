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


    // inserts a user record
    public function saveUser($req){
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
            throw new \Exception('duplicated username');
        }

        // check email
        $sql = 'SELECT user_id, email
                FROM user
                WHERE email = ?';
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array($email));

        if ($stmt->rowCount() != 0){
            throw new \Exception('duplicated email');
        }

        $passwd_hash = password_hash($password, PASSWORD_DEFAULT);

        $reg_time = $_SERVER['REQUEST_TIME'];

        $sql = "INSERT INTO user (username, email, passwd_hash, birth, gender, reg_time)
                VALUES (:username, :email, :passwd_hash, :birth, :gender, :reg_time)";

        $stmt = $this->getReadConnection()->prepare($sql);

        $stmt->bindParam(':username', $username, FILTER_SANITIZE_STRING);
        $stmt->bindParam(':email', $email, FILTER_SANITIZE_EMAIL);
        $stmt->bindParam(':passwd_hash', $passwd_hash, PASSWORD_BCRYPT);
        $stmt->bindParam(':birth', $birth, \PDO::PARAM_STR);
        $stmt->bindParam(':gender', $gender, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam(':reg_time', $reg_time, \PDO::PARAM_INT);
        $isSuccess = $stmt->execute();


        if (!$isSuccess){
            throw new \Exception($stmt->errorInfo());
        }
        return true;
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
}

