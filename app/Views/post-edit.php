<form action="/ite3/posts/update" method="POST">
    <input type="hidden" name="id" value="<?= $post['id'] ?>">
    <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
    <textarea name="content" required><?= htmlspecialchars($post['content']) ?></textarea>
    <button type="submit">Update Post</button>
</form>