<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>DevBlog CMS</title>
        <link rel="stylesheet" href="/ite3/public/css/style.css">
    </head>
    <body class="container">
        <nav>
            <a href="/ite3/home">Home</a>
            <a href="/ite3/posts/create">Create Post</a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/ite3/logout">Logout</a>
            <?php else: ?>
                <a href="/ite3/login">Login</a>
            <?php endif; ?>
        </nav>

        <main>
                <?php echo $content; ?>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> DevBlog CMS. Capstone Model.</p>
        </footer>
        <script src="/ite3/public/js/app.js"></script>
    </body>
</html>