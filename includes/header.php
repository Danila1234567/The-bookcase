<header>
	<nav>
		<div class="logo">Книжный Ларец</div>
		<button class="burger" id="burger">
			<span></span>
			<span></span>
			<span></span>
		</button>
		<ul class="nav-links" id="navLinks">
			<li>
				<a href="index.php" class="nav-link">Главная</a>
			</li>
			<li>
				<a href="catalog.php" class="nav-link">Каталог</a>
			</li>
			<li>
				<a href="cart.php" class="nav-link">
					Корзина
					<span class="cart-count">
						<?= count($_SESSION['cart'] ?? []) ?>
					</span>
				</a>
			</li>
			<?php if (isset($_SESSION['user'])): ?>
			<li class="user-menu">
				<span class="username">
					<?= htmlspecialchars($_SESSION['user']['username']) ?>
				</span>

				<form method="POST" action="logout.php" style="display:inline;">
					<input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">
					<button type="submit" class="nav-link logout">
						Выйти
					</button>
				</form>
			</li>
			<?php else: ?>
				<li>
					<a href="login.php" class="nav-link">Войти</a>
				</li>
				<li>
					<a href="register.php" class="nav-link">Регистрация</a>
				</li>
			<?php endif; ?>
		</ul>
	</nav>
</header>