<?php
require_once 'includes/security.php';
require_once 'config.php';

include 'data/books.php';

if (!isset($_SESSION['cart'])) {
	$_SESSION['cart'] = [];
}

/* добавление книги в корзину */
if (isset($_POST['add_to_cart'])) {

	if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf_token']) {
		die("CSRF validation failed");
	}

	$book_id = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;

	if (isset($books[$book_id])) {

		if (!isset($_SESSION['cart'][$book_id])) {
			$_SESSION['cart'][$book_id] = 0;
		}

		$_SESSION['cart'][$book_id]++;

		header('Location: catalog.php?added=' . $book_id);
		exit();
	}
}

/* фильтрация книг */
$filtered_books = $books;
$genre = $_GET['genre'] ?? 'all';
if ($genre !== 'all') {
	$filtered_books = array_filter($filtered_books, function($book) use ($genre) {
		return isset($book['genre']) && $book['genre'] === $genre;
	});
}


/* поиск */
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
	$search = mb_strtolower($search);
	$filtered_books = array_filter($filtered_books, function($book) use ($search) {
		$title = isset($book['title']) ? mb_strtolower($book['title']) : '';
		$author = isset($book['author']) ? mb_strtolower($book['author']) : '';

		return strpos($title, $search) !== false ||
			   strpos($author, $search) !== false;
	});
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Каталог - Книжный Ларец</title>
	<link rel="stylesheet" href="styles/style.css">
</head>

<body>
<?php require 'includes/header.php'; ?>
<main>
<?php if (isset($_SESSION['user'])): ?>

<div class="user-notice">
	<p>🛒При выходе корзина будет очищена!</p>
</div>

<?php endif; ?>

<div class="filters">
    <form method="GET" class="filter-form">
        <select name="genre" onchange="this.form.submit()">
            <option value="all">Все жанры</option>
            <option value="Роман" <?= $genre === 'Роман' ? 'selected' : '' ?>>
                Художественная литература
            </option>
            <option value="Фэнтези" <?= $genre === 'Фэнтези' ? 'selected' : '' ?>>
                Фэнтези
            </option>
            <option value="Детектив" <?= $genre === 'Детектив' ? 'selected' : '' ?>>
                Детективы
            </option>
            <option value="Научно-популярная литература" <?= $genre === 'Научно-популярная литература' ? 'selected' : '' ?>>
                Научная литература
            </option>
        </select>

    <input
        type="text"
        name="search"
        placeholder="Поиск по названию или автору..."
        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
    >
        <button type="submit">
            Найти
        </button>
    </form>
</div>

<?php if (isset($_GET['added'])): ?>
    <div class="success-message">
        Книга добавлена в корзину!
    </div>
<?php endif; ?>

<h1>Каталог книг</h1>
<div class="books-grid">

<?php if (empty($filtered_books)): ?>

<p style="grid-column:1/-1;text-align:center;padding:40px;">
	Книги не найдены
</p>

<?php else: ?>
<?php foreach ($filtered_books as $id => $book): ?>

<div class="book-card">
    <a href="book.php?id=<?= $id ?>" class="book-link">
    <div class="book-image">
        <img
            src="img/<?= htmlspecialchars($book['image'] ?? 'default.jpg') ?>"
            alt="<?= htmlspecialchars($book['title'] ?? 'Книга') ?>"
            onerror="this.src='img/default.jpg'"
        >
</div>

<div class="book-info">
    <div class="book-title">
        <?= htmlspecialchars($book['title'] ?? '') ?>
    </div>
    <div class="book-author">
        <?= htmlspecialchars($book['author'] ?? '') ?>
    </div>
    <div class="book-price">
        <?= number_format($book['price'] ?? 0, 0, '', ' ') ?> ₽
    </div>
</div>
</a>

<form method="POST" class="add-to-cart-form">
    <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">
    <input type="hidden" name="book_id" value="<?= $id ?>">
    <button type="submit" name="add_to_cart" class="add-to-cart">
        В корзину
    </button>
</form>

</div>

<?php endforeach; ?>
<?php endif; ?>

</div>
</main>

<?php require 'includes/footer.php'; ?>

</body>
</html>