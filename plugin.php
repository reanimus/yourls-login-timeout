<?php
/*
Plugin Name: Login Timeout
Plugin URI: https://github.com/reanimus/yourls-login-timeout
Description: Adds a cap to the number of failed logins with configurable timeout.
Version: 1.0
Author: Alex Guzman
Author URI: http://guzman.io
 */

// In config:
// define( 'MAX_FAILED_LOGINS', 5 );
// define( 'LOGIN_LOCKOUT_MINUTES', 15 );

yourls_add_filter('is_valid_user', 'login_timeout_handle');

function login_timeout_handle( $in ) {
    $max = 5;
    $minutes = 15;
    if (defined('MAX_FAILED_LOGINS')) {
        $max = MAX_FAILED_LOGINS;
    }
    if (defined('LOGIN_LOCKOUT_MINUTES')) {
        $minutes = LOGIN_LOCKOUT_MINUTES;
    }

    $attempted_login = (yourls_is_API() && isset( $_REQUEST['signature'] )) ||
        (isset( $_REQUEST['username'] ) && isset( $_REQUEST['password'] ));

    // If not trying to log in, just pass the login status.
    if (!$attempted_login) {
        return $in;
    }
    
    $ip = $_SERVER['REMOTE_ADDR'];
    $stored_timeout = yourls_get_option("login_timeouts_$ip");

    if ($stored_timeout === false) {
        if (!$in) {
            $timeout = [
                'timestamp' => new DateTime('now'),
                'attempts' => 1,
            ];
            yourls_update_option("login_timeouts_$ip", serialize($timeout));
        }
        return $in;
    } else {
        $timeout = unserialize($stored_timeout);
        $now = new DateTime('now');
        $time_passed = $timeout['timestamp']->diff($now, true);
        if ($time_passed > new DateInterval(sprintf("P%dM", $minutes))) {
            if ($in) {
                yourls_delete_option("login_timeouts_$ip");
            } else {
                $timeout = [
                    'timestamp' => new DateTime('now'),
                    'attempts' => 1,
                ];
                yourls_update_option("login_timeouts_$ip", serialize($timeout));
            }
            return $in;
        } else {
            $timeout['timestamp'] = $now;
            if (!$in) {
                $timeout['attempts']++;
            } else if ($timeout['attempts'] < $max) {
                yourls_delete_option("login_timeouts_$ip");
                return $in;
            }

            if ($timeout['attempts'] >= $max) {
                $timeout['attempts'] = $max;
            }

            yourls_update_option("login_timeouts_$ip", serialize($timeout));

            if ($timeout['attempts'] == $max) {
                yourls_do_action( 'login_failed' );
                yourls_login_screen("You have exceeded the maximum number of " .
                    "login attempts ($max). Please wait $minutes minutes and " .
                    "try again.");
                die();
            }

            return $in;
        }
    }
}
