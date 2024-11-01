<?php

namespace WallkitSDK;


class WallkitRequest
{
    /**
     * @var
     */
    public $method;

    /**
     * @var
     */
    protected $headers;

    /**
     * WallkitRequest constructor.
     *
     * @param $config
     */
    function __construct($config)
    {
        $config = array_merge([
            'method' => 'GET',
            'token' => null,
            'headers' => []
        ],$config);

        $this->method = $config['method'];
        $this->headers = $config['headers'];

    }

    /**
     *
     */
    function getHeaders() {

    }
}