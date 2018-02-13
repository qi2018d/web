<?php
namespace Iot\Controller;


use Iot\Model\RegistrationModel;
use Slimvc\Core\Controller;

class PageController extends Controller
{
    /**
     * Default page action
     */

    var $title = 'QI 2018 Winter Team D';
    var $team_name = 'Team D';


    public function actionHome()
    {
        $this->getApp()->contentType('text/html');
        $data = array(
            "title" => $this->title,
            "team_name" => $this->team_name);
        $this->render("home.phtml", $data);
    }

    public function actionSignup()
    {

        if(!$this->isSignedIn()) {
            $this->getApp()->contentType('text/html');
            $data = array(
                "title" => $this->title,
                "team_name" => $this->team_name);
            $this->render("signup.phtml", $data);
        }
        else
            $this->getApp()->redirect('/');
    }
    public function actionSignupValidation()
    {
        $this->getApp()->contentType('text/html');

        if($this->isOnSignupVerification()){
            $data = array(
                "title" => $this->title,
                "team_name" => $this->team_name);
            $this->render("signup_validation.phtml", $data);
        }
        else
            $this->getApp()->redirect('/');
    }

    public function actionSignin()
    {
        if(!$this->isSignedIn()){

            $this->getApp()->contentType('text/html');
            $data = array(
                "title" => $this->title,
                "team_name" => $this->team_name);

            $this->render("signin.phtml", $data);
        }
        else
            $this->getApp()->redirect('/');
    }

    public function actionForgotpw(){
        if(!$this->isSignedIn()){

            $this->getApp()->contentType('text/html');
            $data = array(
                "title" => $this->title,
                "team_name" => $this->team_name);

            $this->render("forgotpw.phtml", $data);
        }
        else
            $this->getApp()->redirect('/');
    }
    public function actionForgotpwValidation()
    {
        $this->getApp()->contentType('text/html');

        if($this->isOnForgotpwVerification()){
            $data = array(
                "title" => $this->title,
                "team_name" => $this->team_name);
            $this->render("forgotpw_validation.phtml", $data);
        }
        else
            $this->getApp()->redirect('/');
    }
    public function actionForgotpwChange(){
        $this->getApp()->contentType('text/html');

        /*$data = array(
            "title" => $this->title,
            "team_name" => $this->team_name);
        $this->render("forgotpw_change.phtml", $data);
        */

        if($this->isOnForgotpwVerification()){
            $data = array(
                "title" => $this->title,
                "team_name" => $this->team_name);
            $this->render("forgotpw_change.phtml", $data);
        }
        else
            $this->getApp()->redirect('/');

    }


    public function actionUser(){

        if($this->isSignedIn()) {

            $this->getApp()->contentType('text/html');
            $data = array(
                "title" => $this->title,
                "team_name" => $this->team_name);

            $this->render("user.phtml", $data);
        }
        else
            $this->getApp()->redirect('/signin');
    }
    public function actionUserChangePassword(){

        if($this->isSignedIn()) {

            $this->getApp()->contentType('text/html');
            $data = array(
                "title" => $this->title,
                "team_name" => $this->team_name);

            $this->render("changepw.phtml", $data);
        }
        else
            $this->getApp()->redirect('/signin');
    }

    public function actionMap()
    {
        $this->getApp()->contentType('text/html');
        $data = array(
            "title" => $this->title,
            "team_name" => $this->team_name);

        $this->render("map.phtml", $data);
    }
    public function actionSensor(){

        if($this->isSignedIn()) {
            $this->getApp()->contentType('text/html');

            $user_id = $_SESSION['user_id'];

            $registrationModel = new RegistrationModel();
            $records = $registrationModel->getRegistration($user_id);

            $data = array(
                "title" => $this->title,
                "team_name" => $this->team_name,
                "registration" => $records);

            $this->render("sensor.phtml", $data);
        }
        else
            $this->getApp()->redirect('/signin');
    }

    public function actionCharts(){
        if($this->isSignedIn()) {
            $data = array(
                "title" => $this->title,
                "team_name" => $this->team_name);

            $this->getApp()->render("charts.phtml", $data);
        }
        else
            $this->getApp()->redirect('/signin');


    }
    public function actionDevelopers()
    {
        $this->getApp()->contentType('text/html');
        $data = array(
            "title" => $this->title,
            "team_name" => $this->team_name);

        $this->render("developers.phtml", $data);
    }


    public function isOnSignupVerification(){
        return isset($_SESSION['signup_ver_id']);
    }
    public function isOnForgotpwVerification(){
        return isset($_SESSION['forgotpw_ver_id']);
    }
    public function isSignedIn(){
        return isset($_SESSION['user_id']);
    }
}
