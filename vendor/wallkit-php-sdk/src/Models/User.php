<?php

namespace WallkitSDK\Models;

class User
{
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var User self
     */
    private static $instance;

    /**
     * User constructor.
     *
     * @param array $attributes
     */
    function __construct(array $attributes = [])
    {
        if(!self::$instance instanceof User)
        {
            self::$instance = $this;
        }

        if(count($attributes))
        {
            self::$instance->setAttributes($attributes);
        }
    }

    /**
     * @param array $attributes
     */
    public function setAttributes(array $attributes = []) {
        $this->attributes = $attributes;
    }

    /**
     * @return User
     */
    public function __clone() {
        // TODO: Implement __clone() method.
        return self::$instance;
    }

    /**
     * @return bool
     */
    public function isAuth() {
        return (bool) count($this->attributes) && array_key_exists("email", $this->attributes);
    }

    /**
     * @return array
     */
    function toArray() {
        return (array) $this->attributes;
    }

    /**
     * @return array
     */
    function plans() {
        $subscription = [];
        if(array_key_exists('subscriptions', $this->attributes))
        {
            $subscription = $this->attributes['subscriptions'];
        }
        return array_map(function($subscription) {
            if(array_key_exists("plan", $subscription))
            {
                return $subscription['plan'];
            }
        }, $subscription);
    }

    /**
     * @return array|mixed
     */
    public function subscriptions() {
        $subscription = [];
        if(array_key_exists('subscriptions', $this->attributes))
        {
            $subscription = $this->attributes['subscriptions'];
        }

        return $subscription;
    }

    /**
     * @return bool
     */
    public function hasPaidSubscription() {
        $subscription = [];
        if(array_key_exists('subscriptions', $this->attributes))
        {
            $subscription = $this->attributes['subscriptions'];
        }

        foreach($subscription AS $item)
        {
            if(array_key_exists("price", $item) && intval($item["price"]) > 0)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $plan
     * @return bool
     */
    function hasPlan($plan) {

        $plan = is_array($plan)? $plan: [$plan];

        $titles = array_map(function ($plan) {
            return $plan['title'];
        },$this->plans());

        return count(array_intersect($titles,$plan))>0;
    }

    /**
     * @param null $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key = NULL, $default = NULL) {

        if(array_key_exists($key, $this->attributes))
        {
            return $this->attributes[$key];
        }

        return $default;
    }
}