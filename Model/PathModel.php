<?php

namespace Utility\Model;

class PathModel extends \Framework\Model\PathModel
{
    static function getBrowscapDirectory()
    {
        return self::getDataDirectory() . "browscap/";
    }
    static function getAssetsDirectory()
    {
        return self::getApplicationDirectory() . "assets/";
    }
    static function getPackagesDirectory()
    {
        return self::getApplicationDirectory() . "packages/";
    }
    static function getSmartyCompileDirectory()
    {
        return self::getDataDirectory() . "smarty/compile/";
    }
    static function getSmartyCacheDirectory()
    {
        return self::getDataDirectory() . "smarty/cache/";
    }
}