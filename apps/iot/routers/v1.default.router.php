<?php
// default index action, GET /
$app->get('/', 'Iot\Controller\IndexController:actionIndex')
    ->name('get-homepage');

$app->group('/v1', function () use ($app) {
    // get programmers list, GET /v1/programmers
    $app->get('/programmers', 'Iot\Controller\ProgrammerController:actionGetProgrammers')
        ->name('get-programmers-list');

    // get programmer detail, GET /v1/programmers/:id
    $app->get('/programmers/:id', 'Iot\Controller\ProgrammerController:actionGetProgrammer')
        ->conditions(array('id' => '\d+'))
        ->name('get-programmer-detail');
});

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