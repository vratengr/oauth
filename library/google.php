<?php
/**
 * This will handle all Google transactions
 *
 * @author Vanessa Richie Alia-Trapero <vrat.engr@gmail.com>
 */

declare(strict_types = 1);
require_once('vendor/autoload.php');
use Google\Client;
use Google\Service\Oauth2;

class google
{
    private $client;
    private $auth = 'google';

    /**
     * initialize Google client
     */
    public function __construct() {
        global $config;
        $this->client = new Client();
        $this->client->setAuthConfig($config->google['config']);
        $this->client->addScope($config->google['scope']);
        $this->client->setRedirectUri($config->authRedirect . 'google');
    }

    /**
     * create Google's login endpoint
     *
     * @return  string $url    Google's login endpoint
     */
    public function authenticate() : string {
        // state will be used to determine what happens after Google authenticates the user
        // since the demo is using JS SDK for login, and PHP SDK for registration, hence we could just manually put the value here
        // you can pass any string or even a query string here
        $this->client->setState('register');

        $authUrl    = $this->client->createAuthUrl();
        $url        = filter_var($authUrl, FILTER_SANITIZE_URL);
        return $url;
    }

    /**
     * handles callback from Google once user has been authenticated
     * for our demo, registration uses PHP SDK, so we get a <code> which we need to convert to access token so we can get user details
     * for login, we are using Google's JS SDK and we already got the user data in JS, so we are just transforming the data for later use in validating within our system
     *
     * @param   array $data         response from Google
     * @return  array $authData     extracted data to be used in the system
     */
    public function callback(array $data) : array {
        $result = [];
        if (isset($data['info'])) {
            $result['authData'] = $data['info'];
            $result['authData']['token'] = $data['token'];
            $result['authData']['auth'] = $this->auth;

        } else {
            $token = $this->client->fetchAccessTokenWithAuthCode($data['code']);
            $this->client->setAccessToken($token);
            $auth = new Oauth2($this->client);
            try {
                // if the token is no longer valid, calling any APIs will return an error
                $user = $auth->userinfo->get();
                $result['authData'] = json_decode(json_encode($user), true);
                $result['authData']['token'] = $token;
                $result['authData']['auth'] = $this->auth;
            } catch (Exception $e) {
                $result['error'] = 'Google SDK returned an error while getting user data: ' . $e->getMessage();
            }
        }
        return $result;
    }

    /**
     * unlinks our app in user's list of Google connected apps
     * this only works during registration since our registration uses PHP SDK
     * login uses JS SDK and as such, revoke will be handled via JS as well
     */
    public function revokeAccess() : void {
        try {
            $this->client->setAccessToken($_SESSION['authData']['token']);
            $this->client->revokeToken();
        } catch (Exception $e) {
            error_log('Google SDK returned an error while trying to revoke access: ' . $e->getMessage());
        }
    }
}
