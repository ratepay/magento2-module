<?php
class LibraryAutoloader
{
    private static $path = "Library/src";

    static public function loader($className)
    {
        $className = str_replace('RatePAY\\', '', $className);

        $absoluteDir = dirname(__FILE__);
        $libDir = self::$path;
        $fileName = str_replace('\\', '/', $className) . '.php';

        $file = $absoluteDir . "/" . $libDir . "/" . $fileName;

        if (file_exists($file)) {
            require_once($file);
        }
    }
}

spl_autoload_register('LibraryAutoloader::loader');