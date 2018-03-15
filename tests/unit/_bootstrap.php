<?php

$locale = 'nl_NL';
$locale_charset = setlocale(LC_ALL, "$locale.UTF-8", $locale);
Locale::setDefault($locale_charset);
putenv("LC_ALL=$locale_charset");
