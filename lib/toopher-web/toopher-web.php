<?php
require_once('OAuth.php');

class ToopherWeb
{
    public static function pair_iframe_url($username, $reset_email, $ttl, $baseUrl, $key, $secret)
    {
        $params = array(
          'username' => $username,
          'reset_email' => $reset_email
        );

        return ToopherWeb::getOAuthUrl($baseUrl . 'web/pair', $params, $ttl, $key, $secret);
    }
    public static function auth_iframe_url($username, $reset_email, $action, $ttl, $automation_allowed, $baseUrl, $key, $secret, $session_token, $extras = array())
    {
        $params = array(
            'username' => $username,
            'action_name' => $action,
            'automation_allowed' => $automation_allowed ? 'True' : 'False',
            'reset_email' => $reset_email
        );
        if ($extras) {
            $params['requester_metadata'] = base64_encode(json_encode($extras));
        }
        return ToopherWeb::getOAuthUrl($baseUrl . 'web/auth', $params, $ttl, $key, $secret, $session_token);
    }

    public static function validate($secret, $data, $ttl=100)
    {
        $maybe_sig = $data['toopher_sig'];
        unset($data['toopher_sig']);
        $computed_sig = ToopherWeb::signature($secret, $data);
        $signature_valid = $computed_sig === $maybe_sig;
        $ttl_valid = (time() - $ttl) < (int)$data['timestamp'];
        if ($signature_valid && $ttl_valid) {
          return $data;
        } else {
          return false;
        }
    }

    public static function signature($secret, $data)
    {
        ksort($data);
        $to_sign = http_build_query($data);
        $result = base64_encode(hash_hmac('sha1', $to_sign, $secret, true));
        return $result;
    }

    private static function getOAuthUrl($url, $getParams, $ttl, $key, $secret, $session_token=Null)
    {
        $getParams['v'] = '2';
        $expiresAt = (time() + $ttl);
        $getParams['expires'] = (string)$expiresAt;
        if ($session_token){
            $getParams['session_token'] = $session_token;
        }
        $oauth = new OAuthConsumer($key, $secret);
        $req = OAuthRequest::from_consumer_and_token($oauth, NULL, 'GET', $url, $getParams);
        $req->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $oauth, null);
        return $req->to_url();
    }
}

?>
