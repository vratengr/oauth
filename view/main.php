<?php
/**
 * View file for main page
 *
 * @author Vanessa Richie Alia-Trapero <vrat.engr@gmail.com>
 */

global $config;
?>

<html>
    <head>
        <title>OAuth Login</title>
    </head>
    <body>
        <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js"></script>
        <script async defer src="https://accounts.google.com/gsi/client"></script>
        <script type="text/javascript" src="/assets/js/jquery.min.js"></script>
        <script type="text/javascript" src="/assets/js/main.js"></script>
        <link type="text/css" rel="stylesheet" href="/assets/css/main.css">

        <div id="message"><?= ((isset($_SESSION['message'])) ? $_SESSION['message'] : '') ?></div>
        <div id="main">
            <div id="login-container" class="section">
                <form id="login-email" method="post" action="/main/login">
                    <div class="row"><input type="text" name="email" placeholder="Email" /></div>
                    <div class="row"><input type="password" name="password" placeholder="Password" /></div>
                    <div class="row"><input type="hidden" name="auth" value="email" /></div>
                    <div class="row"><input class="btn" type="submit" name="login" value="Log In" disabled/></div>
                </form>
                <hr/>
                <div class="or">OR</div>
                <div id="login-oauth">
                    <div class="row"><button id="google-login" class="btn" data-auth="google"><img src="/assets/icons/google.svg" /><span>Continue with Google</span></button></div>
                    <div class="row"><button class="btn" data-auth="facebook"><img src="/assets/icons/facebook.svg" /><span>Continue with Facebook</span></button></div>
                    <!-- @vrat: next chapter -->
                    <!-- <div class="row"><button class="btn" data-auth="microsoft"><img src="/assets/icons/microsoft.svg" /><span>Continue with Microsoft</span></button></div> -->
                    <!-- <div class="row"><button class="btn" data-auth="apple"><img src="/assets/icons/apple.svg" /><span>Continue with Apple</span></button></div> -->
                </div>
            </div>

            <div id="register-container" class="section">
                <form id="register-email" method="post" action="/main/register">
                    <div class="row"><input type="text" name="name" placeholder="Name" /></div>
                    <div class="row"><input type="text" name="email" placeholder="Email" /></div>
                    <div class="row"><input type="password" name="password" placeholder="Password" /><img id="view-password" data-state="off" src="/assets/icons/eye-slash-regular.svg" /></div>
                    <div class="row"><input type="hidden" name="auth" value="email" /></div>
                    <div class="row"><input class="btn" type="submit" name="register" value="Register" disabled/></div>
                </form>
                <hr/>
                <div class="or">OR<br/>Create an account</div>
                <div id="register-oauth">
                    <div class="row"><button class="btn" data-auth="google"><img src="/assets/icons/google.svg" /></button></div>
                    <div class="row"><button class="btn" data-auth="facebook"><img src="/assets/icons/facebook.svg" /></button></div>
                    <!-- @vrat: next chapter -->
                    <!-- <div class="row"><button class="btn" data-auth="microsoft"><img src="/assets/icons/microsoft.svg" /></button></div> -->
                    <!-- <div class="row"><button class="btn" data-auth="apple"><img src="/assets/icons/apple.svg" /></button></div> -->
                </div>
            </div>
        </div>

        <script type="text/javascript">
            main.authRedirect       = "<?= $config->authRedirect ?>";
            main.goClientId         = "<?= $config->google['clientId'] ?>";
            main.fbAppId            = "<?= $config->facebook['appId'] ?>";
            main.fbVersion          = "<?= $config->facebook['version'] ?>";
        </script>
    </body>
</html>