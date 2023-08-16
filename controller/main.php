<?php
/**
 * Controller file for the entire site
 *
 * @author Vanessa Richie Alia-Trapero <vrat.engr@gmail.com>
 */

declare(strict_types = 1);
require_once('model/users.php');

class mainController
{
    private $user;

    public function __construct() {
        session_start();
        $this->user = new usersModel();
    }

    /**
     * renders the main page
     */
    public function index() : void {
        if (isset($_SESSION['userData'])) {
            // if user was already logged in, redirect them back to the welcome page
            $this->redirect('main/welcome');
        } else {
            require_once('view/main.php');
            // message is usually set when there are errors, let's unset it so a refresh removes the message
            unset($_SESSION['message']);
        }
    }

    /**
     * renders the welcome page once successfully logged in/register
     */
    public function welcome() : void {
        if (!isset($_SESSION['userData'])) {
            // go back to the login/registration page if we don't have the necessary data
            $this->reset('An error occurred while processing your request');
        } else {
            require_once('view/welcome.php');
        }
    }

    /**
     * handle registration request
     *
     * if registration is via email, this will save the data to DB on first pass
     * if registration is via oauth, on first pass, it will create the redirect link for the third-party app's login page
     *      on second pass, once we got authentication from the third-party app, we'll save the data to the DB
     */
    public function register() : void {
        $data = (isset($_SESSION['authData'])) ? $_SESSION['authData'] : $_POST;

        if (($data['auth'] != 'email') && !isset($data['token'])) {
            // if this is an initial oauth registration, we need to redirect to the third-party app's site first to get user authentication and details
            // we have created separate class files per third-party app so each can implement their own authentication process
            $auth = $data['auth'];
            require_once("library/$auth.php");
            $app = new $auth();
            $this->redirect(null, $app->authenticate());

        } else {
            // since we're here, so it's either this is an email registration
            // or we have already received authorization from the third-party app along with the user's data

            // let's then check if this email address has already been used for a different app
            // in our case, we won't allow it to avoid multi-platform usage of the same credentials
            $userAuth = $this->user->getAuth($data['email']);
            if (!$userAuth) {
                // this is a new user
                $user = $this->user->save($data);
                $_SESSION['userData'] = $user;
                $this->redirect('main/welcome');

            } else if ($userAuth != $data['auth']) {
                // this is a returning user using a different platform for the same email
                $this->reset('Email address already registered via ' . ucfirst($userAuth) . '. Kindly login instead via ' . ucfirst($userAuth) . '.');

            } else {
                // this is for users trying to register an already registered email for the current auth type
                // for user convenience, you may also directly login the user
                // however, in this demo we would like to subtly inform the user that they already have registered before
                $this->reset('Email address already registered. Kindly login instead.');
            }
        }
    }

    /**
     * handles login request
     *
     * if login is via email, this will validate the user inputs from the DB
     * if login is via oauth, we've used the SDKs from the third-party app in JS, so once it gets here, user has already been authenticated in the third-party app
     *      we then simply validate if that user is already in our system
     */
    public function login() : void {
        $data = (isset($_SESSION['authData'])) ? $_SESSION['authData'] : $_POST;
        $userAuth = $this->user->getAuth($data['email']);

        if (!$userAuth) {
            $this->reset('No record found!');

        } else if ($userAuth != $data['auth']) {
            $this->reset('Email address already used as login via ' . ucfirst($userAuth) . '. Kindly login via ' . ucfirst($userAuth) . '.');

        } else {
            if ($user = $this->user->get($data)) {
                $_SESSION['userData'] = $user;
                $this->redirect('main/welcome');
            } else {
                $this->reset('Email/password is incorrect.');
            }
        }
    }

    /**
     * destroys user and auth sessions
     * this is also re-used for logout since that's just basically what we need for logout here
     *
     * @param   string $message     error message to be displayed in the login/registration page
     */
    public function reset(?string $message) : void {
        $this->user->close();
        unset($_SESSION['authData']);
        unset($_SESSION['userData']);
        $_SESSION['message'] = ($message) ? $message : 'Successfully logged out!';
        $this->redirect('');
    }

    /**
     * revoke any third-party app links then deletes user account in our system
     */
    public function delete() : void {
        $data = $_POST;
        if ($data['auth'] != 'email') {
            $auth = $data['auth'];
            require_once("library/$auth.php");
            $app = new $auth();
            $app->revokeAccess();
        }
        $this->user->delete($data['email']);
        $this->reset('Account successfully deleted.');
    }

    /**
     * handles callback from third-party apps once user has been authenticated
     *
     * @param   string $auth    auth type/third party site (eg: google/facebook)
     */
    public function callback(string $auth) : void {
        $data = array_merge($_POST, $_GET);
        if (isset($data['error'])) {
            $this->reset('An error occurred while processing your request: ' . $data['error']);

        } else {
            require_once("library/$auth.php");
            $oauth = new $auth();
            $result = $oauth->callback($data);

            if (isset($result['error'])) {
                $this->reset($result['error']);

            } else {
                $_SESSION['authData'] = $result['authData'];
                $method = (isset($data['method'])) ? $data['method'] : $data['state'];
                $this->redirect('main/' . $method);
            }
        }
    }

    /**
     * handles page redirection
     * if this is called via ajax, the url is returned so the calling function and it will do the actual redirect
     *
     * @param   string $internalPath    relative path within our site, could be null if we are using an absolute url to an external site
     * @param   string $externalUrl     url of an external site, this could be the third-party app's login site
     */
    private function redirect(?string $internalPath, string $externalUrl = '') : void {
        global $config;
        $isAjax     = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        $fullUrl    = (isset($internalPath)) ? $config->url . $internalPath : $externalUrl;

        if ($isAjax) {
            die($fullUrl);
        } else {
            header('Location: ' . $fullUrl);
        }
    }
}
