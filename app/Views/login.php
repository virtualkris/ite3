<h1><?= htmlspecialchars($title) ?></h1>

<?php if (!empty($error)): ?>
    <p style="color: red;">
        <?= htmlspecialchars($error) ?>
    </p>
<?php endif; ?>

<form action="/ite3/login" method="POST">
    <div>
        <label for="username">Username:</label><br>
        <input type="text" id="username" name="username" required>
    </div>
    <div>
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required>
    </div>
    <button type="submit">Login</button>
</form>