<?php
/**
 * This will handle all Facebook transactions
 *
 * @author Vanessa Richie Alia-Trapero <vrat.engr@gmail.com>
 */

declare(strict_types = 1);
require_once('vendor/autoload.php');

class facebook
{
    private $client;
    private $auth = 'facebook';

    /**
     * initialize Facebook client
     */
    public function __construct() {
        global $config;
        $this->client = new Facebook\Facebook([
            'app_id'                => $config->facebook['appId'],
            'app_secret'            => $config->facebook['appSecret'],
            'default_graph_version' => $config->facebook['version'],
        ]);
    }

    /**
     * create Facebook's login endpoint
     *
     * @return  string $url    Facebook's login endpoint
     */
    public function authenticate() : string {
        global $config;
        // the PHP SDK straight from composer has some deprecated errors which halts the redirect, fix the deprecated errors to continue
        $helper     = $this->client->getRedirectLoginHelper();
        $callbackUrl= $config->authRedirect . $this->auth . '?method=register'; // we are passing an optional query string which will be used by our callback function to determine what happens after we received Facebook's response
        $authUrl    = $helper->getLoginUrl($callbackUrl, $config->facebook['permissions']);
        return $authUrl;
    }

    /**
     * handles callback from Facebook once user has been authenticated
     * for our demo, registration uses PHP SDK, so we need to get an access token to get the user's details
     * for login, we are using the JS SDK and we already got the user data in JS, so we are just transforming the data for later use in validating within our system
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
            $helper = $this->client->getRedirectLoginHelper();
            try {
                $accessToken = $helper->getAccessToken();
            } catch (Exception $e) {
                $result['error'] = 'Facebook SDK returned an error while trying to get access token: ' . $e->getMessage();
            }

            if (isset($accessToken)) {
                $token = $accessToken->getValue();
                $this->client->setDefaultAccessToken($token);

                try {
                    // we need to explicitly specify the fields we want Facebook to return
                    // in here, we're specifying all the default public profile fields + permissions
                    $request = $this->client->get('/me?fields=name,email,id,first_name,last_name,middle_name,name_format,picture,short_name,permissions');
                    $user = $request->getGraphNode()->asArray();

                    $result['authData'] = $user;
                    $result['authData']['token'] = $token;
                    $result['authData']['auth'] = $this->auth;
                } catch (Exception $e) {
                    $result['error'] = 'Facebook SDK returned an error while trying to get user data: ' . $e->getMessage();
                }
            }
        }
        return $result;
    }

    /**
     * unlinks our app in user's list of Facebook connected apps
     */
    public function revokeAccess() : void {
        // the revoke function in JS SDK works even if you logged in/register via PHP SDK, same is true with the PHP revoke, so you can use either one of it
        return;

        // keeping the code here for reference, but skipping execution
        try {
            $token = (is_array($_SESSION['authData']['token'])) ? $_SESSION['authData']['token']['accessToken'] : $_SESSION['authData']['token'];
            $request = $this->client->delete('/me/permissions?access_token=' . $token);
        } catch (Exception $e) {
            error_log('Facebook SDK returned an error while trying to revoke access: ' . $e->getMessage());
        }
    }
}
