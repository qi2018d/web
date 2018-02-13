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
use Slimvc\Core\Controller;

class DataController extends Controller
{
    public function actionPostDataSendAir(){
        $this->getApp()->contentType('application/json');
        $req = json_decode($this->getApp()->request->getBody());

        $air_data = new AirDataModel();
        try {
            $air_data->saveAirData($req);
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
    public function actionPostDataSendHeart(){
        $this->getApp()->contentType('application/json');
        $req = json_decode($this->getApp()->request->getBody());

        $heart_data = new HeartDataModel();
        try {
            $heart_data->saveHeartData($req);
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
    public function actionPostReadMaps(){
        $request = json_decode($this->getApp()->request()->getBody());
        $data = new AirDataModel();
        $response = array(
            'status' => true,
            'code' => 1000,
            'message' => $data->getBoundaryAirData($request)
        );
        echo json_encode($response, JSON_NUMERIC_CHECK);
    }

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
                        'o3'=>$record['o3'], 'pm2_5'=>$record['pm2_5'], 'temp'=>$record['temperature']),
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
    public function actionPostReadAirCharts(){
        $user_id = $_SESSION['user_id'];
        $data = new AirDataModel();
        $response = array(
            'status' => true,
            'code' => 1000,
            'message' => $data->getUserAirData($user_id)
        );
        echo json_encode($response, JSON_NUMERIC_CHECK);
    }

    public function actionPostReadHeartCharts(){
        $user_id = $_SESSION['user_id'];
        $data = new HeartDataModel();
        $response = array(
            'status' => true,
            'code' => 1000,
            // ERROR to display on google charts
            'message' => $data->getUserHeartData($user_id)
        );
        echo json_encode($response, JSON_NUMERIC_CHECK);
    }





    public function actionAppPostReadAirCharts($user_id){
//        $user_id = $_SESSION['user_id'];
        $data = new AirDataModel();
        $response = array(
            'status' => true,
            'code' => 1000,
            'message' => $data->getUserAirData($user_id)
        );
        echo json_encode($response, JSON_NUMERIC_CHECK);
    }

    public function actionAppPostReadHeartCharts($user_id){
//        $user_id = $_SESSION['user_id'];
        $data = new HeartDataModel();
        $response = array(
            'status' => true,
            'code' => 1000,
            // ERROR to display on google charts
            'message' => $data->getUserHeartData($user_id)
        );
        echo json_encode($response, JSON_NUMERIC_CHECK);
    }

}