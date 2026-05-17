<h1>Admin Login</h1>
<p>Please enter your credentials to access the dashboard.</p>

<?php if (isset($errors['auth'])): ?>
    <p style="color: red; font-weight: bold;"><?= htmlspecialchars($errors['auth']) ?></p>
<?php endif; ?>

<form action="/ite3/login" method="POST" style="max-width: 400px;">
    <div style="margin-bottom: 1rem;">
        <label>Username:</label><br>
        <input 
            type="text" 
            name="username" 
            style="width: 100%; padding: 0.5rem;" 
            required>
            <?php if (isset($errors['username'])): ?>
                <span style="color: red; font-size: 0.8rem;"><?= htmlspecialchars($errors['username']) ?></span>
            <?php endif; ?>
    </div>

    <div style="margin-bottom: 1rem;">
        <label>Password:</label><br>
        <input 
            type="password" 
            name="password" 
            style="width: 100%; padding: 0.5rem;"
            required>
            <?php if (isset($errors['password'])): ?>
                <span style="color: red; font-size: 0.8rem;"><?= htmlspecialchars($errors['password']) ?></span>
            <?php endif; ?>
    </div>
    <button type="submit">Login</button>
</form>
