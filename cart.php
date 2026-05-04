<?php
require_once 'includes/security.php';
require_once 'config.php';
include 'data/books.php';

if (!isset($_SESSION['cart'])) {
	$_SESSION['cart'] = [];
}

/* изменение количества товара */

if (isset($_POST['update_quantity'])) {
	if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
		die("CSRF validation failed");
	}

	$book_id = (int)($_POST['book_id'] ?? 0);
	$action = $_POST['action'] ?? '';

	if (!isset($_SESSION['cart'][$book_id])) {
		header('Location: cart.php');
		exit();
	}
	if ($action === 'increase') {
		$_SESSION['cart'][$book_id]++;
	}
	elseif ($action === 'decrease') {
		if ($_SESSION['cart'][$book_id] > 1) {
			$_SESSION['cart'][$book_id]--;
		}
	}
	elseif ($action === 'remove') {
		unset($_SESSION['cart'][$book_id]);
	}
	header('Location: cart.php');
	exit();
}

/* оформление заказа */

if (isset($_POST['checkout'])) {
	if (!isset($_SESSION['user'])) {
		header('Location: login.php');
		exit();
	}
	if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
		die("CSRF validation failed");
	}
	if (!empty($_SESSION['cart'])) {
		$order_number = 'ORD-' . date('Ymd-His');
		$_SESSION['last_order'] = $order_number;
		$_SESSION['cart'] = [];
		header('Location: cart.php?success=1');
		exit();
	}
}

$total_items = 0;
$total_price = 0;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Корзина - Читай-Город</title>
	<link rel="stylesheet" href="styles/style.css">
</head>
<body>
<?php require 'includes/header.php'; ?>

<main>
	<h1>Корзина</h1>

	<?php if (isset($_SESSION['user'])): ?>
	<div class="user-notice warning">
		<p>При выходе из аккаунта корзина будет очищена!</p>
	</div>
	<?php endif; ?>

	<?php if (!isset($_SESSION['user'])): ?>
	<div class="user-notice">
		<p>Чтобы оформить заказ необходимо войти в аккаунт!</p>
	</div>
	<?php endif; ?>

	<?php if (isset($_GET['success'])): ?>
	<div class="success-message">
		Заказ успешно оформлен! Номер заказа:
		<?= htmlspecialchars($_SESSION['last_order']) ?>
	</div>
	<?php endif; ?>

	<div class="cart-items">
		<?php if (empty($_SESSION['cart'])): ?>
		<div class="empty-cart-message">
			Ваша корзина пуста
		</div>

		<?php else: ?>
		<?php foreach ($_SESSION['cart'] as $book_id => $quantity): ?>
		<?php if (isset($books[$book_id])): ?>

		<?php
			$book = $books[$book_id];
			$item_total = $book['price'] * $quantity;
			$total_items += $quantity;
			$total_price += $item_total;
		?>

		<div class="cart-item">
			<div class="item-info">
				<div class="item-image">
					<img src="img/<?= htmlspecialchars($book['image']) ?>" alt="<?= htmlspecialchars($book['title']) ?>" onerror="this.src='img/default.jpg'">
		</div>

		<div class="item-details">
			<h3><?= htmlspecialchars($book['title']) ?></h3>
			<p><?= htmlspecialchars($book['author']) ?></p>
			<p><?= $book['price'] ?> руб.</p>
		</div>
	</div>

	<div class="item-controls">
		<div class="quantity-controls">
			<form method="POST" style="display:inline;">
				<input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">
				<input type="hidden" name="book_id" value="<?= $book_id ?>">
				<input type="hidden" name="action" value="decrease">
				<button type="submit" name="update_quantity" class="quantity-btn minus">-</button>
			</form>

			<span class="quantity"><?= $quantity ?></span>

			<form method="POST" style="display:inline;">
				<input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">
				<input type="hidden" name="book_id" value="<?= $book_id ?>">
				<input type="hidden" name="action" value="increase">
				<button type="submit" name="update_quantity" class="quantity-btn plus">+</button>
			</form>
		</div>
				<form method="POST">
					<input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">
					<input type="hidden" name="book_id" value="<?= $book_id ?>">
					<input type="hidden" name="action" value="remove">
					<button type="submit" name="update_quantity" class="remove-item">
						Удалить
					</button>
				</form>
		</div>
	</div>
	<?php endif; ?>
	<?php endforeach; ?>
	<?php endif; ?>
	</div>

	<?php if (!empty($_SESSION['cart'])): ?>
	<div class="cart-summary">
		<div class="summary-row">
			<span>Товаров:</span>
			<span><?= $total_items ?></span>
		</div>

		<div class="summary-row">
			<span>Общая стоимость:</span>
			<span><?= $total_price ?> руб.</span>
		</div>

		<div class="summary-row summary-total">
			<span>Итого:</span>
			<span><?= $total_price ?> руб.</span>
		</div>

	<?php if (isset($_SESSION['user'])): ?>
		<form method="POST">
			<input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">
			<button type="submit" name="checkout" class="checkout-btn">
			Оформить заказ
			</button>
		</form>
	<?php else: ?>
	<?php endif; ?>
	</div>
	<?php endif; ?>
</main>

<?php require 'includes/footer.php'; ?>

</body>
</html>