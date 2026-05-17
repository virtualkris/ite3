<article style="max-width: 700px;">
    <h1><?= htmlspecialchars($post['title']) ?></h1>
    <small style="color: var(--primary);">/<?= htmlspecialchars($post['slug']) ?></small>

    <div style="margin-top: 1.5rem;">
        <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
    </div>

    <p style="margin-top: 1.5rem;">
        <small>Posted on: <?= htmlspecialchars($post['created_at']) ?></small>
    </p>
</article>

<hr>
<a href="/ite3/home">Back to Home</a>
