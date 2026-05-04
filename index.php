<?php

require_once 'includes/security.php';
require_once 'config.php';

/* Инизиализация отзывов */

if (!isset($_SESSION['cart'])) {
	$_SESSION['cart'] = [];
}

if (isset($_SESSION['user']) && isset($_SESSION['user']['cart'])) {
	$_SESSION['cart'] = &$_SESSION['user']['cart'];
}


/* Выход из системы */

if (isset($_GET['logout'])) {
	$_SESSION = [];
	session_destroy();
	header('Location: index.php');
	exit();
}


/* Отзывы */

$review_success = null;
$review_error = null;
if (isset($_POST['submit_review'])) {

	if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
		die("CSRF validation failed");
	}
	$text = trim($_POST['review_text'] ?? '');
	if (empty($text)) {
		$review_error = "Отзыв не может быть пустым.";
	} else {
		$text = mb_substr($text, 0, 1000);
		$author = $_SESSION['user']['username'] ?? 'Аноним';
		$email  = $_SESSION['user']['email'] ?? null;

		try {
			$db = (new Database())->getConnection();
			$stmt = $db->prepare("
				INSERT INTO reviews (author, email, text, status)
				VALUES (:author, :email, :text, 'pending')
			");
			$stmt->execute([
				':author' => $author,
				':email'  => $email,
				':text'   => $text
			]);
			$review_success = "Отзыв отправлен и ожидает модерации!";
		} catch (PDOException $e) {
			$review_error = "Ошибка при отправке отзыва.";
			error_log("Review error: " . $e->getMessage());
		}
	}
}


/* Загрузка отзывов */

$db_reviews = [];
try {
	$db = (new Database())->getConnection();
	$stmt = $db->prepare("
		SELECT author, text, DATE_FORMAT(date, '%d.%m.%Y') AS date
		FROM reviews
		WHERE status = 'active'
		ORDER BY date DESC
		LIMIT 12
	");

	$stmt->execute();
	$db_reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
	$review_error = $review_error ?: "Не удалось загрузить отзывы";
	error_log("Review load error: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Книжный магазин "Книжный Ларец"</title>
	<link rel="stylesheet" href="styles/style.css">
</head>
<body>
<?php require 'includes/header.php'; ?>
<main>
	<div class="hero">
		<div class="hero-content">
			<div class="hero-text">
				<h1>Добро пожаловать в "Книжный Ларец"</h1>
				<p>Огромный выбор книг по приятным ценам</p>
				<a href="catalog.php" class="btn-index">
					В каталог
				</a>
			</div>

			<div class="photo">
				<img src="img/face.webp" loading="lazy" alt="Книжный магазин">
			</div>
		</div>
	</div>

	<div class="features">
		<div class="feature">
			<h3>Широкий выбор</h3>
			<p>Книги на любой вкус</p>
		</div>

		<div class="feature">
			<h3>Быстрая доставка</h3>
			<p>1–3 дня по России</p>
		</div>

		<div class="feature">
			<h3>Честные отзывы</h3>
			<p>Реальные мнения покупателей</p>
		</div>
	</div>

	<section class="reviews">
		<div class="review-form">
			<h3>Оставить отзыв</h3>

			<?php if ($review_success): ?>
				<div class="success-message">
					<?= htmlspecialchars($review_success) ?>
				</div>
			<?php endif; ?>

			<?php if ($review_error): ?>
				<div class="error-message">
					<?= htmlspecialchars($review_error) ?>
				</div>
			<?php endif; ?>

			<form method="POST" novalidate>
				<input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">
				<textarea
					name="review_text"
					placeholder="Ваш отзыв о магазине..."
					required
					maxlength="1000"
					rows="5"
				></textarea>
				<button type="submit" name="submit_review">
					Отправить
				</button>
			</form>
		</div>
		<div class="review-list">
			<h3>Отзывы покупателей</h3>
			<?php if (empty($db_reviews)): ?>
				<p class="empty">
					Пока нет одобренных отзывов. Будьте первым!
				</p>
			<?php else: ?>
				<?php foreach ($db_reviews as $r): ?>

					<div class="review">
						<div class="review-author">
							<?= htmlspecialchars($r['author']) ?>
						</div>

						<div class="review-date">
							<?= htmlspecialchars($r['date']) ?>
						</div>

						<div class="review-text">
							<?= nl2br(htmlspecialchars($r['text'])) ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</section>
</main>

<?php require 'includes/footer.php'; ?>

<script src="/js/burgermenu.js"></script>
</body>
</html>
