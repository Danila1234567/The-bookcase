<?php
require_once 'includes/security.php';
require_once 'config.php';
include 'data/books.php';


// Проверка авторизации администратора
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    $_SESSION['admin_logged_in'] = false;
}

// Вход администратора
if (isset($_POST['admin_login'])) {
    $login    = trim($_POST['admin_login'] ?? '');
    $password = trim($_POST['admin_password'] ?? '');

    if ($login === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php');
        exit();
    } else {
        $admin_error = "Неверный логин или пароль";
    }
}

// Выход
if (isset($_GET['logout'])) {
    $_SESSION['admin_logged_in'] = false;
    header('Location: admin.php');
    exit();
}

// Форма входа, если не авторизован
if (!$_SESSION['admin_logged_in']) {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Вход в админ-панель — Книжный Ларец</title>
        <link rel="stylesheet" href="styles/style.css">
    </head>
    <body>
        <div class="admin-login-container">
            <div class="admin-login-form">
                <h1>Вход в админ-панель</h1>
                <?php if (isset($admin_error)): ?>
                    <div class="error-message"><?= htmlspecialchars($admin_error) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="text" class='adm_pole' name="admin_login" placeholder="Логин" required>
                    <input type="password" class='adm_pole' name="admin_password" placeholder="Пароль" required>
                    <button type="submit" class="auth-btn">Войти</button>
                </form>
                <a href="index.php" class="back-btn">← На главную</a>
            </div>
        </div>
    </body>
    </html>
<?php
    exit();
}

// Подключение к базе
try {
    $db = (new Database())->getConnection();

    // Все отзывы
    $stmt = $db->query("
        SELECT id, author, email, text, 
               DATE_FORMAT(date, '%d.%m.%Y %H:%i') AS formatted_date,
               status
        FROM reviews 
        ORDER BY date DESC
    ");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Статистика
    $active_count = $db->query("SELECT COUNT(*) FROM reviews WHERE status = 'active'")->fetchColumn() ?: 0;
    $total_count  = $db->query("SELECT COUNT(*) FROM reviews")->fetchColumn() ?: 0;

} catch (PDOException $e) {
    $reviews = [];
    $admin_error = "Ошибка подключения к базе данных: " . $e->getMessage();
    $active_count = $total_count = 0;
}

// Обработка действий с отзывами
$success_msg = $error_msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['review_id'])) {
    $review_id = (int)$_POST['review_id'];

    try {
        if (isset($_POST['approve'])) {
            $stmt = $db->prepare("UPDATE reviews SET status = 'active' WHERE id = ?");
            $stmt->execute([$review_id]);
            $success_msg = "Отзыв одобрен";
        } 
        elseif (isset($_POST['reject'])) {
            $stmt = $db->prepare("UPDATE reviews SET status = 'rejected' WHERE id = ?");
            $stmt->execute([$review_id]);
            $success_msg = "Отзыв отклонён";
        } 
        elseif (isset($_POST['delete'])) {
            $stmt = $db->prepare("DELETE FROM reviews WHERE id = ?");
            $stmt->execute([$review_id]);
            $success_msg = "Отзыв удалён";
        } 
        elseif (isset($_POST['edit_review'])) {
            $text = trim($_POST['review_text'] ?? '');
            if (strlen($text) < 10) {
                $error_msg = "Текст отзыва слишком короткий (минимум 10 символов)";
            } else {
                $stmt = $db->prepare("UPDATE reviews SET text = ? WHERE id = ?");
                $stmt->execute([$text, $review_id]);
                $success_msg = "Отзыв отредактирован";
            }
        }

        if ($success_msg) {
            header("Location: admin.php?msg=success");
            exit();
        }
    } catch (PDOException $e) {
        $error_msg = "Ошибка при выполнении действия: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель — Книжный Ларец</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>

<header>
	<nav>
    <div class="logo">Админ-панель</div>
		<button class="burger" id="burger">
			<span></span>
			<span></span>
			<span></span>
		</button>

		<ul class="nav-links" id="navLinks">
			<li>
                <a href="index.php">На сайт</a>
			</li>
			<li>
                <a href="?logout=1" class="logout">Выйти</a>
			</li>
        </ul>
</header>

<main class="admin-container">
    <h1>Модерация отзывов</h1>

    <?php if (isset($admin_error)): ?>
        <div class="error-message"><?= htmlspecialchars($admin_error) ?></div>
    <?php endif; ?>

    <?php if (isset($error_msg)): ?>
        <div class="error-message"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'success' && $success_msg): ?>
        <div class="success-message"><?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>

    <p class='vsego'>Всего отзывов: <?= $total_count ?> | Одобрено: <?= $active_count ?></p>

    <?php if (empty($reviews)): ?>
        <p class="no-data">Пока нет отзывов</p>
    <?php else: ?>
        <div class="reviews-admin-list">
            <?php foreach ($reviews as $r): ?>
                <div class="review-admin-card">
                    <div class="review-header">
                        <div class="review-author-info">
                            <strong><?= htmlspecialchars($r['author']) ?></strong>
                            <?php if (!empty($r['email'])): ?>
                                <span class="review-email">(<?= htmlspecialchars($r['email']) ?>)</span>
                            <?php endif; ?>
                        </div>
                        <div class="review-date-status">
                            <div class="review-date"><?= htmlspecialchars($r['formatted_date']) ?></div>
                            <div class="review-status status-<?= htmlspecialchars($r['status']) ?>">
                                <?php
                                switch ($r['status']) {
                                    case 'active':
                                        echo 'Одобрен';
                                        break;
                                    case 'pending':
                                        echo 'Ожидает';
                                        break;
                                    case 'rejected':
                                        echo 'Отклонён';
                                        break;
                                    default:
                                        echo 'Неизвестно';
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="review-text"><?= nl2br(htmlspecialchars($r['text'])) ?></div>

                    <div class="review-actions">
                        <?php if ($r['status'] !== 'active'): ?>
                            <form method="POST" class="action-form">
                                <input type="hidden" name="review_id" value="<?= $r['id'] ?>">
                                <button type="submit" name="approve" class="btn btn-approve">Одобрить</button>
                            </form>
                        <?php endif; ?>

                        <?php if ($r['status'] !== 'rejected'): ?>
                            <form method="POST" class="action-form">
                                <input type="hidden" name="review_id" value="<?= $r['id'] ?>">
                                <button type="submit" name="reject" class="btn btn-reject">Отклонить</button>
                            </form>
                        <?php endif; ?>

                        <form method="POST" class="action-form" onsubmit="return confirm('Удалить отзыв навсегда?');">
                            <input type="hidden" name="review_id" value="<?= $r['id'] ?>">
                            <button type="submit" name="delete" class="btn btn-delete">Удалить</button>
                        </form>

                        <button type="button" class="btn btn-edit"
                                onclick="openEditModal(<?= $r['id'] ?>, `<?= htmlspecialchars(addslashes($r['text']), ENT_QUOTES) ?>`)">
                            Редактировать
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Модальное окно редактирования  -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
            <h3>Редактирование отзыва</h3>
            <form method="POST">
                <input type="hidden" name="review_id" id="edit_id">
                <textarea name="review_text" id="edit_text" rows="7" required></textarea>
                <div class="modal-buttons">
                    <button type="button" class="btn btn-cancel" onclick="document.getElementById('editModal').style.display='none'">Отмена</button>
                    <button type="submit" name="edit_review" class="btn btn-save">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</main>

<footer>
    <p>Книжный Ларец © <?= date('Y') ?> | Админ-панель</p>
</footer>

<script src="/js/modelw.js"></script>
<script src="/js/burgermenu.js"></script>
</body>
</html>