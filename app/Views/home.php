<h1><?= htmlspecialchars($title) ?></h1>
<!-- <a href="/ite3/posts/create">Create a New Post</a> -->
<hr>

<?php foreach ($posts as $post): ?>
    <div>
        <h3><strong><?= htmlspecialchars($post['title']) ?></strong></h3>
        <small>/<?= htmlspecialchars($post['slug']) ?></small>
        <p><?= htmlspecialchars($post['content']) ?></p>
        <a href="/ite3/posts/edit/<?= $post['id'] ?>">Edit</a>
        <a href="/ite3/posts/delete/<?= $post['id'] ?>" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
        <small>Created at: <?= htmlspecialchars($post['created_at']) ?></small>
    </div>
<?php endforeach; ?>
