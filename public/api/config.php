<?php

class Config {
    const CONFIG_FILE = '../../../config.json';
    public static $config;
    public static $proxyContent;
};

Config::$config  = json_decode(file_get_contents(__DIR__ . Config::CONFIG_FILE), true);
Config::$proxyContent = isset(Config::$config['proxy']) ? stream_context_create([
    'http' => [
        'proxy' => Config::$config['proxy'],
        'request_fulluri' => true,
    ]
]) : null;