<?php
$appUrl = rtrim($_ENV['APP_URL'] ?? '/ite3', '/');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>DevBlog CMS</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="<?= htmlspecialchars($appUrl) ?>/public/css/style.css">
    </head>
    
    <body>
        <nav>
            <a href="<?= htmlspecialchars($appUrl) ?>/home" class="logo">Devblog CMS</a>
            <div>
                <a href="<?= htmlspecialchars($appUrl) ?>/home">Home</a>
                <a href="<?= htmlspecialchars($appUrl) ?>/posts/create">Create Post</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="<?= htmlspecialchars($appUrl) ?>/logout">Logout</a>
                <?php else: ?>
                <a href="<?= htmlspecialchars($appUrl) ?>/login">Login</a>
                <?php endif; ?>
            </div>            
        </nav>

        <div id="toast-container"></div>

        <div id="deleteModal" class="modal-overlay">
            <div class="modal-content">
                <h3>Are you sure?</h3>
                <p>This action cannot be undone.</p>
                <div class="modal-btns">
                    <button id="cancelDelete" class="btn btn-secondary">Cancel</button>
                    <button id="confirmDelete" class="btn" style="background: #ef4444">Delete</button>
                </div>
            </div>
        </div>

        <div class="container">
            <main>
                <?php echo $content; ?>
            </main>
            <footer>
            <p>&copy; <?php echo date('Y'); ?> DevBlog CMS. Capstone Model.</p>
            </footer>
        </div>

        <script src="<?= htmlspecialchars($appUrl) ?>/public/js/app.js"></script>

        <?php if (isset($_SESSION['flash'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    showToast(<?= json_encode($_SESSION['flash']) ?>);
                });
            </script>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>
    </body>
</html>
