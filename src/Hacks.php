<?php

namespace eNTiDi\Autotoc;

use SilverStripe\View\ViewableData;

class Hacks extends ViewableData
{
    /**
     * Add a new wrapper method.
     *
     * Similar to addWrapperMethod() but made public and working on
     * custom instances to allow to inject custom wrappers.
     *
     * @param string $instance
     * @param string $method
     * @param string $wrap
     */
    public static function addWrapperMethodToInstance($instance, $method, $wrap)
    {
        // hasMethod() trigger the population of $extra_methods
        $instance->hasMethod('UnexistentMethod');
        self::$extra_methods[get_class($instance)][strtolower($method)] = [
            'wrap'   => $wrap,
            'method' => $method,
        ];
    }

    public static function addCallbackMethodToInstance($instance, $method, $callback)
    {
        // hasMethod() trigger the population of $extra_methods
        $instance->hasMethod('UnexistentMethod');
        self::$extra_methods[get_class($instance)][strtolower($method)] = [
            'callback' => $callback,
        ];
    }
}
