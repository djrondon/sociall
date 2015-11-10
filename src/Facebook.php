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
 * @subpackage Facebook
 * @author Grégory Saive <greg@evias.be>
 * @license http://www.apache.org/licenses/LICENSE-2.0
**/

namespace sociall;

require 'vendor/autoload.php';
require_once 'APIWrapper.php';

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;
use Facebook\FacebookRequest;
use Facebook\FacebookSDKException;

/**
 * The Facebook class can be used to authenticate users using
 * the Facebook PHP SDK (@see https://github.com/facebook/facebook-php-sdk-v4).
 *
 * @subpackage Facebook
 */
class Facebook
    extends APIWrapper
{
    /**
     * @var string  Facebook APP ID
     */
    protected $_app_id       = null;

    /**
     * @var string  Facebook APP Token
     */
    protected $_app_secret   = null;

    /**
     * @var string  Your Login callback URL. This URL
     *              is called by Facebook when the Authorization
     *              process is done.
     */
    protected $_login_callback_url = null;

    /**
     * The getLoginURL() method can be used to generate
     * the Facebook API login URL for your Facebook App.
     * This URL must be called to get permissions from the Facebook
     * account trying to login into your App.
     * This is the first URL that must be called for your App to be able
     * to retrieve Access tokens for logged in users.
     *
     * @return string
     */
    public function getLoginURL()
    {
        $callback_url = $this->getLoginCallbackURL();
        if (empty($callback_url))
            throw new Facebook_APICallError(
                "sociall\Facebook::getLoginURL() requires a login callback "
              . "URL to be set first.");

        $app_id    = $this->getAppId();
        $app_secret = $this->getAppSecret();

        FacebookSession::setDefaultApplication($app_id, $app_secret);

        $helper    = new FacebookRedirectLoginHelper($callback_url);
        $login_url = $helper->getLoginUrl(['public_profile', 'email']);
        return $login_url;
    }

    /**
     * The getProfile() method can be used [after permission inquiry]
     * to fetch data from Facebook about the currently logged in user.
     * Facebook's GraphUser class is used to retrieve data about the
     * user. (@see https://github.com/facebook/facebook-php-sdk-v4)
     * Before calling this method you MUST set a login callback URL
     * in order to be able to initialize the FacebookSession class
     * correctly.
     *
     * @throws Facebook_APICallError
     */
    public function getProfile()
    {
        $callback_url = $this->getLoginCallbackURL();
        if (empty($callback_url))
            throw new Facebook_APICallError(
                "sociall\Facebook::getLoginURL() requires a login callback "
              . "URL to be set first.");

        $app_id     = $this->getAppId();
        $app_secret = $this->getAppSecret();

        FacebookSession::setDefaultApplication($app_id, $app_secret);

        $helper     = new FacebookRedirectLoginHelper($callback_url);
        try {
            $sess   = $helper->getSessionFromRedirect();

            if (! $sess)
                throw new Facebook_APICallError(
                    "sociall\Facebook::getProfile() could not retrieve "
                  . "an active Facebook Session.");
        }
        catch(FacebookRequestException $ex) {
            // when Facebook returns an error
            throw new Facebook_APICallError(
                "sociall\Facebook::getProfile() produced an error "
              . "at Facebook : '" . $ex->getMessage() . "'.");
        }

        try {
            $request = new FacebookRequest($sess, 'GET', '/me');
            $profile = $request->execute()
                               ->getGraphObject(GraphUser::className());

            return $profile;
        }
        catch(FacebookRequestException $e) {
            // when Facebook returns an error
            throw new Facebook_APICallError(
                "sociall\Facebook::getProfile() produced an error "
              . "at Facebook : '" . $ex->getMessage() . "'.");
        }
    }

    /**
     * Setter method for the Facebook App ID field.
     * The option "app_id" can be specified in the Constructor's
     * options in order to call this method auto-magically.
     *
     * @param   string  $app_id     Facebook App ID
     * @return  sociall\Facebook
     */
    public function setAppId($app_id)
    {
        $this->_app_id = $app_id;
        return $this;
    }

    /**
     * Getter method for the Facebook App ID field.
     *
     * @return string
     */
    public function getAppId()
    {
        return $this->_app_id;
    }

    /**
     * Setter method for the Facebook App Secret field.
     * The option "app_secret" can be specified in the Constructor's
     * options in order to call this method auto-magically.
     *
     * @param   string  $app_secret     Facebook App Secret
     * @return  sociall\Facebook
     */
    public function setAppSecret($app_secret)
    {
        $this->_app_secret = $app_secret;
        return $this;
    }

    /**
     * Getter method for the Facebook App Token field.
     *
     * @return string
     */
    public function getAppSecret()
    {
        return $this->_app_secret;
    }

    /**
     * Setter method for the Login Callback URL field. This URL field
     * is the URL called from Facebook when the autorization process
     * is done.
     * The option "login_callback_url" can be specified in the Constructor's
     * options in order to call this method auto-magically.
     *
     * @param   string  $login_callback_url     Authorization Callback URL
     * @return  sociall\Facebook
     */
    public function setLoginCallbackURL($login_callback_url)
    {
        $this->_login_callback_url = $login_callback_url;
        return $this;
    }

    /**
     * Getter method for the Login Callback URL.
     *
     * @return string
     */
    public function getLoginCallbackURL()
    {
        return $this->_login_callback_url;
    }

}

/**
 * the Facebook_Exception class is a base class
 * for throwing Exceptions related to the Facebook
 * API requests.
 *
 * @subpackage Facebook Exception Handling
 */
class Facebook_Exception
    extends \Exception
{}

/**
 * the Facebook_APICallError is an Exception
 * class used when the Facebook API call you are trying
 * to fire is not yet authorized or when arguments to
 * an API call are missing.
 *
 * @subpackage Facebook Exception Handling
 */
class Facebook_APICallError
    extends Facebook_Exception
{}
