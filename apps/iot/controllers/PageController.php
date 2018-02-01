<?php
namespace Iot\Controller;


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

        if($this->isOnVerification()){
            $data = array(
                "title" => $this->title,
                "team_name" => $this->team_name);
            $this->render("validation.phtml", $data);
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
    public function actionUserCancelID(){

        if($this->isSignedIn()) {
            $this->getApp()->contentType('text/html');
            $data = array(
                "title" => $this->title,
                "team_name" => $this->team_name);
            $this->render("cancelid.phtml", $data);
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
            $data = array(
                "title" => $this->title,
                "team_name" => $this->team_name);
            $this->render("sensor.phtml", $data);
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


    public function isOnVerification(){
        return isset($_SESSION['ver_id']);
    }
    public function isSignedIn(){
        return isset($_SESSION['user_id']);
    }
}
