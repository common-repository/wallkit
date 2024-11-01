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


class WallkitToken
{
    /**
     * @var String $token
     */
    protected $value;

    /**
     * WallkitToken constructor.
     *
     * @param $token
     */
    function __construct($token)
    {
        $this->value = $token;
    }

    /**
     * @return String
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $appSecret
     * @return string
     */
    public function getSignedToken($appSecret) {
        return hash_hmac('sha256', $this->value, $appSecret);
    }
}