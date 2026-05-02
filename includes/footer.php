<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';
?>
    </main>
    <footer class="main-footer">
        <div class="footer-container">
            <p>&copy; <?php echo date('Y'); ?> ElectroMart. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
