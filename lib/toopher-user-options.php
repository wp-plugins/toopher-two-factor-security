<?php

add_action('show_user_profile', 'toopher_user_options_menu_container');
add_action('edit_user_profile', 'toopher_edit_user_options_menu_container');
add_filter('user_profile_update_errors', 'toopher_record_updated_settings_for_later_application', 20, 3);

$toopherUserOptions = array(
    "t2s_authenticate_login" => array("Logging In", '1'),
    "t2s_authenticate_profile_update" => array("Updating my User Profile", '1')
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
            $userMeta = get_user_meta($uid, $key, true);
            if ($userMeta === ""){
                $userMeta = $toopherUserOptions[$key][1];
            }

            $toopherUserOptionVals[$key] = $userMeta;
        }
    }
}

function toopher_record_updated_settings_for_later_application($errors, $update, $user){
    if(isset($_POST['toopher_sig'])){
        // ignoring toopher authentication postback
        return;
    }
    global $toopherUserOptions;
    global $toopherUserOptionVals;
    $updatedToopherUserOptionVals = array();
    refresh_toopher_user_options($user->ID);
    foreach ($toopherUserOptions as $key => $val){
        $newVal = '0';
        if(isset($_REQUEST[$key])){
            $newVal = '1';
        }
        if($toopherUserOptionVals[$key] !== $newVal){
            $updatedToopherUserOptionVals[$key] = $newVal;
        }
    }
    set_transient($user->ID . '_pending_toopher_profile_settings', $updatedToopherUserOptionVals, 2 * MINUTE_IN_SECONDS);
}

function toopher_apply_updated_user_settings($user){
    $updatedToopherUserOptionVals = get_transient($user->ID . '_pending_toopher_profile_settings');
    delete_transient($user->ID . '_pending_toopher_profile_settings');
    if ($updatedToopherUserOptionVals){
        foreach ($updatedToopherUserOptionVals as $key => $val){
            update_user_meta((int)$user->ID, $key, $val);
            $toopherUserOptionVals[$key] = $val;
        }
    }
}

function toopher_user_options_menu_container($user){

    refresh_toopher_user_options($user->ID);
?>
<div class="wrap">
    <h3>Toopher Device Pairing</h3>
<?php
    toopher_user_options_menu($user);
    echo "<h3>Toopher User Authentication Options</h3>";
    toopher_edit_user_options_menu($user);
?>
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
function toopher_user_options_menu($user){

    $pairedWithToopher = get_user_meta((int)$user->ID, 't2s_user_paired', true);
    $pairingRowStyle = $pairedWithToopher ? 'display: none; ' : '';
    $unpairingRowStyle = $pairedWithToopher ? '' : 'display: none; ';
?>
<div class="wrap" style="width: 600px; ">
<style>
.toopher-button {
  background: #f3f3f3;
  background-image: -webkit-gradient(linear,left top,left bottom,from(#fefefe),to(#f4f4f4));
  background-image: -webkit-linear-gradient(top,#fefefe,#f4f4f4);
  background-image: -moz-linear-gradient(top,#fefefe,#f4f4f4);
  background-image: -o-linear-gradient(top,#fefefe,#f4f4f4);
  background-image: linear-gradient(to bottom,#fefefe,#f4f4f4);
  border: 1px #bbb solid;
  color: #333;
  text-shadow: 0 1px 0 #fff;
  display: inline-block;
  text-decoration: none;
  font-size: 12px;
  line-height: 23px;
  height: 24px;
  margin: 0;
  padding: 0 20px 1px;
  cursor: pointer;
  -webkit-border-radius: 3px;
  -webkit-appearance: none;
  border-radius: 3px;
  white-space: nowrap;
  -webkit-box-sizing: border-box;
  -moz-box-sizing: border-box;
  box-sizing: border-box;
}
.toopher-button-destructive {
  background-color: #b54c16;
  background-image: -webkit-gradient(linear,left top,left bottom,from(#d8510f),to(#b54c16));
  background-image: -webkit-linear-gradient(top,#d8510f,#b54c16);
  background-image: -moz-linear-gradient(top,#d8510f,#b54c16);
  background-image: -ms-linear-gradient(top,#d8510f,#b54c16);
  background-image: -o-linear-gradient(top,#d8510f,#b54c16);
  background-image: linear-gradient(to bottom,#d8510f,#b54c16);
  border-color: #935029;
  border-bottom-color: #1e6a8d:
  -webkit-box-shadow: inset 0 1px 0 rgba(228,134,87,0.9);
  box-shadow: inset 0 1px 0 rgba(228,134,87,0.9);
  color: #fff;
  text-decoration: none;
  text-shadow: 0 1px 0 rgba(0,0,0,0.1);
}

.toopher-button-destructive:hover {
  background-color: #b54c16;
  background-image: -webkit-gradient(linear,left top,left bottom,from(#ea621e),to(#b54c16));
  background-image: -webkit-linear-gradient(top,#ea621e,#b54c16);
  background-image: -moz-linear-gradient(top,#ea621e,#b54c16);
  background-image: -ms-linear-gradient(top,#ea621e,#b54c16);
  background-image: -o-linear-gradient(top,#ea621e,#b54c16);
  background-image: linear-gradient(to bottom,#ea621e,#b54c16);
  border-color: #935029;
  -webkit-box-shadow: inset 0 1px 0 rgb(228,134,87);
  box-shadow: inset 0 1px 0 rgb(228,134,87);
  color: #fff;
  text-shadow: 0 -1px 0 rgba(0,0,0,0.3);
}
</style>
  <div>
    <div class="description" style="width: 250px;" >Get the Toopher app on your smartphone to pair with your account</div>
  </div>
  <div style="margin-top: 12px; ">
    <a href="https://itunes.apple.com/us/app/toopher/id562592093?mt=8&uo=4" target="itunes_store">
      <img src="http://r.mzstatic.com/images/web/linkmaker/badge_appstore-lrg.gif" 
          alt="Toopher - Toopher" 
          style="border: 0; height: 40px;"/>
    </a>
    <a href="http://play.google.com/store/apps/details?id=com.toopher.android" style="margin-left:20px;">
      <img alt="Android app on Google Play"
          src="http://developer.android.com/images/brand/en_app_rgb_wo_45.png"
          style="height: 40px;" />
    </a>
  </div>
  <div style="margin-top:20px; color:#EEE; width: 300px;">
    <hr />
  </div>
  <div id="toopher_pairing" style="height: 220px; margin-top:12px; " >
    <div >

      <div class="toopher-show-when-unpaired" style="float: left; ">
        <img src='<?php echo(TOOPHER_PLUGIN_URL . '/media/iPhone-5-black.png'); ?>' />
      </div>
      <div class="toopher-show-when-unpaired" style="float: left; margin-top:90px; margin-left: 15px;">
        <img src='<?php echo(TOOPHER_PLUGIN_URL . '/media/Arrow.png'); ?>' />
      </div>

      <div style="height: 100%; width: 400px; float: left; margin-left: 15px;" >
        <div class="toopher-show-when-paired" style="display:none;" >
          <div style="margin-top: 10px;">
            <div>
              <span class="description">You have already paired Toopher with this account.  Click the button below to unpair your device.</span>
            </div>
            <div style="margin-top: 15px; margin-left: 80px;">
              <input type="button" class="toopher-pairing-toggle toopher-button toopher-button-destructive" value="Remove Pairing" />
            </div>
          </div>
        </div>

        <div class="toopher-show-when-unpaired" style="display: none;">
          <div class="toopher-hide-when-iframe-loaded" style="margin-top:50px;" >
            <span class="description">You have not paired your account with Toopher yet.</span>
          </div>
          <div  class="toopher-hide-when-iframe-loaded" style="margin-top: 15px; margin-left: 80px;">
            <input type="button" class="toopher-pairing-toggle button button-primary" value="Pair Now!" />
          </div>
        </div>

        <div class="toopher-show-while-iframe-loading" style="display: none; margin-top: 15px; margin-left: 80px; ">
          <img src='<?php echo(TOOPHER_PLUGIN_URL . '/media/preloader-bold.gif'); ?>' />
        </div>

        <div class="toopher-show-on-failure" style="display: none; ">
          <span class="error">There was an error contacting the Toopher API</span>
        </div>
        
        <div id="toopher_iframe_container" class="toopher-show-when-iframe-loaded" style="height:100%; width:100%; display:none; " />
      </div>
    </div>
  </div>
</div>
<script>
var toopherWebApi = <?php include('toopher-web/toopher-web.js'); ?>;
var toopherUserOptions = <?php include('toopher-user-options.js'); ?>;

toopherUserOptions.init(
    toopherWebApi,
    'toopher_pairing', 
    'toopher-pairing-toggle', 
    <?php echo($pairedWithToopher ? "'paired'" : "'unpaired'") ?>
);
</script>
<?php
}

function toopher_edit_user_options_menu($user){
    $uid = (int)$user->ID;
    $pairedWithToopher = get_user_meta($uid, 't2s_user_paired', true);
    global $toopherUserOptions;
    global $toopherUserOptionVals;
    $headerText = IS_PROFILE_PAGE ? 'my account' : 'this user';
    refresh_toopher_user_options($uid);

?>
    <table class="form-table">
        <tbody>
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
        </tbody>
    </table>
<?php
}

?>
