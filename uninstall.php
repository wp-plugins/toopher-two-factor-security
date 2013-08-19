<?php
  if (!defined('WP_UNINSTALL_PLUGIN')){
      exit();
  }
  
  delete_site_option('toopher_api_key');
  delete_site_option('toopher_api_secret');
  delete_site_option('toopher_api_url');

  $user_options = array(
      't2s_user_paired',
      't2s_pairing_id',
      't2s_authenticate_login',
      't2s_authenticate_profile_update'
  );

  $users = get_users('search=*');
  foreach( $users as $user ){
      $uid = $user->ID;
      foreach ($user_options as $user_option ){
          delete_user_option($uid, $user_option);
          delete_user_option($uid, $user_option, true);
      }
  }
?>
