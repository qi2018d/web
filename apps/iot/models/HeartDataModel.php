<?php
/**
 * Created by PhpStorm.
 * User: leo
 * Date: 2018-01-29
 * Time: 오전 10:29
 */

namespace Iot\Model;


use Slimvc\Core\Model;

class HeartDataModel extends Model
{
    // fetch a recent-most heart data
    public function getRecentHeartData($req){
        $int_timestamp = $_SERVER['REQUEST_TIME'] - 10;
        $datetime_timestamp = date('Y-m-d H:i:s', $int_timestamp);
        $sql = 'SELECT *
                FROM heart_data JOIN registration 
                WHERE user_id = ? AND timestamp = ?';
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        // fetch records that saves 10 seconds before.
        $isSuccess = $stmt->execute(array($req->{'user-id'}, $datetime_timestamp));
        if ($isSuccess){
            return $stmt->fetch();
        }
        else {
            throw new \Exception("submit form is invalid", 203);
        }
    }

    public function getUserHeartData($user_id){

        $sql = 'SELECT heartbeat, timestamp
                FROM heart_data JOIN registration 
                WHERE user_id = ?
                ORDER BY timestamp DESC';

        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        // fetch records that saves 10 seconds before.
        $isSuccess = $stmt->execute(array($user_id));
        if ($isSuccess){
            return $stmt->fetchAll();
        }
        else {
            throw new \Exception("submit form is invalid", 203);
        }
    }

    public function saveHeartData($req){
        include '../../public/iot/functions.php';
        // assign request data to variables.
        $user_id = $req->{'user-id'};
        $mac_addr = mac_address_str2hex($req->{'bd-addr'});

        // get registration record with user_id, mac_addr
        $sql = 'SELECT reg_id 
                FROM registration
                WHERE user_id = ? AND HEX(mac_addr) = ?';
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array($user_id, $mac_addr));
        if($stmt->rowCount() > 0){
            $reg_record = $stmt->fetch();
        }
        else {
            new \Exception("There isn't any registered sensor. ", 206);
        }

        // inserted new heart data record.
        $sql = 'INSERT INTO heart_data (reg_id, timestamp, heartbeat, lat, lng, rr_interval)
                VALUES (:reg_id, :timestamp, :heartbeat, :lat, :lng, :rr_interval)';
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->bindParam('reg_id', $reg_record['reg_id'],FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam('timestamp', $req->{'timestamp'},FILTER_SANITIZE_STRING);
        $stmt->bindParam('heartbeat', $req->{'data'}->{'heart-rate'}, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam('rr_interval', $req->{'data'}->{'rr-interval'}, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam('lat', $req->{'location'}->{'lat'}, FILTER_SANITIZE_NUMBER_FLOAT);
        $stmt->bindParam('lng', $req->{'location'}->{'lng'}, FILTER_SANITIZE_NUMBER_FLOAT);
        $stmt->execute();

        if($stmt->rowCount() > 0){
            // successfully record inserted.
            return true;
        }
        else {
            // nothing inserted.
            return false;
        }

    }
}