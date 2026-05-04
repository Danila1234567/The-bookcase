<?php

require_once 'includes/security.php';
require_once 'config.php';

session_start();
include 'data/books.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: catalog.php');
    exit();
}

$book_id = (int)$_GET['id'];

if (!isset($books[$book_id])) {
    header('Location: catalog.php');
    exit();
}

$book = $books[$book_id];

// Обработка добавления в корзину
if (isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    if (!isset($_SESSION['cart'][$book_id])) {
        $_SESSION['cart'][$book_id] = 0;
    }
    $_SESSION['cart'][$book_id]++;
    
    header('Location: book.php?id=' . $book_id . '&added=1');
    exit();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?> - Читай-Город</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>

<?php require 'includes/header.php'; ?>

<main class="book-detail">
    <button class="back-link" onclick="location.href='catalog.php'">← Назад к каталогу</button>

    <?php if (isset($_GET['added'])): ?>
        <div class="success-message">Книга добавлена в корзину!</div>
    <?php endif; ?>

    <div class="book-detail-grid">
        <div class="book-detail-image">
            <img src="img/<?php echo $book['image']; ?>" 
                 alt="<?php echo htmlspecialchars($book['title']); ?>" 
                 onerror="this.src='img/default.jpg'">
        </div>

        <div class="book-detail-info">
            <h1><?php echo htmlspecialchars($book['title']); ?></h1>
            <div class="book-author"><?php echo htmlspecialchars($book['author']); ?></div>
            <div class="book-price"><?php echo $book['price']; ?> руб.</div>
            
            <?php if (!empty($book['description'])): ?>
                <div class="book-description">
                    <h3>Описание</h3>
                    <p><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                </div>
            <?php else: ?>
                <p><em>Подробное описание отсутствует</em></p>
            <?php endif; ?>

            <?php if (!empty($book['genre'])): ?>
                <p><strong>Жанр:</strong> <?php echo htmlspecialchars($book['genre']); ?></p>
            <?php endif; ?>

            <form method="POST" class="add-to-cart-form">
                <button type="submit" name="add_to_cart" class="add-to-carts">Добавить в корзину</button>
            </form>
        </div>
    </div>
</main>

<?php require 'includes/footer.php'; ?>

</body>
</html>