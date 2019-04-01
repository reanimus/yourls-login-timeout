Login Timeout
====================

Plugin for [YOURLS](http://yourls.org).


Description
-----------

This plugin adds simple login timeout functionality that kicks in after a given number of logins over a given period of time. By default, this is 5 logins per 15 minutes, but this can be configured. When the limit is reached, the IP address is blocked from logging in for the timeout period. If another login attempt is made before the timeout expires, the timeout resets.

Installation
------------
1. Put `plugin.php` into `/user/plugins`.
2. Set `MAX_FAILED_LOGINS` and `LOGIN_LOCKOUT_MINUTES` in your `config.php` if you want to use something other than the defaults.
3. Go to the plugin administration page and enable the plugin.

License
------------
Licensed under the 2-Clause BSD License.
