<?php

namespace WallkitSDK;

class WallkitSession
{
    /**
     * @var String $value
     */
    protected $value;

    /**
     * WallkitSession constructor.
     *
     * @param $session_token
     */
    function __construct($session_token)
    {
        $this->value = $session_token;
    }

    /**
     * @return String
     */
    public function getValue()
    {
        return $this->value;
    }
}