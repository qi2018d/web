<?php


/* routes for pages */

$app->get('/', 'Iot\Controller\PageController:actionHome')
    ->name('get-home-page');

$app->get('/map', 'Iot\Controller\PageController:actionMap')
    ->name('get-map-page');

$app->get('/signin', 'Iot\Controller\PageController:actionSignin')
    ->name('get-signin-page');

$app->get('/developers', 'Iot\Controller\PageController:actionDevelopers')
    ->name('get-developers-page');


/* routes for api */
$app->group('/api', function () use ($app) {

    // USER API
    // Register a user account. POST /api/users/add
    $app->post('/user/signup', 'Iot\Controller\REST\UserController:actionPostUserSignup')
        ->name('post-signup-users');

    // Check user’s account is valid POST /api/users/signin
    $app->post('/user/signin', 'Iot\Controller\REST\UserController:actionPostUserSignin')
        ->name('post-user-signin');

    // Check an email to know existence user account. POST /api/users/exist
    $app->post('/user/exist', 'Iot\Controller\REST\UserController:actionPostUserExist')
        ->name('post-user-is-exist');

    // Change user’s password. POST /users/change/password
    $app->post('/user/change/password', 'Iot\Controller\REST\UserController:actionPostUserChangePassword')
        ->name('post-user-change-password');


    // SENSOR API
    // Show a list of user’s sensors.
    $app->map('/user/:user_id/sensor', 'Iot\Controller\REST\UserController:actionPostUserSensor')
        ->via('GET', 'POST')
        ->conditions(array('user_id' => '\d+'))
        ->name('custom-show-users-sensors');

    // Register a sensor.
    $app->map('/user/{:user_id}/register/sensor', 'Iot\Controller\REST\UserController:actionPostUserRegisterSensor')
        ->via('GET', 'POST')
        ->name('post-add-sensor');

    // Deregister a sensor.
    $app->map('/user/{:user_id}/deregister/sensor', 'Iot\Controller\REST\UserController:actionPostUserDeregisterSensor')
        ->via('GET', 'POST')
        ->name('post-delete-sensor');

    //  Send a new air quality sensor data.
    $app->post('/data/send/air', 'Iot\Controller\REST\UserController:actionPostDataSendAir')
        ->name('post-send-sensor-data');

    //  Send a new heart sensor data.
    $app->post('/data/send/heart', 'Iot\Controller\Data\REST\UserController:actionPostDataSendHeart')
        ->name('post-delete-heart-data');

    //  Show air quality data for google maps.
    $app->post('/sensors/read/maps', 'Iot\Controller\REST\UserController:actionPostReadMaps')
        ->name('post-delete-heart-data');




});