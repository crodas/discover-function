<?php

/** @foo xxx */
function fnc($v = true)
{
    return $v;
}

class foobar
{
    /** @foo yyy1 */
    public static function yyy($v = true)
    {
        return $v;
    }

    /** @foo yyy */
    public function xxx($v = true)
    {
        return $v;
    }
}
