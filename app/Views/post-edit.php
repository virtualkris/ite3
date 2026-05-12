<form action="/ite3/posts/update" method="POST">
    <input type="hidden" name="id" value="<?= $post['id'] ?>">
    <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
    <?php if (isset($errors['title'])): ?>
        <span style="color: red;"><?= htmlspecialchars($errors['title']) ?></span>
    <?php endif; ?>
    <textarea name="content" required><?= htmlspecialchars($post['content']) ?></textarea>
    <?php if (isset($errors['content'])): ?>
        <span style="color: red;"><?= htmlspecialchars($errors['content']) ?></span>
    <?php endif; ?>
    <button type="submit">Update Post</button>
</form>
