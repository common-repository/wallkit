<?php

namespace WallkitSDK;

use WallkitSDK\Exceptions\WallkitException;
use WallkitSDK\Models\User;

class WallkitSDK
{

    /**
     * @var WallkitClient $client
     */
    protected $client;

    /**
     * @var self
     */
    private static $instance;

    /**
     * @var string|null
     */
    protected $resource_id;

    /**
     * @var string|null
     */
    protected $resource_secret;

    /**
     * @var WallkitToken $token
     */
    protected $token;

    /**
     * @var FirebaseToken $token
     */
    protected $firebase_token;

    /**
     * @var WallkitSession $token
     */
    protected $session_token;

    /**
     * @var User
     */
    private $user;

    /**
     * WallkitSDK constructor.
     *
     * @param array $config
     * @throws \Wallkit_Wp_SDK_Exception
     */
    function __construct(array $config=[])
    {

        if(!self::$instance instanceof self)
        {
            self::$instance = $this;
        }

        if(!$config || !is_array($config) || !count($config)) {
            throw new \Wallkit_Wp_SDK_Exception('Please configure Wallkit.');
        }

        if (!isset($config['public_key']) || !$config['public_key'] || !isset($config['secret_key']) || !$config['secret_key']){
            throw new \Wallkit_Wp_SDK_Exception('Please configure wallkit resource: Public and private api access keys is required');
        }

        if (!isset($config['api_host']) || !$config['api_host']){
            throw new \Wallkit_Wp_SDK_Exception('Please configure wallkit resource: Host is required');
        }

        if(!isset($config['api_version']))
        {
            $config['api_version'] = 'v1';
        }

        self::$instance->resource_id = $config['public_key'];
        self::$instance->resource_secret = $config['secret_key'];
        self::$instance->token = $this->getRequestToken();
        self::$instance->firebase_token = $this->getRequestFirebaseToken();
        self::$instance->session_token = $this->getRequestSessionToken();
        self::$instance->client = new WallkitClient($this->token,$config['api_host'],$config['api_version'],$config['public_key']);

        if(!$this->client) {
            throw new \Wallkit_Wp_SDK_Exception('Please configure Wallkit.');
        }

        if(self::$instance->firebase_token instanceof FirebaseToken)
        {
            self::$instance->client->setFirebaseToken(self::$instance->firebase_token);
        }

        if(self::$instance->session_token instanceof WallkitSession)
        {
            self::$instance->client->setSessionToken(self::$instance->session_token);
        }

        try
        {
            if(self::$instance->token)
            {
                self::$instance->user = self::$instance->get("/user")->asUser();
            }
        }
        catch (\Exception $exception)
        {
            self::$instance->user = new User();
        }

    }

    /**
     * @return WallkitSDK
     */
    public function __clone() {
        return false;
    }

    /**
     * @return array
     */
    public function __debugInfo() {
        // TODO: Implement __debugInfo() method.
        return array(
            "token" => $this->token,
            "user" => self::$instance->user
        );
    }

    /**
     * @return null|WallkitToken
     */
    function getRequestToken()
    {
        if (isset($_SERVER['HTTP_WK_TOKEN'])){
            return new WallkitToken(stripcslashes($_SERVER['HTTP_WK_TOKEN']));
        }

        if (isset($_COOKIE['wk-token_'.self::$instance->resource_id])){
            return new WallkitToken(stripcslashes($_COOKIE['wk-token_'.self::$instance->resource_id]));
        }

        if (isset($_COOKIE['wk-token'])){
            return new WallkitToken(stripcslashes($_COOKIE['wk-token']));
        }

        return null;
    }

    /**
     * @return null|FirebaseToken
     */
    function getRequestFirebaseToken()
    {
        if (isset($_SERVER['HTTP_FIREBASE_TOKEN'])){
            return new FirebaseToken(stripcslashes($_SERVER['HTTP_FIREBASE_TOKEN']));
        }

        if (isset($_COOKIE['firebase-token_'.self::$instance->resource_id])){
            return new FirebaseToken(stripcslashes($_COOKIE['firebase-token_'.self::$instance->resource_id]));
        }

        if (isset($_COOKIE['firebase-token'])){
            return new FirebaseToken(stripcslashes($_COOKIE['firebase-token']));
        }

        return null;
    }

    /**
     * @since 3.3.7
     *
     * @return null|WallkitSession
     */
    function getRequestSessionToken()
    {
        if (isset($_SERVER['HTTP_WK_SESSION'])){
            return new WallkitSession(stripcslashes($_SERVER['HTTP_WK_SESSION']));
        }

        if (isset($_COOKIE['wk-session_'.self::$instance->resource_id])){
            return new WallkitSession(stripcslashes($_COOKIE['wk-session_'.self::$instance->resource_id]));
        }

        return null;
    }

    /**
     * @return User
     */
    public function getUser() {
        return self::$instance->user;
    }

    /**
     * @param $token
     * @return null|WallkitToken
     * @throws WallkitException
     */
    function setToken($token)
    {
        $this->token = new WallkitToken($token);
        $this->client->setToken($this->token);

        return $this->token;
    }

    /**
     * @param $token
     * @return null|FirebaseToken
     * @throws WallkitException
     */
    function setFirebaseToken($token)
    {
        $this->firebase_token = new FirebaseToken($token);
        $this->client->setFirebaseToken($this->firebase_token);

        return $this->firebase_token;
    }

    /**
     * @return null|FirebaseToken
     */
    function getFirebaseToken()
    {
        return $this->firebase_token;
    }

    /**
     * @return null|string
     */
    function getResourcePublicKey()
    {
        return $this->resource_id;
    }

    /**
     * @return bool
     */
    function isAuth() {
        return (bool) $this->token && self::$instance->user && self::$instance->user->isAuth();
    }

    /**
     * @param $url
     * @param array $params
     * @param bool $asAdmin
     * @return \WallkitSDK\WallkitResponse
     * @throws \WallkitSDK\Exceptions\WallkitApiException
     * @throws \WallkitSDK\Exceptions\WallkitException
     * @throws \WallkitSDK\Exceptions\WallkitNotFoundException
     */
    function get($url,$params = [],$asAdmin = false) {
        $headers = [];
        if ($asAdmin) {
            $headers['token'] = hash_hmac('sha256', $this->resource_id, $this->resource_secret);
        }
        return $this->client->sendRequest("GET",$headers, $url, $params);
    }

    /**
     * @param $url
     * @param array $params
     * @param bool $asAdmin
     * @return \WallkitSDK\WallkitResponse
     * @throws \WallkitSDK\Exceptions\WallkitApiException
     * @throws \WallkitSDK\Exceptions\WallkitException
     * @throws \WallkitSDK\Exceptions\WallkitNotFoundException
     */
    function post($url,$params = [],$asAdmin = false) {
        $headers = [
            'Content-Type'=>'application/json; charset=utf-8',
            'Accept' => 'application/json'
        ];
        if ($asAdmin) {
            $headers['token'] = hash_hmac('sha256', $this->resource_id, $this->resource_secret);
        }
        return $this->client->sendRequest("POST",$headers, $url, $params);
    }

    /**
     * @param $url
     * @param array $params
     * @param bool $asAdmin
     * @return WallkitResponse
     * @throws Exceptions\WallkitApiException
     * @throws WallkitException
     */
    function post_form_data($url,$params = [],$asAdmin = false) {
        $headers = [
            'Content-Type'=>'multipart/form-data; charset=utf-8'
        ];
        if ($asAdmin) {
            $headers['token'] = hash_hmac('sha256', $this->resource_id, $this->resource_secret);
        }
        return $this->client->sendRequest("POST",$headers, $url, $params, false);
    }

    /**
     * @param $url
     * @param array $params
     * @param bool $asAdmin
     * @return WallkitResponse
     * @throws Exceptions\WallkitApiException
     * @throws WallkitException
     */
    function put($url,$params = [],$asAdmin = false) {
        $headers = [
            'Content-Type'=>'application/json; charset=utf-8',
            'Accept' => 'application/json'
        ];
        if ($asAdmin) {
            $headers['token'] = hash_hmac('sha256', $this->resource_id, $this->resource_secret);
        }
        return $this->client->sendRequest("PUT",$headers, $url, $params);
    }

    /**
     * @param $url
     * @param array $params
     * @param bool $asAdmin
     * @return WallkitResponse
     * @throws Exceptions\WallkitApiException
     * @throws WallkitException
     */
    function delete($url,$params = [],$asAdmin = false) {
        $headers = [
            'Content-Type'=>'application/json; charset=utf-8',
            'Accept' => 'application/json'
        ];
        if ($asAdmin) {
            $headers['token'] = hash_hmac('sha256', $this->resource_id, $this->resource_secret);
        }
        return $this->client->sendRequest("DELETE",$headers, $url, $params);
    }

    /**
     * Verify content id hash
     *
     * @param $content_id
     *
     * @return false|string
     */
    function getContentSignatureById($content_id) {
        return hash_hmac('sha256', $content_id . $this->resource_id . $this->token->getValue(), $this->resource_secret);
    }
}