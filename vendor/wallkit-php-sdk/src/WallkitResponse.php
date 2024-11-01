<?php
/**
 * The specific functionality of the plugin.
 *
 *
 * @package    Wallkit_Wp
 * @subpackage Wallkit_Wp/admin
 * @author     Wallkit <dev@wallkit.net>
 */

namespace WallkitSDK;

use WallkitSDK\Models\User;

class WallkitResponse
{
    /**
     * @var object;
     */
    protected $response = [];

    /**
     * @var array|mixed
     */
    protected $response_array = [];

    /**
     * http status
     * @var int
     */
    protected $status_code = 200;

    /**
     * WallkitResponse constructor.
     *
     * @param array $info
     * @param $response
     */
    function __construct(array $info, $response)
    {
        if(array_key_exists('http_code', (array) $info))
        {
            $this->status_code = $info['http_code'];
        }

        $this->response_array = json_decode($response, true);
        $this->response = json_decode($response);
    }

    /**
     * @return User
     */
    function asUser() {
        return new User($this->toArray());
    }

    /**
     * @return array
     */
    function toArray() {
        return (array) $this->response_array;
    }

    /**
     * @return object
     */
    function toObject() {
        return (object) $this->response;
    }

    /**
     * @return int
     */
    function getCode() {
        return (int) $this->status_code;
    }
}