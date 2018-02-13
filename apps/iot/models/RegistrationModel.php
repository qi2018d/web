<?php
/**
 * Created by PhpStorm.
 * User: leo
 * Date: 2018-01-29
 * Time: 오후 10:47
 */

namespace Iot\Model;


use Slimvc\Core\Model;
include '../../public/iot/functions.php';

class RegistrationModel extends Model
{
    public function getRegistration($user_id){
        // need mac address formatting
        $sql = 'SELECT reg_id, name, LPAD(HEX(bd_addr), 12, "0") AS bd_addr
                FROM registration
                WHERE user_id = ?';
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array($user_id));

        if($stmt->rowCount() >= 0){
            $reg_records = $stmt->fetchAll();
            return $reg_records;
        }
        else {
            return false;
        }
    }

    public function saveRegistration($user_id, $req){
        $bd_addr = mac_address_str2hex($req->{'bd_addr'});
        if(strlen($bd_addr) != 12){
            throw new \Exception('bd_addr is invalid. ', 201);
        }

        $name = $req->{'name'};
        $sql = 'SELECT reg_id
                FROM registration
                WHERE user_id = ? AND CONV(bd_addr, 10, 16) = ?';
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array($user_id, $bd_addr));
        if($stmt->rowCount() > 0){
            // already exist
            throw new \Exception('bd_addr is already exist. ', 202);
        }
        else {
            $sql = 'INSERT INTO registration (user_id, bd_addr, name)
                    VALUE (:user_id, CONV(:bd_addr, 16, 10), :name)';
            $stmt = $this->getReadConnection()->prepare($sql);
            $stmt->bindParam('user_id', $user_id, FILTER_SANITIZE_NUMBER_INT);
            $stmt->bindParam('bd_addr', $bd_addr, FILTER_SANITIZE_STRING);
            $stmt->bindParam('name', $name, FILTER_SANITIZE_STRING);
            $status = $stmt->execute();
            if ($status){
                return true;
            }
            else {
                throw new \Exception('submit form is invalid. ', 203);
            }
        }
    }

    public function deleteRegistration($user_id, $req){
        $bd_addr = str_replace(':', '', $req->{'bd_addr'});

        if(strlen($bd_addr) != 12){
            throw new \Exception('bd_addr is invalid. ', 201);
        }

        $sql = 'SELECT reg_id
                FROM registration
                WHERE user_id = ? AND LPAD(HEX(bd_addr), 12, "0") = ?';
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array($user_id, $bd_addr));

        if($stmt->rowCount() == 0){
            // already exist
            throw new \Exception('The sensor is not exist. ', 204);
        }
        else {
            $reg_id = $stmt->fetch()['reg_id'];
            $sql = 'DELETE FROM registration
                    WHERE reg_id = :reg_id';
            $stmt = $this->getReadConnection()->prepare($sql);
            $stmt->bindParam('reg_id', $reg_id, FILTER_SANITIZE_NUMBER_INT);
            $stmt->execute();
            if ($stmt->rowCount() > 0){
                return true;
            }
            else {
                throw new \Exception('Cannot find valid reg_id ' . strval($reg_id), 205);
            }
        }
    }

    public function updateRegistrationName($reg_id, $name){
        $sql = 'UPDATE registration
                SET name = ?
                WHERE reg_id = ?';

        $stmt = $this->getReadConnection()->prepare($sql);
        return $stmt->execute(array($name, $reg_id));
    }


}