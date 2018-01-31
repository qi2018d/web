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
                'code' => 100,
                'message' => 'Success'
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
                'code' => 100,
                'message' => 'Success'
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
}