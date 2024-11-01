<?php

namespace WallkitSDK;

class FirebaseToken
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
}