<?php

add_filter('user_profile_update_errors', 'toopher_begin_authorize_profile_update', 100, 3);
add_filter('user_profile_update_errors', 'toopher_finish_authorize_profile_update', 0, 3);


function toopher_begin_authorize_profile_update($errors, $update, $user){
    if ($errors->get_error_codes()){
        return;
    }
    if ((get_user_meta((int)$user->ID, 't2s_user_paired', true)) && (get_user_meta((int)$user->ID, 't2s_authenticate_profile_update', true))){
        if(isset($_POST['toopher_authentication_successful']) && ($_POST['toopher_authentication_successful'] === 'true')){
            return;
        } else {
            toopher_profile_update_pending($user);
            exit();
        }
    } else {
        toopher_apply_updated_user_settings($user);
    }

}

function toopher_finish_authorize_profile_update($errors, $update, $user){
    // make sure someone isn't trying to circumvent toopher-auth by submitting the authentication success flag through the browser
    if(isset($_POST['toopher_authentication_successful'])){
        unset($_POST['toopher_authentication_successful']);
    }

    if(isset($_POST['toopher_sig'])){
        $pending_user_id = $_POST['pending_user_id'];
        unset($_POST['pending_user_id']);
        $secret = get_site_option('toopher_api_secret');
        foreach(array('terminal_name', 'reason') as $toopher_key){
            $_POST[$toopher_key] = strip_wp_magic_quotes($_POST[$toopher_key]);
        }

        $pending_session_token = get_transient($pending_user_id . '_t2s_authentication_session_token');
        delete_transient($pending_user_id . '_t2s_authentication_session_token');
        $pending_updated_user = get_transient($pending_user_id . '_t2s_pending_profile_update_data');
        delete_transient($pending_user_id . '_t2s_pending_profile_update_data');
        $toopherSigData = $_POST;
        unset($toopherSigData['_wpnonce']);
        unset($toopherSigData['action']);

        if(($pending_session_token === $_POST['session_token']) && ToopherWeb::validate($secret, $toopherSigData, 100)){
            $authGranted = ($_POST['pending'] === 'false') && ($_POST['granted'] === 'true');
            $errors->errors = array();
            if($authGranted){
                $user = $pending_updated_user;
                toopher_apply_updated_user_settings($user);
            } else {
                $errors->add('toopher_auth_fail', __('<strong>Error</strong>: Toopher Two-Factor security prevented the attempt to update user settings.'));
            }
            $_POST['toopher_authentication_successful'] = $authGranted ? 'true' : 'false';
        } else {
            $errors->add('toopher_auth_invalid', __('<strong>Error</strong>: Toopher API Signature did not match expected value!'));
            $_POST['toopher_authentication_successful'] = 'false';
        }
    }
    return;
}

function toopher_profile_update_pending($user){
    $key = get_site_option('toopher_api_key');
    $secret = get_site_option('toopher_api_secret');
    $baseUrl = get_site_option('toopher_api_url');
    $automationAllowed = false;
    $session_token = wp_generate_password(12, false);
    set_transient($user->ID . '_t2s_authentication_session_token', $session_token, 2 * MINUTE_IN_SECONDS);
    set_transient($user->ID . '_t2s_pending_profile_update_data', $user, 2 * MINUTE_IN_SECONDS);
    $signed_url = ToopherWeb::auth_iframe_url($user->user_login, 'Update your profile settings', 100, $automationAllowed, $baseUrl, $key, $secret, $session_token);

    $toopher_finish_authenticate_parameters = array(
        'pending_user_id' => $user->ID,
        '_wpnonce' => wp_create_nonce('update-user_' . (string)$user->ID),
        'action' => 'update'
    );

    wp_enqueue_script('jquery');
    enqueue_jquery_cookie();
?>
<html>
    <head>
        <?php wp_head(); ?>
    </head>
    <body>
        <div style="width:100%; text-align:center; padding:50px;">
        <iframe id='toopher_iframe' style="display: inline-block;"  toopher_postback='<?php echo get_admin_url(get_current_blog_id(), 'profile.php') ?>' framework_post_args='<?php echo json_encode($toopher_finish_authenticate_parameters) ?>' toopher_req='<?php echo $signed_url ?>'></iframe>
        </div>
        <script>
<?php  include('toopher-web/toopher-web.js'); ?>
        </script>
<?php get_footer(); wp_footer(); ?>
    </body>
</html>
<?php
}

?>
