<?php

add_action('admin_menu', 'toopher_plugin_admin_menu');

function toopher_plugin_admin_menu() {
    add_options_page('Toopher Plugin Options', 'Toopher Authentication', 'manage_options', TOOPHER_PLUGIN_ID, 'toopher_plugin_admin_options');
}
function toopher_plugin_admin_options(){
    add_site_option('toopher_api_key', 'YOUR TOOPHER API KEY');
    add_site_option('toopher_api_secret', 'YOUR TOOPHER API SECRET');
    add_site_option('toopher_api_url', 'https://api.toopher.com/v1/');

    if(isset($_POST['update_settings'])){
        update_site_option('toopher_api_key', $_POST['toopher_api_key']);
        update_site_option('toopher_api_secret', $_POST['toopher_api_secret']);
        update_site_option('toopher_api_url', $_POST['toopher_api_url']);
        echo '<div id="message" class="updated">Settings saved</div> ';
    }
?>
    <div class="wrap">
        <h2>Toopher Authentication Admin Settings</h2>
        <form method='post' action=''>
            <table>
            <tr><td><label for='toopher_api_key'>Toopher API Key</label></td><td><input type='text' name='toopher_api_key' value='<?php echo get_site_option('toopher_api_key'); ?>'/></td></tr>
            <tr><td><label for='toopher_api_secret'>Toopher API Secret</label></td><td><input type='text' name='toopher_api_secret' value='<?php echo get_site_option('toopher_api_secret'); ?>'/></td></tr>
            <tr><td><label for='toopher_api_url'>Toopher API URL</label></td><td><input type='text' name='toopher_api_url' value='<?php echo get_site_option('toopher_api_url'); ?>'/></td></tr>
            </table>
            <input type='hidden' name='update_settings' value='Y' />
            <input type='submit' value='Save Settings' class='button-primary' />
        </form>
    </div>
<?php
}

?>
