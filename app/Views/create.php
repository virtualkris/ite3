<h1>Create a New Post</h1>
<form action="/ite3/posts" method="POST">
    <div>
        <label for="title">Title:</label><br>
        <input type="text" id="title" name="title" value="<?= isset($old['title']) ? htmlspecialchars($old['title']) : '' ?>" required>
        <?php if (isset($errors['title'])): ?>
            <span style="color: red;"><?= $errors['title'] ?></span>
        <?php endif; ?>
    </div>
    <div>
        <label for="content">Content:</label><br>
        <textarea id="content" name="content" rows="5" required><?= isset($old['content']) ? htmlspecialchars($old['content']) : '' ?></textarea>
        <?php if (isset($errors['content'])): ?>
            <span style="color: red;"><?= $errors['content'] ?></span>
        <?php endif; ?>
    </div>
    <button type="submit">Create Post</button>
</form>