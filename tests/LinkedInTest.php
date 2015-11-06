<?php
namespace sociall\Tests;

require_once 'PHPUnit/Autoload.php';
require_once 'src/LinkedIn.php';

class LinkedInTest
    extends \PHPUnit_Framework_TestCase
{
    private $_app_id       = "LinkedIn_App_Id";
    private $_app_secret   = "LinkedIn_App_Secret";
    private $_callback_url = "http://example.com/auth/callback";

    public function testArrayInitialization()
    {
        $client = new \sociall\LinkedIn([
            "app_id"     => $this->_app_id,
            "app_secret" => $this->_app_secret,
            "auth_callback_url" => $this->_callback_url]);

        $this->assertEquals($client->getAppId(), $this->_app_id);
        $this->assertEquals($client->getAppSecret(), $this->_app_secret);
        $this->assertEquals($client->getAuthCallbackURL(), $this->_callback_url);
    }

    public function testSetterInitialization()
    {
        $client = new \sociall\LinkedIn();
        $client->setAppId($this->_app_id);
        $client->setAppSecret($this->_app_secret);
        $client->setAuthCallbackURL($this->_callback_url);

        $this->assertEquals($client->getAppId(), $this->_app_id);
        $this->assertEquals($client->getAppSecret(), $this->_app_secret);
        $this->assertEquals($client->getAuthCallbackURL(), $this->_callback_url);
    }

    public function testChainInitialization()
    {
        $client = new \sociall\LinkedIn();
        $client->setAppId($this->_app_id)
               ->setAppSecret($this->_app_secret)
               ->setAuthCallbackURL($this->_callback_url);

        $this->assertEquals($client->getAppId(), $this->_app_id);
        $this->assertEquals($client->getAppSecret(), $this->_app_secret);
        $this->assertEquals($client->getAuthCallbackURL(), $this->_callback_url);
    }
}
?>
