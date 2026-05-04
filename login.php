<?php
require_once 'includes/security.php';
require_once 'config.php';

if (isset($_SESSION['user'])) {
	header('Location: catalog.php');
	exit();
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
		die("CSRF validation failed");
	}
	$login = trim($_POST['login'] ?? '');
	$password = $_POST['password'] ?? '';

	if (empty($login) || empty($password)) {
		$error = "Заполните все поля";
	} else {
		$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
		$stmt->execute([$login, $login]);
		$user = $stmt->fetch();
		if (!$user) {
			$error = "Пользователь не найден";
		} elseif (!password_verify($password, $user['password'])) {
			$error = "Неверный пароль";
		} else {
			unset($user['password']);
			$_SESSION['user'] = $user;
			header('Location: catalog.php');
			exit();
		}
	}
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Вход - Книжный Ларец</title>
	<link rel="stylesheet" href="styles/style.css">
</head>

<body>
<?php require 'includes/header.php'; ?>
<main>
	<div class="auth-container">
		<h1>Вход в аккаунт</h1>

		<?php if ($error): ?>
			<div class="error">
				<?= htmlspecialchars($error) ?>
			</div>
		<?php endif; ?>

		<form method="POST">
			<input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">
			<div class="auth-field">
				<label>Имя пользователя или Email</label>
				<input type="text" name="login" required
					value="<?= htmlspecialchars($_POST['login'] ?? '') ?>">
			</div>

			<div class="auth-field">
				<label>Пароль</label>
				<input type="password" name="password" required>
			</div>

			<button type="submit" class="btn">
				Войти
			</button>
		</form>

		<div class="link">
			Нет аккаунта? <a href="register.php">Зарегистрироваться</a>
		</div>
	</div>
</main>

<?php require 'includes/footer.php'; ?>
<script src="/js/burgermenu.js"></script>

</body>
</html>