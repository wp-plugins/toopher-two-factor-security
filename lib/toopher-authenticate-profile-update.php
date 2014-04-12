<?php

add_filter('user_profile_update_errors', 'toopher_begin_authorize_profile_update', 100, 3);
add_filter('user_profile_update_errors', 'toopher_finish_authorize_profile_update', 0, 3);

$toopherProfileUpdateRecursionGuard = 0;

function toopherProfileGuardEnter() {
    global $toopherProfileUpdateRecursionGuard;
    if($toopherProfileUpdateRecursionGuard !== 0){
        return false;
    }
    $toopherProfileUpdateRecursionGuard += 1;
    return true;
}

function toopherProfileGuardExit() {
    global $toopherProfileUpdateRecursionGuard;
    $toopherProfileUpdateRecursionGuard -= 1;
    if ($toopherProfileUpdateRecursionGuard !== 0) {
        error_log('recursive call detected to non-reentrant function: toopher-authenticate-profile');
    }
}

function toopher_begin_authorize_profile_update($errors, $update, $user){
    if (!toopherProfileGuardEnter()) {
        return;
    }
    $cur_user = wp_get_current_user();
    if ($errors->get_error_codes()){
        // no-op
    } elseif (!$update) {
        // no-op
    } elseif(isset($_POST['toopher_authentication_successful']) && ($_POST['toopher_authentication_successful'] === 'true')){
        // no-op
    } elseif (get_user_option('t2s_authenticate_profile_update', (int)$cur_user->ID)){
        toopher_profile_update_pending($user, $cur_user);
        exit();
    } else {
        toopher_apply_updated_user_settings($user);
    }
    toopherProfileGuardExit();

}

function toopher_finish_authorize_profile_update($errors, $update, $user){
    if (!toopherProfileGuardEnter()) {
        return;
    }
    // make sure someone isn't trying to circumvent toopher-auth by submitting the authentication success flag through the browser
    if(isset($_POST['toopher_authentication_successful'])){
        unset($_POST['toopher_authentication_successful']);
    }

    if(isset($_POST['toopher_sig'])){
        $pending_user_id = $_POST['pending_user_id'];
        unset($_POST['pending_user_id']);
        $secret = get_option ('toopher_api_secret');
        foreach(array('terminal_name', 'reason') as $toopher_key){
            if (array_key_exists($toopher_key, $_POST)) {
                $_POST[$toopher_key] = strip_wp_magic_quotes($_POST[$toopher_key]);
            }
        }

        $pending_session_token = get_transient($pending_user_id . '_t2s_authentication_session_token');
        delete_transient($pending_user_id . '_t2s_authentication_session_token');
        $pending_updated_user = get_transient($pending_user_id . '_t2s_pending_profile_update_data');
        delete_transient($pending_user_id . '_t2s_pending_profile_update_data');
        $toopherSigData = $_POST;
        unset($toopherSigData['_wpnonce']);
        unset($toopherSigData['action']);

        if(($pending_session_token === $_POST['session_token']) && ToopherWeb::validate($secret, $toopherSigData, 100)){
            $authGranted = false;
            if (array_key_exists('error_code', $_POST)){
                $error_code = $_POST['error_code'];
                $error_message = $_POST['error_message'];

                # three specific errors will be allowed to fail open, corresponding to allowing users
                # to opt-in to Toopher (instead of requiring all users to participate)
                if ($error_code === '707') { # pairing deactivated - allow in
                    $authGranted = true;
                } elseif ($error_code === '704') { # user opt-out - allow in
                    $authGranted = true;
                } elseif ($error_code === '705') { # unknown user - allow in
                    $authGranted = true;
                }
            } else {
                $authGranted = ($_POST['pending'] === 'false') && ($_POST['granted'] === 'true');
            }

            $errors->errors = array();
            if($authGranted){
                $user = $pending_updated_user;
                toopher_apply_updated_user_settings($user);
                $_POST['toopher_authentication_successful'] = 'true';
            } else {
                $errors->errors = array();
                $errors->add('toopher_auth_fail', __('<strong>Error</strong>: Toopher Two-Factor security prevented the attempt to update user settings.'));
                $_POST['toopher_authentication_successful'] = 'false';
            }

        } else {
            $errors->errors = array();
            $errors->add('toopher_auth_invalid', __('<strong>Error</strong>: Toopher API Signature did not match expected value!'));
            $_POST['toopher_authentication_successful'] = 'false';
        }
    }
    toopherProfileGuardExit();
    return;
}

function toopher_profile_update_pending($user, $cur_user){
    $key = get_option('toopher_api_key');
    $secret = get_option('toopher_api_secret');
    $baseUrl = get_option('toopher_api_url');
    $automationAllowed = false;
    $session_token = wp_generate_password(12, false);
    set_transient($user->ID . '_t2s_authentication_session_token', $session_token, 2 * MINUTE_IN_SECONDS);
    set_transient($user->ID . '_t2s_pending_profile_update_data', $user, 2 * MINUTE_IN_SECONDS);
    $postbackUrl = '';
    $actionName = '';
    if (IS_PROFILE_PAGE) {
        $postbackUrl = get_edit_profile_url($user->ID);
        $actionName = 'Update your profile';
    } else {
        $postbackUrl = get_edit_user_link($user->ID);
        $actionName = 'Edit profile for ' . $user->user_login;
    }
    if ($_GET) {
      $postbackUrl = $postbackUrl . '?' . http_build_query($_GET);
    }
    $signed_url = ToopherWeb::auth_iframe_url($cur_user->user_login, $cur_user->user_email, $actionName, 100, $automationAllowed, $baseUrl, $key, $secret, $session_token);
    $toopher_finish_authenticate_parameters = array(
        'pending_user_id' => $user->ID,
        '_wpnonce' => wp_create_nonce('update-user_' . (string)$user->ID),
        'action' => 'update'
    );

    wp_enqueue_script('jquery');
?>
<html>
    <head>
        <?php wp_head(); ?>
    </head>
    <body>
        <div style="width:80%; height:300 px; text-align:center; margin-left:auto; margin-right:auto;">
        <iframe id='toopher_iframe' style="display: inline-block; height: 100%; width: 100%;"  toopher_postback='<?php echo $postbackUrl ?>' framework_post_args='<?php echo json_encode($toopher_finish_authenticate_parameters) ?>' toopher_req='<?php echo $signed_url ?>'></iframe>
        </div>
        <script>
<?php  include('jquery.cookie.min.js'); ?>
<?php  include('toopher-web/toopher-web.js'); ?>

    toopher.init('#toopher_iframe');
    
        </script>
<?php get_footer(); wp_footer(); ?>
    </body>
</html>
<?php
}

?>
