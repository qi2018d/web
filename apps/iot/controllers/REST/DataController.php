<?php
/**
 * Created by PhpStorm.
 * User: leo
 * Date: 2018-01-28
 * Time: 오후 5:21
 */

namespace Iot\Controller\REST;


use Iot\Model\AirDataModel;
use Iot\Model\HeartDataModel;
use Iot\Model\RegistrationModel;
use Slimvc\Core\Controller;

class DataController extends Controller
{
    // /api/data/send/air
    //  Send a new air quality sensor data.
    public function actionPostDataSendAir(){
        $this->getApp()->contentType('application/json');
        $req = json_decode($this->getApp()->request->getBody());

        $userId = $req->{'user-id'};
        $bd_addr = mac_address_str2hex($req->{'bd-addr'});

        $air_data = new AirDataModel();
        $registerId = $air_data->getRegisterId($userId , $bd_addr);

        if (!isset($registerId)){
            $registration = new RegistrationModel();
            $registration->saveAutoRegistration($userId, $bd_addr);
            $registerId = $air_data->getRegisterId($userId , $bd_addr);
        }

        try {
            $air_data->saveAirData($registerId, $req);
            $res = array(
                'status' => true,
                'code' => 1000
            );
        }
        catch (\Exception $e){
            $res = array(
                'status' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            );
        }
        echo json_encode($res);
    }

    // /api/data/send/air/historical
    //  Send a new air quality sensor data.
    public function actionPostDataSendAirHistorical(){
        $this->getApp()->contentType('application/json');
        $req = json_decode($this->getApp()->request->getBody());

        $userId = $req->{'user-id'};
        $bd_addr = mac_address_str2hex($req->{'bd-addr'});
        $air_data = new AirDataModel();

        $registerId = $air_data->getRegisterId($userId, $bd_addr);
        try {
            $air_data->saveAirDataArray($registerId,$req);
            $res = array(
                'status' => true,
                'code' => 1000
            );
        }
        catch (\Exception $e){
            $res = array(
                'status' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            );
        }
        echo json_encode($res);
    }

    // /api/data/send/aqi
    //  Send a new AQI sensor data.
    public function actionPostDataSendAQI(){
        $this->getApp()->contentType('application/json');
        $req = json_decode($this->getApp()->request->getBody());

        $userId = $req->{'user-id'};
        $bd_addr = mac_address_str2hex($req->{'bd-addr'});

        $air_data = new AirDataModel();
        $registerId = $air_data->getRegisterId($userId, $bd_addr);

        if(!isset($registerId)){
            $registration = new RegistrationModel();
            $registration->saveAutoRegistration($userId, $bd_addr);
            $registerId = $air_data->getRegisterId($userId, $bd_addr);

        }
        try {
            $air_data->saveAQIData($registerId, $req);
            $res = array(
                'status' => true,
                'code' => 1000
            );
        }
        catch (\Exception $e){
            $res = array(
                'status' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            );
        }
        echo json_encode($res);
    }

    // /api/data/send/heart
    //  Send a new heart sensor data.
    public function actionPostDataSendHeart(){
        $this->getApp()->contentType('application/json');
        $req = json_decode($this->getApp()->request->getBody());

        $userId = $req->{'user-id'};
        $bd_addr = mac_address_str2hex($req->{'bd-addr'});

        $heart_data = new HeartDataModel();
        $registerId = $heart_data->getRegisterId($userId, $bd_addr);
        if(!isset($registerId)){
            $registration = new RegistrationModel();
            $registration->saveAutoRegistration($userId, $bd_addr);
            $registerId = $heart_data->getRegisterId($userId, $bd_addr);
        }
        try {
            $heart_data->saveHeartData($registerId, $req);
            $res = array(
                'status' => true,
                'code' => 1000
            );
        }
        catch (\Exception $e){
            $res = array(
                'status' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            );
        }
        echo json_encode($res);
    }



    // /api/data/read/maps
    //  POST air quality JSON data for google maps.
    public function actionPostReadMaps(){
        date_default_timezone_set('America/Los_Angeles');
        $request = json_decode($this->getApp()->request()->getBody());

        $from = time() - 3600;
        $to = time();

        $data = new AirDataModel();
        $response = array(
            'status' => true,
            'code' => 1000,
            'message' => $data->getBoundaryAirDataInRange($request, $from, $to)
        );
        echo json_encode($response, JSON_NUMERIC_CHECK);
    }

    // /api/data/read/maps/now
    //  POST air quality JSON data for google maps in 1 hour.
    public function actionPostReadMapsNow(){
        $request = json_decode($this->getApp()->request()->getBody());
            date_default_timezone_set('America/Los_Angeles');

            $from = time() - 3600;
            $to = time();

        $data = new AirDataModel();
        $records = $data->getBoundaryAirDataInRange($request, $from, $to);

        $geojson = array(
            'type'=>'FeatureCollection',
            'features'=> array()
        );

        foreach($records as $record){
            array_push($geojson['features'],
                array('type'=> 'Feature',
                    'properties'=> array('co'=>$record['co'], 'no2'=>$record['no2'], 'so2'=>$record['so2'],
                        'o3'=>$record['o3'], 'pm2_5'=>$record['pm2_5'], 'timestamp'=>$record['timestamp']),
                    'geometry'=> array(
                        'type'=> 'Point',
                        'coordinates'=> array($record['lng'], $record['lat'])
                    )
                )
            );
        }

        $response = array(
            'status' => true,
            'code' => 1000,
            'message' => $geojson
        );
        echo json_encode($response, JSON_NUMERIC_CHECK);
    }

    // /api/data/read/maps/geojson
    // POST return air quality GeoJSON data for google maps.
    public function actionPostReadMapsGeojson(){
        $request = json_decode($this->getApp()->request()->getBody());
        $data = new AirDataModel();
        $records = $data->getBoundaryAirData($request);

        $geojson = array(
            'type'=>'FeatureCollection',
            'features'=> array()
        );

        foreach($records as $record){
            array_push($geojson['features'],
                array('type'=> 'Feature',
                    'properties'=> array('co'=>$record['co'], 'no2'=>$record['no2'], 'so2'=>$record['so2'],
                        'o3'=>$record['o3'], 'pm2_5'=>$record['pm2_5'], 'timestamp'=>$record['timestamp']),
                    'geometry'=> array(
                        'type'=> 'Point',
                        'coordinates'=> array($record['lng'], $record['lat'])
                    )
                )
            );
        }

        $response = array(
            'status' => true,
            'code' => 1000,
            'message' => $geojson
        );
        echo json_encode($response, JSON_NUMERIC_CHECK);
    }



    // /api/data/read/charts/air
    // Post return air quality JSON data for google maps.
    public function actionGetReadAirCharts(){
        $user_id = $_SESSION['user_id'];
        $data = new AirDataModel();
        $response = array(
            'status' => true,
            'code' => 1000,
            'message' => $data->getUserAirData($user_id)
        );
        echo json_encode($response, JSON_NUMERIC_CHECK);
    }

    // /api/data/read/charts/air/range
    public function actionPostReadAirChartsInRange(){
        $request = json_decode($this->getApp()->request()->getBody());

        if (isset($_SESSION['user_id'])){
            $userId = $_SESSION['user_id'];
        } else {
            $userId = $request->{'user-id'};
        }

        //$from = date('Y-m-d H:i:s', $request->{'from'});
        //$to = date('Y-m-d H:i:s', $request->{'to'});
        $from = $request->{'from'};
        $to = $request->{'to'};

        $data = new AirDataModel();
        $response = array(
            'status' => true,
            'code' => 1000,
            'message' => $data->getUserAirDataInRange($userId, $from, $to)
        );
        echo json_encode($response, JSON_NUMERIC_CHECK);
    }

    // /api/data/read/charts/aqi/range
    public function actionPostReadAQIChartsInRange(){
        $request = json_decode($this->getApp()->request()->getBody());

        if (isset($_SESSION['user_id'])){
            $userId = $_SESSION['user_id'];
        } else {
            $userId = $request->{'user-id'};
        }

        //$from = date('Y-m-d H:i:s', $request->{'from'});
        //$to = date('Y-m-d H:i:s', $request->{'to'});
        $from = date_create($request->{'from'});
        $to = date_create($request->{'to'});

        $data = new AirDataModel();
        $response = array(
            'status' => true,
            'code' => 1000,
            'message' => $data->getUserAQIDataInRange($userId, $from, $to)
        );
        echo json_encode($response, JSON_NUMERIC_CHECK);
    }



    // /api/data/read/charts/heart
    // Post return heart-related JSON data
    public function actionPostReadHeartCharts(){
        $request = json_decode($this->getApp()->request()->getBody());

        if (isset($_SESSION['user_id'])){
            $userId = $_SESSION['user_id'];
        } else {
            $userId = $request->{'user-id'};
        }

        $data = new HeartDataModel();
        $response = array(
            'status' => true,
            'code' => 1000,
            // ERROR to display on google charts
            'message' => $data->getUserHeartData($userId)
        );
        echo json_encode($response, JSON_NUMERIC_CHECK);
    }

    // /api/data/read/charts/heart/range
    public function actionPostReadHeartChartsInRange(){
        $request = json_decode($this->getApp()->request()->getBody());

        if (isset($_SESSION['user_id'])){
            $userId = $_SESSION['user_id'];
        } else {
            $userId = $request->{'user-id'};
        }

        //$from = date('Y-m-d H:i:s', $request->{'from'});
        //$to = date('Y-m-d H:i:s', $request->{'to'});
        $from = $request->{'from'};
        $to = $request->{'to'};

        $data = new HeartDataModel();
        $response = array(
            'status' => true,
            'code' => 1000,
            'message' => $data->getUserHeartDataInRange($userId, $from, $to)
        );
        echo json_encode($response, JSON_NUMERIC_CHECK);
    }



    // /api/data/send/air/csv
    public function actionAppPostCSVFile(){

        $jsonRequest = json_decode(file_get_contents($_FILES['json']['tmp_name']));

        $userId = $jsonRequest->{'user-id'};

        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/../../uploads/';
        $uploadFile = $uploadDir . $userId . '_' . $_SERVER['REQUEST_TIME']  . '_' . basename($_FILES['csv']['name']) ;

        echo '<pre>';
        if (move_uploaded_file($_FILES['csv']['tmp_name'], $uploadFile)) {

            echo "File is valid, and was successfully uploaded.\n";
        } else {

            echo "Possible file upload attack!\n";
        }

        echo 'Here is some more debugging info:';
        print_r($_FILES);

        print "</pre>";
    }

    // /api/app/data/read/charts/air/range
    public function actionAppPostReadAQIChartsInRange(){
        $request = json_decode($this->getApp()->request()->getBody());

        if (isset($_SESSION['user_id'])){
            $userId = $_SESSION['user_id'];
        } else {
            $userId = $request->{'user-id'};
        }

        //$from = date('Y-m-d H:i:s', $request->{'from'});
        //$to = date('Y-m-d H:i:s', $request->{'to'});
        $from = date_create($request->{'from'});
        $to = date_create($request->{'to'});

        $difference = date_diff($from, $to);

        $data = new AirDataModel();
        $response = array(
            'status' => true,
            'code' => 1000,
            'message' => $data->getUserSimplifedAQIDataInRange($userId, $from, $to, $difference)
        );
        echo json_encode($response, JSON_NUMERIC_CHECK);
    }

    // /api/data/read/charts/heart/range
    public function actionAppPostReadHeartChartsInRange(){
        $request = json_decode($this->getApp()->request()->getBody());

        if (isset($_SESSION['user_id'])){
            $userId = $_SESSION['user_id'];
        } else {
            $userId = $request->{'user-id'};
        }

        //$from = date('Y-m-d H:i:s', $request->{'from'});
        //$to = date('Y-m-d H:i:s', $request->{'to'});
        $from = date_create($request->{'from'});
        $to = date_create($request->{'to'});

        $difference = date_diff($from, $to);

        $data = new HeartDataModel();
        $response = array(
            'status' => true,
            'code' => 1000,
            'message' => $data->getUserSimplifiedHeartDataInRange($userId, $from, $to, $difference)
        );
        echo json_encode($response, JSON_NUMERIC_CHECK);
    }

}
function mac_address_formatter($str_addr){
    if(strlen($str_addr) == 12){
        $str_addr = substr(chunk_split($str_addr, 2, ":"),0,17);
        return $str_addr;
    }
}
function mac_address_str2hex($str_addr){
    return ltrim(str_replace(':', '', $str_addr), '0');
}
