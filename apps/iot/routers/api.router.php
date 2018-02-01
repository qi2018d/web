<?php
$app->group('/api', function () use ($app) {

    // USER API
    $app->group('/user', function () use ($app){
        // Register a user account. POST /api/users/add
        $app->post('/signup', 'Iot\Controller\REST\UserController:actionPostUserSignup')
            ->name('post-signup-users');

        // Check user’s account is valid POST /api/users/signin
        $app->post('/signin', 'Iot\Controller\REST\UserController:actionPostUserSignin')
            ->name('post-user-signin');

        $app->get('/signout', 'Iot\Controller\REST\UserController:actionPostUserSignout')
            ->name('post-user-signout');

        // Check an email to know existence user account. POST /api/users/exist
        $app->post('/exist', 'Iot\Controller\REST\UserController:actionPostUserExist')
            ->name('post-user-is-exist');

        // Change user’s password. POST /users/change/password
        $app->post('/change/password', 'Iot\Controller\REST\UserController:actionPostUserChangePassword')
            ->name('post-user-change-password');

        // Check user’s verification code. POST /users/verify/code
        $app->post('/verify/code', 'Iot\Controller\REST\UserController:actionPostUserVerifyCode')
            ->name('post-user-verify-code');

        $app->group('/info', function () use ($app) {
            $app->get('/', 'Iot\Controller\REST\UserController:actionPostCurrentUserInfo')
                ->name('post-my-info');
            $app->get('/:user_id', 'Iot\Controller\REST\UserController:actionPostUserInfo')
                ->conditions(array('user_id' => '\d+'))
                ->name('post-user-info');
        });



        // Show a list of user’s sensors.
        $app->get('/:user_id/sensor', 'Iot\Controller\REST\UserController:actionGetUserSensor')
            ->conditions(array('user_id' => '\d+'))
            ->name('get-show-users-sensors');

        // Register a sensor.
        $app->post('/:user_id/register/sensor', 'Iot\Controller\REST\UserController:actionPostUserRegisterSensor')
            ->conditions(array('user_id' => '\d+'))
            ->name('post-add-sensor');

        // Deregister a sensor.
        $app->post('/:user_id/deregister/sensor', 'Iot\Controller\REST\UserController:actionPostUserDeregisterSensor')
            ->conditions(array('user_id' => '\d+'))
            ->name('post-delete-sensor');
    });

    // DATA API
    $app->group('/data', function () use ($app){
        //  Send a new air quality sensor data.
        $app->post('/send/air', 'Iot\Controller\REST\DataController:actionPostDataSendAir')
            ->name('post-send-sensor-data');

        //  Send a new heart sensor data.
        $app->post('/send/heart', 'Iot\Controller\REST\DataController:actionPostDataSendHeart')
            ->name('post-delete-heart-data');

        //  Show air quality data for google maps.
        $app->post('/read/maps', 'Iot\Controller\REST\DataController:actionPostReadMaps')
            ->name('post-read-data-maps');
    });
});