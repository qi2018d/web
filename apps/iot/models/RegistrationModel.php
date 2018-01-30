<?php
/**
 * Created by PhpStorm.
 * User: leo
 * Date: 2018-01-29
 * Time: 오후 10:47
 */

namespace Iot\Model;


use Slimvc\Core\Model;

class RegistrationModel extends Model
{
    public function getRegistration($user_id){
        $sql = 'SELECT name, hex(mac_addr) AS mac_addr
                FROM registration
                WHERE user_id = ?';
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array($user_id));
        if($stmt->rowCount() >= 0){
            return $stmt->fetchAll();
        }
        else {
            return false;
        }
    }

    public function saveRegistration($user_id, $req){
        $mac_addr = str_replace(':', '', $req->{'bd_addr'});
        if(strlen($mac_addr) != 12){
            throw new \Exception('mac_addr is invalid. ');
        }

        $name = $req->{'name'};
        $sql = 'SELECT reg_id
                FROM registration
                WHERE user_id = ? AND CONV(mac_addr, 10, 16) = ?';
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array($user_id, $mac_addr));
        if($stmt->rowCount() > 0){
            // already exist
            throw new \Exception('mac_addr is already exist. ');
        }
        else {
            $sql = 'INSERT INTO registration (user_id, mac_addr, name)
                    VALUE (:user_id, CONV(:mac_addr, 16, 10), :name)';
            $stmt = $this->getReadConnection()->prepare($sql);
            $stmt->bindParam('user_id', $user_id, FILTER_SANITIZE_NUMBER_INT);
            $stmt->bindParam('mac_addr', $mac_addr, FILTER_SANITIZE_STRING);
            $stmt->bindParam('name', $name, FILTER_SANITIZE_STRING);
            $status = $stmt->execute();
            if ($status){
                return true;
            }
            else {
                throw new \Exception('submit form is invalid. ');
            }
        }
    }
}