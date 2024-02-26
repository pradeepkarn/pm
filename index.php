<?php
// error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once(__DIR__ . "/config.php");
require_once(__DIR__ . "/settings.php");
import("/includes/class-autoload.inc.php");
import("functions.php");
import("settings.php");
define("direct_access", 1);
// import('/vendor/autoload.php');
define('DEFAULT_LANG', 'en');
$home = home;
$languages = ["hi", "en"];
define('LANG_ARR', $languages);
if (!isset($_COOKIE['lang'])) {
  setcookie('lang', DEFAULT_LANG, time() + (86400 * 30 * 12), "/");
}
if (isset($_COOKIE['lang'])) {
  if (!in_array($_COOKIE['lang'], $languages)) {
    setcookie('lang', DEFAULT_LANG, time() + (86400 * 30 * 12), "/");
  }
}

function lang($dir, $lang = null)
{
  if ($lang == null) {
    if (!isset($_COOKIE['lang'])) {
      $lang = DEFAULT_LANG;
    } else {
      $lang = $_COOKIE['lang'];
    }
  }

  $filePath = RPATH . "/data/php/lang/{$lang}/{$dir}/lang.php";

  if (!is_file($filePath)) {
    if ($lang == null) {
      $lang = DEFAULT_LANG;
    }
    $filePath = RPATH .  "/data/php/lang/{$lang}/{$dir}/lang.php";
  }
  include $filePath;
  return obj($lang);
}

$home = home;
define('RELOAD', js("location.reload();"));

$acnt = new Account;
if (isset($_COOKIE['remember_token'])) {
  $acnt->loginWithCookie($cookie = $_COOKIE['remember_token']);
}
$acnt = $acnt->getLoggedInAccount();
define('USER', $acnt);
if (authenticate()) {
  if (!USER) {
    session_destroy();
    header("location:/" . home);
    exit;
  }
}
$checkaccess = ['admin', 'subadmin'];
if (authenticate() == true) {
  if (isset(USER['user_group'])) {
    $pass = in_array(USER['user_group'], $checkaccess);
    define('PASS', $pass);
  } else {
    $pass = false;
    define('PASS', $pass);
  }
} else {
  $pass = false;
  define('PASS', $pass);
}

import("routes/routes.php");
