# Login via OAuth / OpenID

### BRIEF
Sample OAuth login for commonly used third-party apps
- Google
    - [Sign in With Google](https://developers.google.com/identity/gsi/web/guides/overview) (authentication only)
    - [Server-side Web Apps using PHP SDK](https://developers.google.com/identity/protocols/oauth2/web-server)
- Facebook
    - [Facebook Login for the Web with Javascript SDK](https://developers.facebook.com/docs/facebook-login/web)
    - [Facebook Login for the Web using PHP SDK](https://github.com/facebookarchive/php-graph-sdk/tree/master/docs?fbclid=IwAR1F6ZuACuYdD5wc7ljaYhSzN9HTu7bo2xaYaNeiuQk7HTsBq1al0Lu5wDQ)


### BACKGROUND
One of the struggles I had with some guides is that some of them are too fragmented, which sometimes ends in the question - How to actually run it? I love Stack Overflow which helps putting up the not-working pieces, but my hopes for this is to actually have a guide that shows the entire flow.


### ABOUT THE CODE
Backend is based on PHP with a basic made-up MVC framework. (There are many beautiful frameworks out there but I intend to minimize the code to only include the necessary.) Registration process for third-party apps are using their respective PHP SDK, which was downloaded via composer. Meanwhile, login process is using the third-party app's Javascript SDK.

Comments are added within each file. If this is your first time, start by checking index.php and follow thru each function calls.


### DISCLAIMER
Note that for demo purposes, I have used both the third-party app's Javascript and PHP SDKs, but you only need to use one.

As of the moment, this only covers the basics of oauth login, logout and revoking app access and might not cover all of your project requirements.


### WORKFLOW
- User logs in / registers via email
    - this simply transacts with our simple DB table
- User registers via third-party apps (using PHP SDK)
    1. on click of the oauth buttons, we will generate the login url and redirect to it
    1. user will be asked if they want to grant access to our app (this is handled by the third-party app)
    1. third-party app then informs us of user's decision
        - if access granted
            - check for email uniqueness to avoid multi-platform accounts, so if that email was already used to login using a different third-party app, we'll just tell the user to login using that other app
            - if email is unique, save user data and log in the user
        - if not, well, what can we do? - none 😝
- User logs in via third-party apps (using Javascript SDK)
    - same as registering, but since user already granted access before, it simply just lets the user login
    - there's also no need to save the user data as we already have that data in our DB, instead, just retrieve user details from the DB
    - other apps let's the user register even if login button was used
        - in the event that the user has not registered before, for our case, we'll just tell the user to register first
        - note that this can be handled differently depending on your specs
- User logs out
    - log the user out from our app by destroying our app's session, we don't need to touch user's session on the third-party app
- User deletes account
    - if user's account was registered thru third-party app, call the third-party app to revoke our access
        - we are doing both Javascript and PHP implementation for revoking access, only choose one that is consistent with your login flow
    - delete the user's account in our system
    - call logout flow


### TECHNOLOGIES
- OAuth
- PHP
- MySQL
- HTML
- CSS
- SASS
- Javascvript
- JQuery
- AJAX
- Composer
- MVC


### HELPFUL GUIDES / NOTES / TIPS
- Google
    - [Google API Dashboard](https://console.cloud.google.com/apis/dashboard)
    - [Google PHP SDK](https://github.com/googleapis/google-api-php-client)
    - If you only need email, profile and openid scopes, [Sign In With Google](https://developers.google.com/identity/gsi/web/guides/overview) is the recommended option
        - this is what we are using in the Google login part
- Facebook
    - [Facebook/Meta API Dashboard](https://developers.facebook.com/apps)
    - [Facebook PHP SDK](https://github.com/facebookarchive/php-graph-sdk/tree/master/docs)
    - Asking for email and public_profile does not require App Review
    - Testing the login flow will not require Business Verification as long as you add all test users with project roles
        - https://developers.facebook.com/apps/{appId}/roles/roles
    - Once your app will be used by public users, your app needs to undergo [Business Verification](https://www.facebook.com/business/help/1095661473946872)
- Redirect URIs should be in https
    - If you're developing locally, create local certificates by following [this guide for ubuntu-xampp users](https://coderoffice.blogspot.com/2020/03/enabling-https-ssl-on-lampp-xampp-at.html), or [this guide which works in windows-wsl2-docker](https://realtechtalk.com/[warn]_RSA_server_certificate_is_a_CA_certificate_BasicConstraints_CA_TRUE__Apache_Error_Solution-1870-articles)
        - then add it to trusted list following the 2nd option in [this stack thread](https://unix.stackexchange.com/questions/90450/adding-a-self-signed-certificate-to-the-trusted-list#answer-132163)
- Install Google and Facebook's PHP SDK via composer
    - run in terminal: composer require google/apiclient facebook/graph-sdk
    - these are the files under library/vendor, library/composer.json and library/composer.lock - so don't worry about coding them
- If you will be offering multi-platform login/registration options, check for email uniqueness to avoid creating duplicate accounts for each platform.
- Always assume token expiration time is short andd they could change anytime depending on the third-party app, so check for token validity periodically or better yet, every page load.
    - [check this guide for possible token expiration scenarios](https://developers.google.com/identity/protocols/oauth2#expiration)


### DATABASE
The structure below is the one used for this demo and is meant for guidance but is not required when implementing it on your project
- Users
    - id
    - date_created
    - last_modified
    - email
    - password - will be empty if from oauth
    - name
    - auth - <email, google, facebook, microsoft, apple>
    - info - any data passed by oauth
```
CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
    `date_created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    `last_modified` DATETIME on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    `auth` VARCHAR(25) NOT NULL ,
    `email` VARCHAR(100) NOT NULL ,
    `password` VARCHAR(100) NOT NULL ,
    `name` VARCHAR(100) NOT NULL ,
    `info` TEXT NOT NULL ,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;
```

### MY THOUGHTS - don't read 😛
I initially planned on implementing PHP SDK alone, but as I went along, I found a JS implementation, and if you're only concerned of authenticating and not authorizing, then JS would be an easier route. I got turned between two lovers, so decided to implement both.

I would like to implement Microsoft and Apple login since I haven't done so since, but maybe on a later free time. Then, since we've covered the basics of calling the third-party's SDK, it would be nice to call some sample APIs.

This will probably be just my notes, not sure if I ever want this public, but I do hope it would help someone, so maybe will make it public later. 😅