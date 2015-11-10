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
 * @subpackage LinkedIn
 * @author Grégory Saive <greg@evias.be>
 * @license http://www.apache.org/licenses/LICENSE-2.0
**/

namespace sociall;

require_once 'APIWrapper.php';

/**
 * The LinkedIn class can be used to authenticate users using
 * the LinkedIn API (@see https://developer.linkedin.com/docs/oauth2).
 *
 * @example
 * $client   = new LinkedIn([
 *     'app_id' => "MyAppID",
 *     'app_secret' => "MyAppSecret",
 *     'auth_callback_url' => "http://example.com/auth/callback"]);
 *
 * // ... to be inserted in the login page (on submit)
 * $auth_url = $client->getAuthorizationURL();
 * header("Location: {$auth_url}");
 * exit;
 *
 * // ... insert this in callback action (in this example: /auth/callback)
 * $access_token  = $client->getAccessToken($_GET);
 * $linkedin_user = $client->getProfile();
 * echo "Hello " . $linkedin_user->firstName, PHP_EOL;
 *
 * @subpackage LinkedIn
 */
class LinkedIn
    extends APIWrapper
{
    /**
     * @var string  LinkedIn APP ID
     */
    protected $_app_id       = null;

    /**
     * @var string  LinkedIn APP Secret
     */
    protected $_app_secret   = null;

    /**
     * @var string  LinkedIn Access Token (60 days validity)
     */
    protected $_access_token = null;

    /**
     * @var string  Your Authorization callback URL. This URL
     *              is called by linkedIn when the Authorization
     *              process is done. (OAuth 2.0 Step 1)
     */
    protected $_auth_callback_url = null;

    /**
     * @var string  Last generated CSRF token
     */
    protected $_last_csrf = null;

    /**
     * The getAuthorizationURL() method can be used to generate
     * the LinkedIn OAuth 2.0 authorization URL for your LinkedIn App.
     * This URL must be called to get permissions from the LinkedIn
     * account trying to login into your App.
     * This is the first URL that must be called for your App to be able
     * to retrieve Access tokens for logged in users.
     *
     * @return string
     */
    public function getAuthorizationURL()
    {
        $app_id     = $this->getAppId();
        $app_secret = $this->getAppSecret();

        // create CSRF token. (security for Cross Site Request Forgery)
        $random     = bin2hex(openssl_random_pseudo_bytes(16));
        $csrf_token = sha1(sprintf("%s-%s-%s-%d",
                            $app_secret, $app_id, $random, time()));

        $this->_last_csrf = $csrf_token;

        $url_params = [
          "response_type" => "code",
          "client_id"     => $app_id,
          "redirect_uri"  => $this->getAuthCallbackURL(),
          "state"         => $csrf_token,
        ];
        $url = sprintf("https://www.linkedin.com/uas/oauth2/authorization?%s",
                        http_build_query($url_params));
        return $url;
    }

    /**
     * Setter method for the LinkedIn Access Token field.
     * LinkedIn Access Tokens are valid for 60 days and MUST
     * be used when querying any data from the LinkedIn API.
     * This method can be used to bypass the getAccessToken
     * cURL request to LinkedIn in case you already have a
     * valid access token which you wish to use.
     *
     * @param   string  $access_token     LinkedIn Access Token
     * @return  sociall\LinkedIn
     */
    public function setAccessToken($access_token)
    {
        $this->_access_token = $access_token;
        return $this;
    }

    /**
     * The getAccessToken() method can be used to retrieve a valid LinkedIn
     * access token for your future LinkedIn API requests.
     * This method can be called with a response array corresponding to
     * the LinkedIn Authorization request's response details being:
     * The 'code' key for the LinkedIn authorization code and the 'state'
     * key for the CSRF token used in the authorization request.
     * Calling the getAccessToken() when no access token is set yet will
     * trigger an access token retrieval cURL request to the LinkedIn API
     * server. This is Step 3 in the LinkedIn OAuth 2.0 API workflow.
     *
     * @param   array   $auth_response  Mandatory fields are 'code' and 'state'.
     *                                  If LinkedIn encountered an error and you
     *                                  pass $_GET directly to this function from
     *                                  your callback URL, this method will throw
     *                                  an exception LinkedIn_AuthorizationError.
     * @return  string
     * @throws  LinkedIn_EmptyResponseError     On missing response & access_token
     * @throws  LinkedIn_AuthorizationError     On LinkedIn API error
     */
    public function getAccessToken(array $auth_response = [])
    {
        if (empty($auth_response) && $_GET)
            $auth_response = $_GET;

        // Error handling
        if (empty($auth_response) && empty($this->_access_token))
            throw new LinkedIn_EmptyResponseError(
                "Response Array needed in sociall\LinkedIn::getAccessToken().");
        elseif (!empty($this->_access_token))
            // Access token already present.
            return $this->_access_token;

        if (!empty($auth_response['error']))
            throw new LinkedIn_AuthorizationError(
                "LinkedIn API Error: " . urldecode($auth_response['error_description']));

        if (empty($auth_response['code']))
            throw new LinkedIn_AuthorizationError(
                "LinkedIn API Error: Invalid auth_response provided, missing "
              . "authorization code from LinkedIn.");

        if ($auth_response['state'] != $this->getLastCSRF())
            throw new LinkedIn_AuthorizationError(
                "LinkedIn API Error: Invalid CSRF token provided.");

        // Now we can query for an Access Token.
        // OAuth 2.0 Step 3: translation of the authorization code
        // we got from LinkedIn into a LinkedIn API valid access token.

        $auth_code  = $auth_response['code'];
        $handle     = curl_init();

        $url_params = [
            "grant_type"    => "authorization_code",
            "code"          => $auth_code,
            "redirect_uri"  => $this->getAuthCallbackURL(),
            "client_id"     => $this->getAppId(),
            "client_secret" => $this->getAppSecret()
        ];

        // configure our cURL request with mandatory parameters :
        // grant_type, code, redirect_uri, client_id & client_secret
        curl_setopt_array($handle, [
            CURLOPT_URL     => "https://www.linkedin.com/uas/oauth2/accessToken",
            CURLOPT_POST    => true,
            CURLOPT_POSTFIELDS  => http_build_query($url_params),
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $json_response = curl_exec($handle);
        curl_close($handle);
        $response = json_decode($json_response);

        if (empty($response->access_token))
            throw new LinkedIn_AuthorizationError(
                "LinkedIn API Error: No access token received.");

        $this->_access_token = $response->access_token;
        return $this->_access_token;
    }

    /**
     * The getProfile() method can be used [after access token retrieval]
     * to fetch data from LinkedIn about the currently logged in user.
     * Fields returned by this method include:
     * - id                 : LinkedIn profile ID
     * - numConnections     : user's count of connections
     * - firstName          : user's first name
     * - lastName           : user's last name
     * - emailAddress       : user's active email address for LinkedIn
     * - headLine           : LinkedIn profile headline
     * - publicProfileUrl   : LinkedIn public profile URL
     *
     * @return stdClass     User object with fields described above.
     * @throws LinkedIn_APICallError
     */
    public function getProfile()
    {
        // No access token ? You missed at least one step.
        if (empty($this->_access_token))
            throw new LinkedIn_APICallError(
                "sociall\LinkedIn::getProfile() requires an access "
              . "token to be retrieved first.");

        // This time we will need to include an Authorization HTTP header
        // (see LinkedIn OAuth 2.0 implementation details).
        $handle  = curl_init();
        $headers = ['Authorization: Bearer ' . $this->getAccessToken()];

        // define which fields we want to retrieve. In order to do so
        // we must simply generate a scope that we will pass to the LinkedIN
        // people API URL.
        $fields  = [
            "id",
            "num-connections",
            "first-name",
            "last-name",
            "email-address",
            "headline",
            "public-profile-url"
        ];
        $scope   = "(" . implode(",", $fields) . ")";
        curl_setopt_array($handle, [
            CURLOPT_URL     => "https://api.linkedin.com/v1/people/~:" . $scope . "?format=json",
            CURLOPT_HTTPHEADER  => $headers,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        // do the magic and return an stdClass object populated with
        // the LinkedIn account data.
        $api_response = curl_exec($handle);
        curl_close($handle);
        $data = json_decode($api_response);
        return $data;
    }

    /**
     * Setter method for the LinkedIn App ID field.
     * The option "app_id" can be specified in the Constructor's
     * options in order to call this method auto-magically.
     *
     * @param   string  $app_id     LinkedIn App ID
     * @return  sociall\LinkedIn
     */
    public function setAppId($app_id)
    {
        $this->_app_id = $app_id;
        return $this;
    }

    /**
     * Getter method for the LinkedIn App ID field.
     *
     * @return string
     */
    public function getAppId()
    {
        return $this->_app_id;
    }

    /**
     * Setter method for the LinkedIn App Secret field.
     * The option "app_secret" can be specified in the Constructor's
     * options in order to call this method auto-magically.
     *
     * @param   string  $app_secret     LinkedIn App Secret
     * @return  sociall\LinkedIn
     */
    public function setAppSecret($app_secret)
    {
        $this->_app_secret = $app_secret;
        return $this;
    }

    /**
     * Getter method for the LinkedIn App Secret field.
     *
     * @return string
     */
    public function getAppSecret()
    {
        return $this->_app_secret;
    }

    /**
     * Setter method for the Auth Callback URL field. This URL field
     * is the URL called from LinkedIn when the autorization process
     * is done. The authorization process is Step 1 in OAuth 2.0,
     * which allow you to get permissions for your LinkedIn App from
     * the logged in users accounts.
     * The option "auth_callback_url" can be specified in the Constructor's
     * options in order to call this method auto-magically.
     *
     * @param   string  $auth_callback_url     Authorization Callback URL
     * @return  sociall\LinkedIn
     */
    public function setAuthCallbackURL($auth_callback_url)
    {
        $this->_auth_callback_url = $auth_callback_url;
        return $this;
    }

    /**
     * Getter method for the Authorization Callback URL.
     *
     * @return string
     */
    public function getAuthCallbackURL()
    {
        return $this->_auth_callback_url;
    }

    /**
     * Getter method for the last CSRF token generated.
     * There is no Setter for this method as this field
     * is populated by the API's different steps and
     * sometimes by LinkedIn.
     *
     * @return string
     */
    public function getLastCSRF()
    {
        return $this->_last_csrf;
    }
}

/**
 * the LinkedIn_Exception class is a base class
 * for throwing Exceptions related to the LinkedIn
 * API requests.
 *
 * @subpackage LinkedIn Exception Handling
 */
class LinkedIn_Exception
    extends \Exception
{}

/**
 * the LinkedIn_AuthorizationError is an Exception
 * class used when the LinkedIn Authorization response
 * provided contains an error from the LinkedIn API.
 *
 * @subpackage LinkedIn Exception Handling
 */
class LinkedIn_AuthorizationError
    extends LinkedIn_Exception
{}

/**
 * the LinkedIn_EmptyResponseError is an Exception
 * class used when the LinkedIn Authorization response
 * provided to @see LinkedIn::getAccessToken() is empty.
 *
 * @subpackage LinkedIn Exception Handling
 */
class LinkedIn_EmptyResponseError
    extends LinkedIn_Exception
{}

/**
 * the LinkedIn_APICallError is an Exception
 * class used when the LinkedIn API call you are trying
 * to fire is not yet authorized or when arguments to
 * an API call are missing.
 *
 * @subpackage LinkedIn Exception Handling
 */
class LinkedIn_APICallError
    extends LinkedIn_Exception
{}
