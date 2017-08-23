<?php

require __DIR__.DIRECTORY_SEPARATOR.'libs.php';

define('USERNAME', 'test@lish.ir'); // your user name goes here
define('PASSWORD', '123456');       // and your password

$targetUrl = 'http://google.com';   //your link that you want to short
$category_id = 11;

$token = login(USERNAME, PASSWORD);
$shorten = shortenUrl($targetUrl, $token, TYPE_COMMERCIAL, $category_id, 1);
print_r($shorten);