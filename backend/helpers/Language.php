<?php

function __($key)
{
    static $strings = null;
    static $loaded = false;

    if (!$loaded) {
        $loaded = true;
        $lang = $_SESSION['lang'] ?? 'en';

        $file = __DIR__ . "/../lang/{$lang}.php";
        if (file_exists($file)) {
            $strings = require $file;
        } else {
            $strings = require __DIR__ . "/../lang/en.php";
        }
    }

    return $strings[$key] ?? $key;
}

function setLanguage($lang)
{
    if (in_array($lang, ['en', 'sw'])) {
        $_SESSION['lang'] = $lang;
    }
}
