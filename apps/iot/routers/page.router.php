<?php

/* routes for pages */

$app->get('/', 'Iot\Controller\PageController:actionHome')->name('get-home-page');

$app->get('/map', 'Iot\Controller\PageController:actionMap')->name('get-map-page');

$app->group('/user', function() use ($app) {
    $app->get('/', 'Iot\Controller\PageController:actionUser')->name('get-user-page');
    $app->get('/changepw', 'Iot\Controller\PageController:actionUserChangePassword')->name('get-user-changepw-page');
    $app->get('/cancelid', 'Iot\Controller\PageController:actionUserCancelID')->name('get-user-cancelid-page');
});

$app->get('/sensor', 'Iot\Controller\PageController:actionSensor')->name('get-sensor-page');

$app->get('/signin', 'Iot\Controller\PageController:actionSignin')->name('get-signin-page');

$app->group('/signup', function() use ($app){

    $app->get('/', 'Iot\Controller\PageController:actionSignup')->name('get-signup-page');

    $app->get('/validation', 'Iot\Controller\PageController:actionSignupValidation')->name('get-signup-validation-page');
});


$app->get('/developers', 'Iot\Controller\PageController:actionDevelopers')->name('get-developers-page');