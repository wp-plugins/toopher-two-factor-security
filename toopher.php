<?php
/* 
Plugin Name: Toopher Two-Factor Authentication (BETA)
Plugin URI: http://wordpress.org/plugins/toopher-two-factor-security/
Description: Toopher's Location-based Two-Factor Authentication protects your website from unauthorized logins.
Version: 1.3
Author: Toopher, Inc.
Author URI: https://www.toopher.com
License: GPLv2 or later
 */

define ('TOOPHER_PLUGIN_ID', 'ToopherForWordpress');
define ('TOOPHER_PLUGIN_URL', plugins_url('', __FILE__));

function strip_wp_magic_quotes($s){
    if (get_magic_quotes_gpc() || function_exists('wp_magic_quotes')){
        return stripslashes($s);
    } else {
        return $s;
    }
}

add_filter('plugin_action_links', 'toopher_plugin_action_links', 10, 2);

require('lib/ajax-endpoints.php');
require('lib/toopher-authenticate-login.php');
require('lib/toopher-authenticate-profile-update.php');
require('lib/toopher-user-options.php');
require('lib/toopher-settings.php');

if(!function_exists('_log')){
  function _log( $message ) {
    if( WP_DEBUG === true ){
      if( is_array( $message ) || is_object( $message ) ){
        error_log('dumping an object...');
        echo('dumping an object');
        error_log(print_r( $message , true));
        echo(var_export( $message , true));
      } else {
        error_log( $message );
      }
    }
  }
}

function toopher_plugin_action_links($links, $file) {
    // h/t to http://www.wpmayor.com/code/provide-a-shortcut-to-your-settings-page-with-plugin-action-links/
    static $this_plugin;

    if (!$this_plugin) {
        $this_plugin = plugin_basename(__FILE__);
    }

    if ($file == $this_plugin) {
        $settings_link = '<a href="options-general.php?page=ToopherForWordpress">Settings</a>';
        array_unshift($links, $settings_link);
    }

    return $links;
}

if(!class_exists('ToopherWordpress') && !isset($toopherWordpress)) :
    class ToopherWordpress
    {
        public function __construct()
        {
        }
    }
    $toopherWordpress = new ToopherWordpress();


endif;


?>
