/**
 * JS file for the entire site
 *
 * @author Vanessa Richie Alia-Trapero <vrat.engr@gmail.com>
 */

main = {
    setup: function() {
        main.initializeGoogle();
        main.initializeFB();
        main.setEventHandlers();
    },

    initializeGoogle: function() {
        window.onload = function() {
            google.accounts.id.initialize({
                client_id   : main.goClientId,
                callback    : main.googleLogin
            });
            if (!window.location.href.includes('welcome')) {
                // there are 2 ways to render the button, via HTML or JS
                // with HTML, you just need to use specific Google defined IDs and Google's SDK will transform the element
                // meanwhile, here in JS, you just specify the container ID, and the sign in button will be placed in it
                google.accounts.id.renderButton(
                    document.getElementById('google-login'),
                    {text: 'continue_with', locale: 'en_US', logo_alignment: 'center'}
                );
                // this is if you want to also show the one tap option that is usually shown on the upper right corner of the page
                // note that if user opts out of one tap, this will not work
                google.accounts.id.prompt();
            }
        }
    },

    initializeFB: function() {
        // ensure that FB script has already been loaded before calling any FB funcs
        window.fbAsyncInit = function() {
            FB.init({
                appId       : main.fbAppId,
                version     : main.fbVersion,
            });
        };
    },

    setEventHandlers: function() {
        $('input').on('input', main.validateInputs);
        $('#view-password').on('click', main.viewPassword);
        $('#delete').on('click', main.deleteAccount);

        // to diversify our demo, we will be implementing oauth login using the third-party app's JS SDK/library
        // while we will use PHP SDK for registration
        // you actually only need one of these for both login and register
        $('#register-oauth button').on('click', main.initiateOauthRegistration);
        $('#login-oauth button').on('click', main.initiateOauthLogin);
    },

    validateInputs: function() {
        // you could do a couple of validations here like check email format or password rules
        // but now, we'll only be checking that all fields are filled in
        let form = $(this).closest('form');
        if ($('input', form).filter(function() {return $(this).val().trim().length == 0}).length == 0) {
            $(form).find('.btn').attr('disabled', false);
        } else {
            $(form).find('.btn').attr('disabled', true);
        }
    },

    viewPassword: function() {
        // since we are not showing a confirm password option, let's offer a way to view the password to ensure that user haven't mis-typed it
        state = $(this).data('state');
        if (state == 'off') {
            $(this).data('state', 'on');
            $(this).parent().find('input').attr('type', 'text');
            $(this).attr('src', '/assets/icons/eye-regular.svg');
        } else {
            $(this).data('state', 'off');
            $(this).parent().find('input').attr('type', 'password');
            $(this).attr('src', '/assets/icons/eye-slash-regular.svg');
        }
    },

    deleteAccount: function() {
        url = '/main/delete';
        params = {
            auth    : $(this).data('auth'),
            email   : $(this).data('email'),
            id      : $(this).data('id'),
        };

        if (params.auth == 'google') {
            // we are using Sign in With Google for login and PHP SDK for registration
            // Sign in With Google is only for authentication and returns a JWT with no access token
            // while PHP SDK can also be used for authorization and has an access token
            // due to these differences, revoking will depend on which SDK used
            // note that there is also a JS SDK that includes authorization, but for this demo, we're not gonna cover that
            google.accounts.id.revoke(params.email, function(response) {
                $.post(url, params, function(result) { location.href = result; });
            });

        } else if (params.auth == 'facebook') {
            // the revoke function in JS SDK works even if you logged in/register via PHP SDK, same is true with the PHP revoke, so you can use either one of it
            FB.api('/me/permissions', 'DELETE', {access_token: main.fbToken}, function(response) {
                $.post(url, params, function(result) { location.href = result; });
            });
        } else { 
            // if email
            $.post(url, params, function(result) { location.href = result; });
        }
    },

    initiateOauthRegistration: function() {
        auth = $(this).data('auth');
        url     = '/main/register';
        params  = {auth: auth};
        $.post(url, params, function(response) { location.href = response; });
    },

    initiateOauthLogin: function() {
        auth = $(this).data('auth');
        if (auth == 'google') {
            // this is already handled by Google's SDK in initializeGoogle()
        } else if (auth == 'facebook') {
            main.fbLogin();
        }
    },

    googleLogin: function(response) {
        // google library does the checking of state and calling of login, what we have here is only the result of it's internal processing
        user = main.getGoogleUserData(response.credential);
        main.oauthLogin('google', user, response);
    },

    getGoogleUserData: function(token) {
        // this is actually just decoding the JWT from Google's response
        // you can also pass this credential in your backend and use Google's verifyIdToken() thru it's PHP SDK
        var base64Url = token.split('.')[1];
        var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        var jsonPayload = decodeURIComponent(window.atob(base64).split('').map(function(c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));

        return JSON.parse(jsonPayload);
    },

    fbLogin: function() {
        // unlike google, we will do the actual checking of state and call login if necessary
        FB.getLoginStatus(function(response) {
            if (response.status != 'connected') {
                FB.login(function(result) {
                    main.getFbUserData(result.authResponse);
                }, {scopes: 'email,public_profile'});
            } else {
                main.getFbUserData(response.authResponse);
            }
        });
    },

    getFbUserData: function(token) {
        // getting all the default public profile fields + permissions
        fields  = 'name,email,id,first_name,last_name,middle_name,name_format,picture,short_name,permissions';
        FB.api('/me', {fields: fields}, function(response) {
            main.oauthLogin('facebook', response, token);
        });
    },

    oauthLogin: function(auth, user, token) {
        // this will call the actual login handler in our controller
        url = main.authRedirect + auth;
        params  = {
            auth    : auth,
            info    : user,
            token   : token,
            method  : 'login',
        };
        $.post(url, params, function(response) { location.href = response; });
    },
};

$(document).ready(main.setup);