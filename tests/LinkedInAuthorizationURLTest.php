<?php
namespace sociall\Tests;

require_once 'PHPUnit/Autoload.php';
require_once 'src/LinkedIn.php';

class LinkedInAuthorizationURLTest
    extends \PHPUnit_Framework_TestCase
{
    private $_client       = null;
    private $_app_id       = "LinkedIn_App_Id";
    private $_app_secret   = "LinkedIn_App_Secret";
    private $_callback_url = "http://example.com/auth/callback";

    public function __construct()
    {
        $this->_client = new \sociall\LinkedIn([
            "app_id"     => $this->_app_id,
            "app_secret" => $this->_app_secret,
            "auth_callback_url" => $this->_callback_url]);
    }

    public function testURLValidity()
    {
        $auth_url = $this->_client->getAuthorizationURL();
        $filtered = filter_var($auth_url, FILTER_VALIDATE_URL);

        $this->assertFalse(empty($filtered));
    }

    public function testURLGeneration()
    {
        $auth_url = $this->_client->getAuthorizationURL();

        $host     = parse_url($auth_url, PHP_URL_HOST);
        $path     = parse_url($auth_url, PHP_URL_PATH);

        $this->assertEquals($host, "www.linkedin.com");
        $this->assertEquals($path, "/uas/oauth2/authorization");

        // check for LinkedIn's mandatory parameters presence
        $query    = [];
        parse_str(parse_url($auth_url, PHP_URL_QUERY), $query);
        $this->assertEquals($query['response_type'], "code");
        $this->assertEquals($query['client_id'], $this->_client->getAppId());
        $this->assertEquals($query['redirect_uri'], $this->_client->getAuthCallbackURL());
        $this->assertEquals($query['state'], $this->_client->getLastCSRF());
    }

    public function testCSRFRandomness()
    {
        $client1 = new \sociall\LinkedIn([
            "app_id"     => $this->_app_id,
            "app_secret" => $this->_app_secret,
            "auth_callback_url" => $this->_callback_url]);

        $client2 = new \sociall\LinkedIn([
            "app_id"     => $this->_app_id,
            "app_secret" => $this->_app_secret,
            "auth_callback_url" => $this->_callback_url]);

        // even if both clients use the same App ID and secret,
        // the CSRF token generated should be unique!

        $url1 = $client1->getAuthorizationURL();
        $url2 = $client2->getAuthorizationURL();

        $this->assertNotEquals($client1->getLastCSRF(), $client2->getLastCSRF());
    }
}
?>
