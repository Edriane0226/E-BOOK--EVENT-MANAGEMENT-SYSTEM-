<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(to right, #5d0000, #ff3c3c);">
    <div class="container">
        <a class="navbar-brand fw-bold text-white" href="#">📅 EBOOK PLANNER</a>
        <div class="ms-auto">
            <?php if ($current_page !== 'dashboard.php'): ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="btn btn-outline-light">Logout</a>
                <?php else: ?>
                    <?php if ($current_page == 'login.php'): ?>
                        <a href="register.php" class="btn btn-outline-light">Register</a>
                    <?php elseif ($current_page == 'register.php'): ?>
                        <a href="login.php" class="btn btn-outline-light">Login</a>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</nav>
