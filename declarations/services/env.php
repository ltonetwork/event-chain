<?php
/**
 * Environment variables
 */

return [
    'app.env' => function () {
        return getenv('APPLICATION_ENV') ?: 'dev';
    }
];
