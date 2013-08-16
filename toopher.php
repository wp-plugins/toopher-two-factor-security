<?php
/* 
Plugin Name: Toopher Two-Factor Authentication
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

function enqueue_jquery_cookie(){
    wp_enqueue_script('jquery-cookie', plugins_url('js/jquery.cookie.min.js', __FILE__));
}

add_action('admin_enqueue_scripts', 'enqueue_jquery_cookie');

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
