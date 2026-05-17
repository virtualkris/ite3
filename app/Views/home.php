<h1>Welcome to the Blog</h1>
<p>Modernised UI with external assets.</p>
<!-- <a href="/ite3/posts/create">Create a New Post</a> -->
<hr>

<div style="margin-top: 2rem;">
    <?php if (empty($posts)): ?>
        <p>No posts found.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($posts as $post): ?>
                <li class="reveal">
                    <strong>
                        <a href="/ite3/post/<?= htmlspecialchars($post['slug']) ?>">
                            <?= htmlentities($post['title']) ?>
                        </a>
                    </strong>
                    <small style="color: var(--primary);">/<?= htmlspecialchars($post['slug']) ?></small>
                    <p><?= htmlspecialchars($post['content']) ?></p>
                    <small>Posted on: <?= htmlspecialchars($post['created_at']) ?></small>

                    <div class="actions">
                        <a href="/ite3/posts/edit/<?= htmlspecialchars($post['slug']) ?>">Edit</a>
                        <a href="/ite3/posts/delete/<?= htmlspecialchars($post['id']) ?>" class="delete delete-btn">Delete</a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
