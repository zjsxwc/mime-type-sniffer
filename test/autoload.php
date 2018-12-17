<?php
/**
 * Created by PhpStorm.
 * User: wangchao
 * Date: 17/12/2018
 * Time: 8:39 AM
 */


function classLoader($class)
{
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = __DIR__ . '/../src/' . $path . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
}
spl_autoload_register('classLoader');
