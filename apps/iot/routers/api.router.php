<?php
$app->group('/api', function () use ($app) {

    // USER API
    $app->group('/user', function () use ($app){

        /* user management */
        $app->group('/signup', function () use ($app){
            $app->post('/', 'Iot\Controller\REST\UserController:actionPostUserSignup')->name('post-user-signup');
            $app->post('/verify', 'Iot\Controller\REST\UserController:actionPostUserSignupVerifyCode')->name('post-user-signup-verify-code');
        });

        $app->post('/signin', 'Iot\Controller\REST\UserController:actionPostUserSignin')->name('post-user-signin');
        $app->get('/signout', 'Iot\Controller\REST\UserController:actionPostUserSignout')->name('post-user-signout');

        $app->post('/changepw', 'Iot\Controller\REST\UserController:actionPostUserChangePassword')->name('post-user-change-password');
        $app->group('/forgotpw', function () use ($app){
            $app->post('/', 'Iot\Controller\REST\UserController:actionPostUserForgotpw')->name('post-user-forgotpw');
            $app->post('/verify', 'Iot\Controller\REST\UserController:actionPostUserForgotpwVerifyCode')->name('post-user-forgotpw-verify-code');
            $app->post('/change', 'Iot\Controller\REST\UserController:actionPostUserForgotpwChange')->name('post-user-forgotpw-verify-code');

        });

        $app->get('/cancelid', 'Iot\Controller\REST\UserController:actionGetUserCancelID')->name('get-user-cancelid');

        $app->group('/info', function () use ($app) {
            $app->get('/', 'Iot\Controller\REST\UserController:actionGetCurrentUserInfo')->name('post-my-info');
            $app->get('/:user_id', 'Iot\Controller\REST\UserController:actionGetUserInfo')
                ->conditions(array('user_id' => '\d+'))->name('post-user-info');
        });
        /* user management */


        //$app->post('/exist', 'Iot\Controller\REST\UserController:actionPostUserExist')->name('post-user-is-exist');


        // Show a list of user’s sensors.
        $app->get('/:user_id/sensor', 'Iot\Controller\REST\UserController:actionGetUserSensor')
            ->conditions(array('user_id' => '\d+'))->name('get-show-users-sensors');

        // Register a sensor.
        $app->post('/:user_id/register/sensor', 'Iot\Controller\REST\UserController:actionPostUserRegisterSensor')
            ->conditions(array('user_id' => '\d+'))->name('post-add-sensor');

        // Deregister a sensor.
        $app->post('/:user_id/deregister/sensor', 'Iot\Controller\REST\UserController:actionPostUserDeregisterSensor')
            ->conditions(array('user_id' => '\d+'))->name('post-delete-sensor');
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

        $app->post('/read/maps/geojson', 'Iot\Controller\REST\DataController:actionPostReadMapsGeojson')
            ->name('post-read-data-maps-geojson');

        $app->get('/read/charts', 'Iot\Controller\REST\DataController:actionPostReadCharts')
            ->name('post-read-data-history');
    });
});