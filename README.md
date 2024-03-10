# WordPress MimbleWimble Coin Donation Button

### Description
Plugin for WordPress that adds a MimbleWimble Coin donation button to WordPress's block editor blocks that's capable of accepting MimbleWimble Coin donations without having to run any wallet software.

### Installing
Download this plugin's [newest release](https://github.com/NicolasFlamel1/WordPress-MimbleWimble-Coin-Donation-Button/releases) and choose to upload it on your WordPress site's add plugins page. After it's been installed and activated, you can add a MimbleWimble Coin donation button to any page in WordPress's block editor.

This plugin relies on PHP's FFI API to load shared libraries at runtime. This API can be enabled by adding `ffi.enable=true` to your `php.ini` file or by adding `php_admin_value ffi.enable 1` to your `httpd.conf` or `apache2.conf` file.
