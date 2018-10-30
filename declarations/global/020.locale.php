<?php declare(strict_types=1);

/**
 * Set the system locale.
 * @internal This doesn't need to come from the config, let's change that.
 */

use Psr\Container\ContainerInterface;

return function (ContainerInterface $container) {
    if (!$container->has('config.locale')) {
        return;
    }

    $locale = $container->get('config.locale');

    $localeCharset = setlocale(LC_ALL, "$locale.UTF-8", $locale);

    if ($localeCharset === false) {
        trigger_error("Failed to set locale to '$locale'", E_USER_WARNING);
        return;
    }

    Locale::setDefault($localeCharset);
    putenv("LC_ALL=$localeCharset");
};
