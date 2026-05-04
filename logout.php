<?php
require_once 'includes/security.php';

/* проверка запроса на POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: index.php');
	exit();
}

/* проверка CSRF */
if (
	!isset($_POST['csrf']) ||
	!isset($_SESSION['csrf_token']) ||
	$_POST['csrf'] !== $_SESSION['csrf_token']
) {
	die('CSRF validation failed');
}

/* очистка сессии */
$_SESSION = [];

/* удаление cookie */
if (ini_get("session.use_cookies")) {
	$params = session_get_cookie_params();
	setcookie(
		session_name(),
		'',
		time() - 42000,
		$params["path"],
		$params["domain"],
		$params["secure"],
		$params["httponly"]
	);

}

/* уничтожение сессии */
session_destroy();

/* редирект */
header('Location: index.php');
exit();