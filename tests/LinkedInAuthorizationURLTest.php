<?php
namespace sociall\Tests;

require_once 'PHPUnit/Autoload.php';
require_once 'src/LinkedIn.php';

class LinkedInAuthorizationURLTest
    extends \PHPUnit_Framework_TestCase
{
    private $_app_id       = "LinkedIn_App_Id";
    private $_app_secret   = "LinkedIn_App_Secret";
    private $_callback_url = "http://example.com/auth/callback";

    public function testURLValidity()
    {
        $client = new \sociall\LinkedIn([
            "app_id"     => $this->_app_id,
            "app_secret" => $this->_app_secret,
            "auth_callback_url" => $this->_callback_url]);

        $auth_url = $client->getAuthorizationURL();
        $filtered = filter_var($auth_url, FILTER_VALIDATE_URL);

        $this->assertFalse(empty($filtered));
    }
}
?>
