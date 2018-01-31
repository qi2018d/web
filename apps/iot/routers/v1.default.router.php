<?php


/* routes for pages */

$app->get('/', 'Iot\Controller\PageController:actionHome')
    ->name('get-home-page');

$app->get('/map', 'Iot\Controller\PageController:actionMap')
    ->name('get-map-page');

$app->get('/signin', 'Iot\Controller\PageController:actionSignin')
    ->name('get-signin-page');

$app->group('/signup', function() use ($app){

    $app->get('/', 'Iot\Controller\PageController:actionSignup')
        ->name('get-signup-page');

    $app->get('/validation', 'Iot\Controller\PageController:actionSignupValidation')
        ->name('get-signup-validation-page');

});


$app->get('/developers', 'Iot\Controller\PageController:actionDevelopers')
    ->name('get-developers-page');


/* routes for api */
$app->group('/api', function () use ($app) {

    // USER API
    $app->group('/user', function () use ($app){
        // Register a user account. POST /api/users/add
        $app->post('/signup', 'Iot\Controller\UserController:actionPostUserSignup')
            ->name('post-signup-users');

        // Check user’s account is valid POST /api/users/signin
        $app->post('/signin', 'Iot\Controller\UserController:actionPostUserSignin')
            ->name('post-user-signin');

        // Check an email to know existence user account. POST /api/users/exist
        $app->post('/exist', 'Iot\Controller\UserController:actionPostUserExist')
            ->name('post-user-is-exist');

        // Change user’s password. POST /users/change/password
        $app->post('/change/password', 'Iot\Controller\UserController:actionPostUserChangePassword')
            ->name('post-user-change-password');

        // Show a list of user’s sensors.
        $app->get('/:user_id/sensor', 'Iot\Controller\UserController:actionGetUserSensor')
            ->conditions(array('user_id' => '\d+'))
            ->name('get-show-users-sensors');

        // Register a sensor.
        $app->post('/:user_id/register/sensor', 'Iot\Controller\UserController:actionPostUserRegisterSensor')
            ->conditions(array('user_id' => '\d+'))
            ->name('post-add-sensor');

        // Deregister a sensor.
        $app->post('/:user_id/deregister/sensor', 'Iot\Controller\UserController:actionPostUserDeregisterSensor')
            ->conditions(array('user_id' => '\d+'))
            ->name('post-delete-sensor');
    });

    // DATA API
    $app->group('/data', function () use ($app){
        //  Send a new air quality sensor data.
        $app->post('/send/air', 'Iot\Controller\DataController:actionPostDataSendAir')
            ->name('post-send-sensor-data');

        //  Send a new heart sensor data.
        $app->post('/send/heart', 'Iot\Controller\Data\Controller:actionPostDataSendHeart')
            ->name('post-delete-heart-data');

        //  Show air quality data for google maps.
        $app->post('/read/maps', 'Iot\Controller\DataController:actionPostReadMaps')
            ->name('post-read-data-maps');
    });
});