<?php declare(strict_types=1);

/**
 * Set the system locale
 */

if (!isset(App::config()->locale)) {
    return;
}

$locale = App::config()->locale;

$localeCharset = setlocale(LC_ALL, "$locale.UTF-8", $locale);

if ($localeCharset === false) {
    trigger_error("Failed to set locale to '$locale'", E_USER_WARNING);
    return;
}

Locale::setDefault($localeCharset);
putenv("LC_ALL=$localeCharset");
