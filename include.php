<?php

/**
 * DO NOT ADD ANYTHING TO THIS FILE!
 * @todo Handle this in middleware instead, so we can remove it altogether.
 */

if (isset($_SERVER['HTTP_HOST'])) {
    define('BASE_URL', (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST']);
    define('DOMAIN', preg_replace('/^' . basename($_SERVER['DOCUMENT_ROOT']) . '\./', '', $_SERVER['HTTP_HOST']));
}

if (isset($_SERVER['HTTP_X_FORWARDED_URL'])) {
    if (substr($_SERVER['HTTP_X_FORWARDED_URL'], -1 * strlen($_SERVER['REQUEST_URI'])) !== $_SERVER['REQUEST_URI']) {
        trigger_error("Proxy isn't set to application root", E_USER_ERROR);
    }
    define('BASE_REWRITE', substr($_SERVER['HTTP_X_FORWARDED_URL'], 0, -1 * strlen($_SERVER['REQUEST_URI'])));
}

require_once("vendor/autoload.php");
