<?php
require_once('toopher-web/toopher-web.php');



function toopherGetPairUrlForCurrentUser() {
    $key = get_option('toopher_api_key');
    $secret = get_option('toopher_api_secret');
    $baseUrl = get_option('toopher_api_url');
    $user = wp_get_current_user();
    
    if (get_user_meta((int)$user->ID, 't2s_user_paired', true)){
        $url = ToopherWeb::unpair_iframe_url($user->data->user_login, 60, $baseUrl, $key, $secret);
    } else {
        $url = ToopherWeb::pair_iframe_url($user->data->user_login, 60, $baseUrl, $key, $secret);
    }
    $framework_post_args = '{"action":"toopher_update_pairing"}';
    echo json_encode(array('toopher_req'=> $url, 'framework_post_args'=>$framework_post_args));

    die();  // wordpress sucks.
}

function toopherUpdatePairing() {
    error_log('checking signature');
    $secret = get_option('toopher_api_secret');
    unset($_POST['action']);
    if (ToopherWeb::validate($secret, $_POST)){
        $pairingEnabled = $_POST['enabled'] === 'true';
        $pairingPending = $_POST['pending'] === 'true';
        $toopherPairingId = $_POST['id'];
        $toopherUserId = $_POST['user_id'];
        $user = wp_get_current_user();
        update_user_meta((int)$user->ID, 't2s_user_paired', $pairingEnabled && (!$pairingPending));
        update_user_meta((int)$user->ID, 't2s_pairing_id', $toopherPairingId);
        echo json_encode(array('paired' => $pairingEnabled && (!$pairingPending)));
    }
    die();
}

add_action('wp_ajax_toopher_get_pair_url_for_current_user', 'toopherGetPairUrlForCurrentUser');
add_action('wp_ajax_toopher_update_pairing', 'toopherUpdatePairing');
error_log('added ajax handler');
//add_action('all', create_function('', 'error_log(current_filter());'));
?>
