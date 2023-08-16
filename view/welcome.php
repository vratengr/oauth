<?php
/**
 * View file for welcome page once user successfully logs in/register
 *
 * @author Vanessa Richie Alia-Trapero <vrat.engr@gmail.com>
 */

global $config;
$user = $_SESSION['userData'];
$user['info'] = json_decode($user['info'], true);
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

        <div id="welcome">
            <div class="name">Hi <span><?= $user['name'] ?></span>!</div>
            <div>You've logged in via <?= ucfirst($user['auth']) ?></div>
            <?php if ($user['auth'] != 'email') { ?>
                <div class="info">
                    <div>Here's what <?= ucfirst($user['auth']) ?> has shared to us:</div>
                    <div><pre><?= print_r($user['info'], true); ?></pre></div>
            </div>
            <?php } ?>
            <div><a href="/main/reset"><button>Logout</button></a></div>
            <div><button id="delete" data-auth="<?=$user['auth']?>" data-email="<?=$user['email']?>" data-id="<?= $user['info']['id'] ?>">Delete Account</button></div>
        </div>

        <script type="text/javascript">
            main.auth               = "<?= $user['auth'] ?>";
            main.goClientId         = "<?= $config->google['clientId'] ?>";
            main.fbAppId            = "<?= $config->facebook['appId'] ?>";
            main.fbVersion          = "<?= $config->facebook['version'] ?>";
            main.fbToken            = "<?= ($user['auth'] == 'facebook') ? ((is_array($_SESSION['authData']['token'])) ? $_SESSION['authData']['token']['accessToken'] : $_SESSION['authData']['token']) : ''?>";
        </script>
    </body>
</html>