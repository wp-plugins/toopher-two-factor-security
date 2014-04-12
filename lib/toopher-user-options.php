<?php

add_action('show_user_profile', 'toopher_user_profile_page');
add_action('edit_user_profile', 'toopher_user_profile_page');
add_filter('user_profile_update_errors', 'toopher_record_updated_settings_for_later_application', 20, 3);

$toopherUserOptions = array(
    "t2s_authenticate_login" => array("Logging In", '1'),
    "t2s_authenticate_profile_update" => array("Updating a User Profile", '1')
);
$toopherUserOptionVals = array();

$refreshToopherUserOptionsCalled = false;
function refresh_toopher_user_options($uid){
    global $toopherUserOptions;
    global $toopherUserOptionVals;
    global $refreshToopherUserOptionsCalled;
    if(!$refreshToopherUserOptionsCalled){
        $refreshToopherUserOptionsCalled = true;
        foreach($toopherUserOptions as $key => $val){
            $userMeta = get_user_option($key, $uid);
            if ($userMeta === false){
                // user has no setting for toopher options.  Set the default.
                $userMeta = $toopherUserOptions[$key][1];
                update_user_option($uid, $key, $userMeta);
            }

            $toopherUserOptionVals[$key] = $userMeta;
        }
    }
}

function toopher_record_updated_settings_for_later_application($errors, $update, $user){
    // only want to run if we're updating an existing user, not adding a new one
    if (!$update){
        return;
    }
    if(isset($_POST['toopher_sig'])){
        // ignoring toopher authentication postback
        return;
    }

    $savedPOST = $_POST;
    set_transient($user->ID . '_pending_user_profile_POST', $savedPOST, 2 * MINUTE_IN_SECONDS);
}

function toopher_apply_updated_user_settings($user){
    global $toopherUserOptions;
    $savedPOST = get_transient($user->ID . '_pending_user_profile_POST');
    delete_transient($user->ID . '_pending_user_profile_POST');
    if ($savedPOST) {
        $realPOST = $_POST;
        $_POST = $savedPOST;
        edit_user($user->ID);
        $_POST = $realPOST;
        foreach ($toopherUserOptions as $key => $val) {
            if (array_key_exists($key, $savedPOST)) {
                update_user_option((int)$user->ID, $key, '1');
            } else {
                update_user_option((int)$user->ID, $key, '0');
            }
        }
    }
}

function toopher_user_profile_page($user) {
?>
<div class="wrap">
    <h3>Toopher Two-Factor Authentication</h3>
    <table class="form-table">
        <tbody>
<?php
  if (IS_PROFILE_PAGE) {
    toopher_pairing_iframe_row($user);
  }
  toopher_user_options_row($user);
?>
        </tbody>
    </table>
</div>
<?php
}

function toopher_edit_user_options_menu_container($user){
?>
<div class="wrap">
    <h3>Toopher User Authentication Options</h3>
<?php
    toopher_edit_user_options_menu($user);
?>
</div>
<?php
}
function toopher_pairing_iframe_row($user){
    $key = get_option('toopher_api_key');
    $secret = get_option('toopher_api_secret');
    $baseUrl = get_option('toopher_api_url');
    $toopherPairingIframeSrc = ToopherWeb::pair_iframe_url($user->data->user_login, $user->data->user_email, 60, $baseUrl, $key, $secret);


?>
<tr>
    <th>Toopher Device Pairing</th>
    <td>
        <div class="wrap" style="width: 100%; height:300px;">
            <iframe id="toopher-iframe" style="height:100%; width:100%;" src='<?php echo($toopherPairingIframeSrc); ?>' ></iframe>
        </div>
    </td>
</tr>
<?php
}

function toopher_user_options_row($user){
    $uid = (int)$user->ID;
    global $toopherUserOptions;
    global $toopherUserOptionVals;
    $headerText = IS_PROFILE_PAGE ? 'my account' : 'this user';
    refresh_toopher_user_options($uid);

?>
        <tr>
        <th>Require Toopher Authentication for <?php echo $headerText ?> when:</th>
            <td>
<?php   foreach($toopherUserOptions as $key => $val){
            echo "<label for='" . $key . "'>";
            $checkedText = $toopherUserOptionVals[$key] ? "checked='checked'" : "";
            echo "<input type='checkbox' name='" . $key . "' id='" . $key . "' " . $checkedText . " />";
            echo "  " . $val[0] . "</label>";
            echo "<br />";
        }
?>
            </td>
        </tr>
<?php
}

?>
