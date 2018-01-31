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

    public function actionSignin()
    {
        $this->getApp()->contentType('text/html');
        $data = array(
            "title" => $this->title,
            "team_name" => $this->team_name);
        $this->render("signin.phtml", $data);

    }
    public function actionSignupValidation()
    {
        $this->getApp()->contentType('text/html');
        /*
        if(isset($_SESSION["ver_id"])){
            $data = array(
                "title" => $this->title,
                "team_name" => $this->team_name);
            $this->render("validation.phtml", $data);
        }
        else
        {
            $this->getApp()->status(404);
        }
        */
        $data = array(
            "title" => $this->title,
            "team_name" => $this->team_name);
        $this->render("validation.phtml", $data);
    }
    public function actionSignup()
    {
        $this->getApp()->contentType('text/html');
        $data = array(
            "title" => $this->title,
            "team_name" => $this->team_name);
        $this->render("signup.phtml", $data);
    }

    public function actionMap()
    {
        $this->getApp()->contentType('text/html');
        $data = array(
            "title" => $this->title,
            "team_name" => $this->team_name);
        $this->render("map.phtml", $data);
    }
    public function actionDevelopers()
    {
        $this->getApp()->contentType('text/html');
        $data = array(
            "title" => $this->title,
            "team_name" => $this->team_name);
        $this->render("developers.phtml", $data);
    }

}
