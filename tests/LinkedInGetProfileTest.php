<?php
namespace sociall\Tests;

require_once 'PHPUnit/Autoload.php';
require_once 'src/LinkedIn.php';

class LinkedInGetProfileTest
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

    /**
     * @expectedException sociall\LinkedIn_APICallError
     */
    public function testNoAccessTokenResponse()
    {
        $profile = $this->_client->getProfile();
    }
}
?>
