<?php
/**
 * LICENSE
 *
 Copyright 2015 Grégory Saive (greg@evias.be)

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
 *
 * @package Sociall
 * @subpackage Tests
 * @author Grégory Saive <greg@evias.be>
 * @license http://www.apache.org/licenses/LICENSE-2.0
**/

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
