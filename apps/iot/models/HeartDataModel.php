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
    public function saveHeartData($registerId, $req){
        /*include '../../public/iot/functions.php';
        // assign request data to variables.
        $user_id = $req->{'user-id'};
        $bd_addr = mac_address_str2hex($req->{'bd-addr'});

        // get registration record with user_id, mac_addr
        $sql = 'SELECT reg_id 
                FROM registration
                WHERE user_id = ? AND HEX(bd_addr) = ?';
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array($user_id, $bd_addr));
        if($stmt->rowCount() > 0){
            $reg_record = $stmt->fetch();
        }
        else {
            new \Exception("There isn't any registered sensor. ", 206);
        }*/

        // inserted new heart data record.
        $sql = 'INSERT INTO heart_data (reg_id, timestamp, heartbeat, lat, lng, rr_interval)
                VALUES (:reg_id, :timestamp, :heartbeat, :lat, :lng, :rr_interval)';
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->bindParam('reg_id', $registerId,FILTER_SANITIZE_NUMBER_INT);
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

    public function getRegisterId($userId, $bd_addr){
        //$hex_addr = mac_address_str2hex($bd_addr);

        // get registration record with user_id, mac_addr
        $sql = 'SELECT reg_id 
                FROM registration
                WHERE user_id = ? AND HEX(bd_addr) = ?';
        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array($userId, $bd_addr));
        if($stmt->rowCount() > 0){
            return $stmt->fetch()['reg_id'];
        }
        else {
            new \Exception("There isn't any registered sensor. ", 206);
        }
    }
    // fetch a recent-most heart data
    public function getRecentHeartData($req){
        $int_timestamp = $_SERVER['REQUEST_TIME'] - 10;
        $datetime_timestamp = date('Y-m-d H:i:s', $int_timestamp);
        $sql = 'SELECT *
                FROM heart_data_registration
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
                FROM heart_data_registration
                WHERE user_id = ?
                ORDER BY timestamp ASC';

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

    public function getUserHeartDataInRange($user_id, $from, $to){
        $sql = 'SELECT heartbeat, timestamp
                FROM heart_data_registration
                WHERE user_id = ? AND timestamp between ? and ?
                ORDER BY timestamp ASC'; // AND timestamp > ?;

        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);

        $stmt->execute(array($user_id, $from, $to));//, $realtime_datetime));

        return $stmt->fetchAll();
    }

    public function getUserSimplifiedHeartDataInRange($user_id, $from, $to, $difference){
        $sql = "SELECT user_id, reg_id, from_unixtime(floor((unix_timestamp(timestamp) / ( ? ))) * ?) time_range, 
                  floor(avg(heartbeat)) heartbeat, round(avg(rr_interval), 2) rr_interval
                FROM heart_data_registration
                WHERE user_id = ? AND timestamp BETWEEN ? AND ?
                GROUP BY time_range
                ORDER BY time_range ASC"; // AND timestamp > ?;

        if ($difference->h <= 1){
            // 10 seconds for 1 hour
            $interval = 20;
        }
        else if ($difference->h <= 24){
            // 2 minutes for 1 day
            $interval = 2 * 60;
        }
        else if ($difference->h <= 168){
            // 10 minutes for 1 week
            $interval = 10 * 60;
        }
        else if ($difference->h <= 672){
            // 20 minutes for 4 weeks
            $interval = 20 * 60;
        }
        else {
            // 30 minutes for more than 4 weeks
            $interval = 30 * 60;
        }

        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);

        $stmt->execute(array($interval, $interval, $user_id, $from->format('Y-m-d'), $to->format('Y-m-d')));//, $realtime_datetime));

        return $stmt->fetchAll();
    }

}