<?php

namespace App;

class Utils
{
    public static function errorObjNotFound(string $obj, string $by = ''): string {
        return "\"$obj\" object not found" . (!$by ? '' : " by \"$by\"") . ".";
    }
}
