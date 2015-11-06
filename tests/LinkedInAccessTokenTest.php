<?php
namespace sociall\Tests;

require_once 'PHPUnit/Autoload.php';
require_once 'src/LinkedIn.php';

class LinkedInAccessTokenTest
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
     * @expectedException sociall\LinkedIn_EmptyResponseError
     */
    public function testEmptyResponse()
    {
        $token = $this->_client->getAccessToken();
    }

    /**
     * @expectedException sociall\LinkedIn_AuthorizationError
     */
    public function testResponseError()
    {
        $token = $this->_client->getAccessToken([
            'error' => 1,
            'error_description' => "This is the error message"]);
    }

    /**
     * @expectedException sociall\LinkedIn_AuthorizationError
     */
    public function testCodeError()
    {
        $token = $this->_client->getAccessToken([
            'everything' => 'but',
            'not' => 'code']);
    }
}
?>
