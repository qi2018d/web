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
});