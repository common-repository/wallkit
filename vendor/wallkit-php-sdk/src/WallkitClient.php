<?php

namespace WallkitSDK;

use WallkitSDK\Exceptions\WallkitApiException;
use WallkitSDK\Exceptions\WallkitException;
use WallkitSDK\Exceptions\WallkitNotFoundException;

class WallkitClient
{
    
    /**
     * @var WallkitToken $token
     */
    protected $token;

    /**
     * @var FirebaseToken $firebase_token
     */
    protected $firebase_token;

    /**
     * @var WallkitSession $session_token
     */
    protected $session_token;

    protected $host;
    protected $api_version;
    protected $resource;
    
    /**
     * WallkitClient constructor.
     * @param $token
     * @param $host
     * @param $api_version
     * @param String $resource
     */
    function __construct($token, $host, $api_version, $resource)
    {
        $this->token = $token;
        $this->host = $host;
        $this->api_version = $api_version;
        $this->resource = $resource;
    }
    
    /**
     * @param WallkitToken $token
     * @throws WallkitException
     */
    function setToken($token)
    {
        if(!$token instanceof WallkitToken)
            throw new WallkitException('Invalid Token');
        
        $this->token = $token;
    }

    /**
     * @param FirebaseToken $token
     * @throws WallkitException
     */
    function setFirebaseToken($token)
    {
        if (!$token instanceof FirebaseToken)
            throw new WallkitException('Invalid Firebase Token');

        $this->firebase_token = $token;
    }

    /**
     * @param WallkitSession $session_token
     * @throws WallkitException
     */
    function setSessionToken($session_token)
    {
        if (!$session_token instanceof WallkitSession)
            throw new WallkitException('Invalid Firebase Token');

        $this->session_token = $session_token;
    }
    
    /**
     * @return bool|String
     */
    public function getTokenValue()
    {
        if($this->token)
            return $this->token->getValue();
        
        return false;
    }

    /**
     * @return bool|String
     */
    public function getFirebaseTokenValue() {
        if ($this->firebase_token)
            return $this->firebase_token->getValue();
        return false;
    }

    /**
     * @return bool|String
     */
    public function getSessionTokenValue() {
        if ($this->session_token)
            return $this->session_token->getValue();
        return false;
    }
    
    /**
     * @param $headers
     * @return array
     */
    function compileHeaders($headers)
    {
        $headers = array_merge([
            'token'    => $this->getTokenValue(),
            'resource' => $this->resource,
        ], $headers);

        if($this->firebase_token instanceof FirebaseToken)
        {
            $headers['firebase-token'] = $this->getFirebaseTokenValue();
        }

        if($this->session_token instanceof WallkitSession)
        {
            $headers['session'] = $this->getSessionTokenValue();
        }

        if(isset($_SERVER['REMOTE_ADDR'])) {
            $headers['wk-plugin-x-forwarded-for'] = $_SERVER['REMOTE_ADDR'];
        }

        return array_map(function ($value, $key) {
            return $key.': '.$value;
        }, $headers, array_keys($headers));
    }

    /**
     * @param $method
     * @param array $headers
     * @param $url
     * @param $body
     * @param bool $is_json
     * @return WallkitResponse
     * @throws WallkitApiException
     * @throws WallkitException
     */
    function sendRequest($method,$headers,$url,$body, $is_json = true) {

        $info = "";

        $url = "https://$this->host/api/$this->api_version$url";

        if(defined("WPWKP_VERSION"))
        {
            $info .= " WPWKP v".WPWKP_VERSION;
        }

        if(function_exists("phpversion"))
        {
            $info .= " php v".phpversion();
        }

        $headers["Wallkit-Client"] = "PhpSDK v0.1.3".$info;

        if($is_json) {
            $body = $this->utf8ize($body);
        }

        if($method === "GET")
        {
            $url .= "?".http_build_query($body);
        }

        $options = [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_URL => $url,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => $this->compileHeaders($headers),
            CURLOPT_RETURNTRANSFER => true, // Follow 301 redirects
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ];

        if($method !== "GET")
        {
            try {
                if($is_json) {
                    $body = wp_json_encode($body, JSON_UNESCAPED_UNICODE);
                }
                $options[CURLOPT_POSTFIELDS] = $body;
            }
            catch (\Exception $exception)
            {
                throw new WallkitException("Json encode is failed");
            }
        }

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        $curlErrorCode = curl_errno($curl);
        if ($curlErrorCode) {
            throw new WallkitException(curl_error($curl), $curlErrorCode);
        }

        $info = curl_getinfo($curl);

        curl_close($curl);

        if (!($info['http_code'] <= 226 && $info['http_code'] >= 200 )) {
            throw new WallkitApiException($info,$response);
        }

        return new WallkitResponse($info,$response);
    }
    
    /**
     * @param $mixed
     * @return array|string
     */
    private function utf8ize($mixed)
    {
        
        if(!function_exists("mb_convert_encoding")) {
            return $mixed;
        }
        
        if(is_array($mixed)) {
            foreach($mixed as $key => $value) {
                $mixed[$key] = $this->utf8ize($value);
            }
        } else if(is_string($mixed)) {
            return mb_convert_encoding($mixed, "UTF-8", "auto");
        }
        
        return $mixed;
    }
}