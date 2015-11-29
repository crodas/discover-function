<?php

/** @foo xxx */
function fnc($v = true)
{
    return $v;
}

class foobar
{
    /** @foo yyy1 @auth */
    public static function yyy($v = true)
    {
        return $v;
    }

    /** @foo yyy @auth */
    public function xxx($v = true)
    {
        return $v;
    }
}
