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
    public function saveAirData($registerId, $req){
        // 0130 PROBLEM NOW WE AIR DATA REG_ID NOT FETCHEDl
        // assign request data to variables.
        // get registration record with user_id, mac_addr
        // insert new air quality data.
        $sql = 'INSERT INTO airq_data (reg_id, timestamp, co, so2, no2, o3, pm2_5, temperature, lat, lng)
                VALUES (:reg_id, :timestamp, :co, :so2, :no2, :o3, :pm2_5, :temperature, :lat, :lng)';

        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->bindParam('reg_id', $registerId,FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam('timestamp', $req->{'timestamp'},FILTER_SANITIZE_STRING);
        $stmt->bindParam('co', $req->{'data'}->{'co'}, FILTER_SANITIZE_NUMBER_FLOAT);
        $stmt->bindParam('so2', $req->{'data'}->{'so2'}, FILTER_SANITIZE_NUMBER_FLOAT);
        $stmt->bindParam('no2', $req->{'data'}->{'no2'}, FILTER_SANITIZE_NUMBER_FLOAT);
        $stmt->bindParam('o3', $req->{'data'}->{'o3'}, FILTER_SANITIZE_NUMBER_FLOAT);
        $stmt->bindParam('pm2_5', $req->{'data'}->{'pm2_5'}, FILTER_SANITIZE_NUMBER_FLOAT);
        $stmt->bindParam('temperature', $req->{'data'}->{'temp'}, FILTER_SANITIZE_NUMBER_FLOAT);
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

    public function saveAirDataArray($registerId, $req){
        // 0130 PROBLEM NOW WE AIR DATA REG_ID NOT FETCHEDl
        // assign request data to variables.
        /*$user_id = $req->{'user-id'};
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

        // insert new air quality data.
        $sql = 'INSERT INTO airq_data (reg_id, timestamp, co, so2, no2, o3, pm2_5, temperature, lat, lng)
                VALUES (:reg_id, :timestamp, :co, :so2, :no2, :o3, :pm2_5, :temperature, :lat, :lng)';

        foreach($req->{'message'} as $record){

            $stmt = $this->getReadConnection()->prepare($sql);
            $stmt->bindParam('reg_id', $registerId,FILTER_SANITIZE_NUMBER_INT);
            $stmt->bindParam('timestamp', $record->{'timestamp'},FILTER_SANITIZE_STRING);
            $stmt->bindParam('co', $record->{'data'}->{'co'}, FILTER_SANITIZE_NUMBER_FLOAT);
            $stmt->bindParam('so2', $record->{'data'}->{'so2'}, FILTER_SANITIZE_NUMBER_FLOAT);
            $stmt->bindParam('no2', $record->{'data'}->{'no2'}, FILTER_SANITIZE_NUMBER_FLOAT);
            $stmt->bindParam('o3', $record->{'data'}->{'o3'}, FILTER_SANITIZE_NUMBER_FLOAT);
            $stmt->bindParam('pm2_5', $record->{'data'}->{'pm2_5'}, FILTER_SANITIZE_NUMBER_FLOAT);
            $stmt->bindParam('temperature', $record->{'data'}->{'temp'}, FILTER_SANITIZE_NUMBER_FLOAT);
            $stmt->bindParam('lat', $record->{'location'}->{'lat'}, FILTER_SANITIZE_NUMBER_FLOAT);
            $stmt->bindParam('lng', $record->{'location'}->{'lng'}, FILTER_SANITIZE_NUMBER_FLOAT);
            $stmt->execute();
        }

        if($stmt->rowCount() > 0){
            // successfully record inserted.
            return true;
        }
        else {
            // nothing inserted.
            return false;
        }

    }

    public function saveAQIData($registerId, $req){
        // 0130 PROBLEM NOW WE AIR DATA REG_ID NOT FETCHEDl
        // assign request data to variables.
        /*$user_id = $req->{'user-id'};
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

        // insert new air quality data.
        $sql = 'INSERT INTO aqi_data (reg_id, timestamp, co, so2, no2, o3, pm2_5, lat, lng)
                VALUES (:reg_id, :timestamp, :co, :so2, :no2, :o3, :pm2_5, :lat, :lng)';

        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->bindParam('reg_id', $registerId,FILTER_SANITIZE_NUMBER_INT);
        $stmt->bindParam('timestamp', $req->{'timestamp'},FILTER_SANITIZE_STRING);
        $stmt->bindParam('co', $req->{'data'}->{'co'}, FILTER_SANITIZE_NUMBER_FLOAT);
        $stmt->bindParam('so2', $req->{'data'}->{'so2'}, FILTER_SANITIZE_NUMBER_FLOAT);
        $stmt->bindParam('no2', $req->{'data'}->{'no2'}, FILTER_SANITIZE_NUMBER_FLOAT);
        $stmt->bindParam('o3', $req->{'data'}->{'o3'}, FILTER_SANITIZE_NUMBER_FLOAT);
        $stmt->bindParam('pm2_5', $req->{'data'}->{'pm2_5'}, FILTER_SANITIZE_NUMBER_FLOAT);
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
        $realtime_timestamp = $_SERVER['REQUEST_TIME'];
        $realtime_datetime = date('Y-m-d H:i:s', $realtime_timestamp);

        $sql = "SELECT co, so2, no2, o3, pm2_5, lat, lng, timestamp
                FROM aqi_data
                WHERE lat > ? AND lng > ? AND lat < ? AND lng < ?";

        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array($boundary->{'south'}, $boundary->{'west'}, $boundary->{'north'}, $boundary->{'east'}));
        return $stmt->fetchAll();
    }

    public function getBoundaryAirDataInRange($boundary, $from, $to){
        $from_date = date('Y-m-d H:i:s', $from);
        $to_date = date('Y-m-d H:i:s', $to);

        $sql = "SELECT co, so2, no2, o3, pm2_5, lat, lng, timestamp
                FROM aqi_data
                WHERE lat > ? AND lng > ? AND lat < ? AND lng < ? AND timestamp BETWEEN ? AND ?";

        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array($boundary->{'south'}, $boundary->{'west'}, $boundary->{'north'}, $boundary->{'east'}, $from_date, $to_date));
        return $stmt->fetchAll();
    }

    public function getUserAirData($user_id){
        $sql = "SELECT co, so2, no2, o3, pm2_5, temperature, timestamp
                FROM airq_data_registration
                WHERE user_id = ?
                ORDER BY timestamp ASC";

        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);
        $stmt->execute(array($user_id));
        return $stmt->fetchAll();
    }

    public function getUserAirDataInRange($user_id, $from, $to){
        $sql = "SELECT co, so2, no2, o3, pm2_5, temperature, timestamp
                FROM airq_data_registration
                WHERE user_id = ? AND timestamp BETWEEN ? AND ?
                ORDER BY timestamp ASC"; // AND timestamp > ?;

        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);

        $stmt->execute(array($user_id, $from, $to));//, $realtime_datetime));

        return $stmt->fetchAll();
    }

    public function getUserAQIDataInRange($user_id, $from, $to){

        $sql = "SELECT co, so2, no2, o3, pm2_5, timestamp
                FROM aqi_data_registration
                WHERE user_id = ? AND timestamp BETWEEN ? AND ?
                ORDER BY timestamp ASC";

        $stmt = $this->getReadConnection()->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_ASSOC);

        $stmt->execute(array($user_id, $from->format('Y-m-d'), $to->format('Y-m-d')));

        return $stmt->fetchAll();
    }

    public function getUserSimplifedAQIDataInRange($user_id, $from, $to, $difference){

        $sql = "SELECT user_id, reg_id, from_unixtime(floor((unix_timestamp(timestamp) / ( ? ))) * ?) AS time_range, 
                  round(avg(co), 2) co, round(avg(so2), 2) so2, round(avg(no2), 2) no2, round(avg(o3), 2) o3, round(avg(pm2_5), 2) pm2_5
                FROM aqi_data_registration
                WHERE user_id = ? AND timestamp BETWEEN ? AND ?
                GROUP BY time_range
                ORDER BY time_range ASC";

        if ($difference->h <= 1){
            // 10 seconds for 1 hour
            $interval = 10;
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

        $stmt->execute(array($interval, $interval, $user_id, $from->format('Y-m-d'), $to->format('Y-m-d')));

        return $stmt->fetchAll();
    }
}



