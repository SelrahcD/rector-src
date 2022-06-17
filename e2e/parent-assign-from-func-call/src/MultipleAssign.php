<?php

final class MultipleAssign
{
    /**
     * @var LoopInterface
     */
    private static $instance;

    public static function get()
    {
        if (self::$instance instanceof LoopInterface) {
            return self::$instance;
        }

        self::$instance = $loop = Factory::create();

        register_shutdown_function(function () {
            $test = test([]);
        });
    }
}
