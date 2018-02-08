<?php
/**
 * Created by PhpStorm.
 * User: leo
 * Date: 2018-01-30
 * Time: 오후 8:02
 */


namespace Iot\Model;
use Slimvc\Core\Model;


class AirDataModel extends Model
{
// fetch a recent-most heart data
    public function getRecentAirData($req){
        $int_timestamp = $_SERVER['REQUEST_TIME'] - 10;
        $datetime_timestamp = date('Y-m-d H:i:s', $int_timestamp);
        $sql = 'SELECT *
                FROM airq_data JOIN registration 
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

    public function saveAirData($req){
        // 0130 PROBLEM NOW WE AIR DATA REG_ID NOT FETCHEDl
        // assign request data to variables.
        include '../../public/iot/functions.php';
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

        // insert new air quality data.
        $sql = 'INSERT INTO airq_data (reg_id, timestamp, co, so2, no2, o3, pm2_5, pm10, temperature, lat, lng)
                VALUES (:reg_id, :timestamp, :co, :so2, :no2, :o3, :pm2_5, :pm10, :temperature, :lat, :lng)';

        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->bindParam('reg_id', $reg_record['reg_id'],FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam('timestamp', $req->{'timestamp'},FILTER_SANITIZE_STRING);
        $stmt->bindParam('co', $req->{'data'}->{'co'}, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam('so2', $req->{'data'}->{'so2'}, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam('no2', $req->{'data'}->{'no2'}, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam('o3', $req->{'data'}->{'o3'}, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam('pm2_5', $req->{'data'}->{'pm2_5'}, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam('pm10', $req->{'data'}->{'pm10'}, FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam('temperature', $req->{'data'}->{'temperature'}, FILTER_SANITIZE_NUMBER_FLOAT);
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

    public function getBoundaryAirData($boundary){
        $realtime_timestamp = $_SERVER['REQUEST_TIME']; // - 86400 * 2;
        $realtime_datetime = date('Y-m-d H:i:s', $realtime_timestamp);

        $sql = "SELECT co, so2, no2, o3, pm2_5, temperature, lat, lng
                FROM airq_data
                WHERE lat > ? AND lng > ? AND lat < ? AND lng < ?"; // AND timestamp > ?";

        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array($boundary->{'south'}, $boundary->{'west'}, $boundary->{'north'}, $boundary->{'east'}));//, $realtime_datetime));
        return $stmt->fetchAll();
    }

    public function getUserAirData($user_id){
        $sql = "SELECT co, so2, no2, o3, pm2_5, temperature, timestamp
                FROM airq_data_user
                WHERE user_id = ?
                ORDER BY timestamp DESC"; // AND timestamp > ?;

        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array($user_id));//, $realtime_datetime));
        return $stmt->fetchAll();
    }
}
