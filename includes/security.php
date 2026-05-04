<?php
//    оптимизация кеш-памяти
header("Cache-Control: public, max-age=3600");

//    безопасность сессии
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

session_start();

//    безопасность заголовков
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// CSRF токен

if (empty($_SESSION['csrf_token'])) {
	$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}