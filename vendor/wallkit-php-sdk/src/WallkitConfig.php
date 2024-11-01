<?php

namespace WallkitSDK;

class WallkitConfig
{
    /**
     * @var null
     */
    private static $_instance = null;

    /**
     * WallkitConfig constructor.
     */
    private function __construct() {

    }

    /**
     *
     */
    protected function __clone() {

    }

    /**
     * @return null|WallkitConfig
     */
    static public function getInstance() {
        if(is_null(self::$_instance))
        {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     *
     */
    public function import() {

    }

    /**
     *
     */
    public function get() {

    }

}