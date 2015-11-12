<?php

/** @foo xxx */
function fnc()
{
    return true;
}

class foobar
{
    /** @foo yyy1 */
    public static function yyy()
    {
        return true;
    }

    /** @foo yyy */
    public function xxx()
    {
        return true;
    }
}
