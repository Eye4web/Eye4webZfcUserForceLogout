Eye4webZfcUserForceLogout
=======
[![Build Status](https://travis-ci.org/Eye4web/Eye4webZfcUserForceLogout.svg?branch=master)](https://travis-ci.org/Eye4web/Eye4webZfcUserForceLogout)
[![Code Climate](https://codeclimate.com/github/Eye4web/Eye4webZfcUserForceLogout/badges/gpa.svg)](https://codeclimate.com/github/Eye4web/Eye4webZfcUserForceLogout)
[![Test Coverage](https://codeclimate.com/github/Eye4web/Eye4webZfcUserForceLogout/badges/coverage.svg)](https://codeclimate.com/github/Eye4web/Eye4webZfcUserForceLogout)

Introduction
------------
This module will force log the user to log out if the flag is set in database

Installation
------------
#### With composer

1. Add this project composer.json:

    ```json
    "require": {
        "eye4web/eye4web-zfc-user-force-logout": "dev-master"
    }
    ```

2. Now tell composer to download the module by running the command:

    ```bash
    $ php composer.phar update
    ```

3. Enable it in your `application.config.php` file.

    ```php
    <?php
    return array(
        'modules' => array(
            // ...
            'Eye4web\ZfcUser\ForceLogout'
        ),
        // ...
    );
    ```

4. Make your user entity implement `Eye4web\ZfcUser\Entity\UserForceLogoutInterface`
