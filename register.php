<?php
require_once 'includes/security.php';
require_once 'config.php';

if (isset($_SESSION['user'])) {
	header('Location: catalog.php');
	exit();
}
$errors = [];
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

	if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
		die("CSRF validation failed");
	}
	$username = trim($_POST['username'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$password = $_POST['password'] ?? '';
	$password2 = $_POST['password2'] ?? '';

	/* валидация */

	if (empty($username)) {
		$errors[] = "Введите имя пользователя";
	}
	if (!preg_match('/^[a-zA-Z0-9_а-яА-Я]{3,30}$/u', $username)) {
		$errors[] = "Имя пользователя должно содержать 3–30 символов";
	}
	if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$errors[] = "Некорректный email";
	}
	if (strlen($password) < 6) {
		$errors[] = "Пароль должен быть минимум 6 символов";
	}
	if ($password !== $password2) {
		$errors[] = "Пароли не совпадают";
	}


	/* проверка уникальности */

	$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
	$stmt->execute([$username, $email]);
	if ($stmt->fetch()) {
		$errors[] = "Пользователь или email уже существуют";
	}

	/* регистрация */

	if (empty($errors)) {
		$hash = password_hash($password, PASSWORD_DEFAULT);
		$stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
		$stmt->execute([$username, $email, $hash]);
		$_SESSION['register_success'] = true;
		header("Location: register.php");
		exit();
	}
}

if (isset($_SESSION['register_success'])) {
	$success = true;
	unset($_SESSION['register_success']);
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Регистрация - Читай-Город</title>
	<link rel="stylesheet" href="styles/style.css">
</head>

<body>
<?php require 'includes/header.php'; ?>
<main>
	<div class="auth-container">
		<h1>Регистрация</h1>
		<?php if ($success): ?>
			<div class="success">
				Регистрация успешна! Теперь вы можете <a href="login.php">войти</a>.
			</div>
		<?php endif; ?>

		<?php if (!empty($errors)): ?>
			<div class="error">
				<?php foreach ($errors as $err): ?>
					<p><?= htmlspecialchars($err) ?></p>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<?php if (!$success): ?>
			<form method="POST">
				<input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">
				<div class="auth-field">
					<label>Имя пользователя</label>
					<input type="text" name="username" required maxlength="30"
						value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
				</div>

				<div class="auth-field">
					<label>Email</label>
					<input type="email" name="email" required
						value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
				</div>

				<div class="auth-field">
					<label>Пароль</label>
					<input type="password" name="password" required minlength="6" maxlength="72">
				</div>

				<div class="auth-field">
					<label>Повторите пароль</label>
					<input type="password" name="password2" required minlength="6" maxlength="72">
				</div>

				<button type="submit" class="btn">
					Зарегистрироваться
				</button>
			</form>

			<div class="link">
				Уже есть аккаунт? <a href="login.php">Войти</a>
			</div>
		<?php endif; ?>
	</div>
</main>

<?php require 'includes/footer.php'; ?>
<script src="/js/burgermenu.js"></script>

</body>
</html>