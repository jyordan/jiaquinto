<?php

require_once('vendor/autoload.php');
require_once("./helper.php");

use Dotenv\Dotenv;

// Load .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

function env($key, $default = '')
{
  return $_ENV[$key] ?? $default;
}
