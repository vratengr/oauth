<?php
/**
 * Contains all the site config.
 * Since this is a fairly small project, we'll just keep one site-wide config file.
 *
 * @author Vanessa Richie Alia-Trapero <vrat.engr@gmail.com>
 */

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// ini_set('error_log', '/opt/lampp/htdocs/projects/oauth/logs/php_log');
// ini_set('date.timezone', 'Asia/Manila');
error_reporting(E_ERROR);

global $config;

//site
$config                 = new stdClass();
$config->url            = 'https://' . $_SERVER['HTTP_HOST'] . '/';
$config->dir            = $_SERVER['DOCUMENT_ROOT'] . '/';
$config->authRedirect   = $config->url . 'main/callback/';

// database
$config->database       = [
    'host'              => 'db', // database service name in docker compose file or localhost if using XAMPP
    'user'              => 'user', // docker cannot use root user, it's reserved for the "root" user
    'password'          => 'user',
    'db'                => 'oauth',
];

//google
$google                 = json_decode(file_get_contents($config->dir . '/config/google.json'), true); // the downloaded credentials file from Google
$config->google         = [
    'config'            => $google,
    'authUri'           => $google['web']['auth_uri'], // extracted for simplicity in JS
    'clientId'          => $google['web']['client_id'], // extracted for simplicity in JS
    'scope'             => 'email profile',
];
    

// facebook
$facebook               = json_decode(file_get_contents($config->dir . '/config/facebook.json'), true); // manually created json file with credentials from Facebook
$config->facebook       = [
    'appId'             => $facebook['app_id'],
    'appSecret'         => $facebook['app_secret'],
    'version'           => 'v17.0',
    'permissions'       => ['email', 'public_profile'],
];