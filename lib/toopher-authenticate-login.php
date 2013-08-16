<?php

add_filter('authenticate', 'toopher_begin_authenticate_login', 100, 1);
add_filter('authenticate', 'toopher_finish_authenticate_login', 0, 1);

/**
 * run last in the authenticate chain.  If user has passed previous auth, see if they
 * need to be toopher-authenticated and handle it.  Else is a no-op.
 **/

function toopher_begin_authenticate_login($user){
    error_log('toopher_begin_authenticate_login');
    if (is_a($user, 'WP_User')){
        if ((get_user_meta((int)$user->ID, 't2s_user_paired', true)) && (get_user_meta((int)$user->ID, 't2s_authenticate_login', true))){
            if(isset($_POST['toopher_authentication_successful']) && ($_POST['toopher_authentication_successful'] === 'true')){
                return $user;
            } else {
                error_log('user should be toopher-authenticated');
                if(defined('XMLRPC_REQUEST') && XMLRPC_REQUEST){
                    error_log('toopher-authenticating XML-RPC login');
                    error_log('cookies: ' . var_export($_COOKIE, true));
                    setcookie('wp-xmlrpc-t2s-terminal-id', 'test');
                    require_once('toopher_api.php');
                    $api = new ToopherAPI(get_option('toopher_api_key'), get_option('toopher_api_secret'), get_option('toopher_api_url'));
                    $authStatus = $api->authenticate(get_user_meta((int)$user->ID, 't2s_pairing_id', true), "my blog");
                    while($authStatus['pending']){
                        $authStatus = $api->getAuthenticationStatus($authStatus['id']);
                    }
                    if(!$authStatus['granted']){
                        $user = new WP_Error('Toopher Authentication Failure', __('Unable to authenticate user through Toopher API'));
                    }
                } else {
                    error_log('beginning toopher interactive login');
                    toopher_login_pending($user);
                    exit();
                }
            }
        } else {
            error_log('user does not have toopher protection enabled');
            error_log('t2s_user_paired is ' + (string)get_user_meta((int)$user->ID, 't2s_user_paired'));
        }
    } else {
        error_log('not a WP_User');
    }

    return $user;
}

function toopher_finish_authenticate_login($user){
    // make sure someone isn't trying to circumvent toopher-auth by submitting the authentication success flag through the browser
    if(isset($_POST['toopher_authentication_successful'])){
        unset($_POST['toopher_authentication_successful']);
    }

    if(isset($_POST['toopher_sig'])){
        $pending_user_id = $_POST['pending_user_id'];
        $redirect_to = $_POST['redirect_to'];
        unset($_POST['pending_user_id']);
        unset($_POST['redirect_to']);
        $secret = get_site_option('toopher_api_secret');
        foreach(array('terminal_name', 'reason') as $toopher_key){
            $_POST[$toopher_key] = strip_wp_magic_quotes($_POST[$toopher_key]);
        }

        $pending_session_token = get_transient($pending_user_id . '_t2s_authentication_session_token');
        delete_transient($pending_user_id . '_t2s_authentication_session_token');
        if(($pending_session_token === $_POST['session_token']) && ToopherWeb::validate($secret, $_POST, 100)){
            error_log('toopher signature validates');
            $authGranted = ($_POST['pending'] === 'false') && ($_POST['granted'] === 'true');
            if($authGranted){
                error_log('auth granted');
                $user = get_user_by('id', $pending_user_id);
                $_POST['redirect_to'] = $redirect_to;
            }
            $_POST['toopher_authentication_successful'] = $authGranted ? 'true' : 'false';
        } else {
            error_log('toopher signature does not validate!');
            $user = new WP_Error('Toopher Authentication Failure', __('Toopher API Signature did not match expected value'));
        }
    }
    return $user;
}

function toopher_login_pending($user){
    $key = get_site_option('toopher_api_key');
    $secret = get_site_option('toopher_api_secret');
    $baseUrl = get_site_option('toopher_api_url');
    $automatedLoginAllowed = get_site_option('toopher_allow_automated_login', true);
    $session_token = wp_generate_password(12, false);
    set_transient($user->ID . '_t2s_authentication_session_token', $session_token, 2 * MINUTE_IN_SECONDS);
    $signed_url = ToopherWeb::auth_iframe_url($user->user_login, 'Log In', 100, $automatedLoginAllowed, $baseUrl, $key, $secret, $session_token);

    $toopher_finish_authenticate_parameters = array(
        'pending_user_id' => $user->ID,
        'redirect_to' => $_POST['redirect_to']
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
        <iframe id='toopher_iframe' style="display: inline-block;"  toopher_postback='<?php echo wp_login_url() ?>' framework_post_args='<?php echo json_encode($toopher_finish_authenticate_parameters) ?>' toopher_req='<?php echo $signed_url ?>'></iframe>
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