<h1>Create a New Post</h1>

<form action="/ite3/posts" method="POST" style="max-width: 600px;">
    <div style="margin-bottom: 1rem;">
        <label for="title">Title:</label><br>
        <input 
            type="text" 
            id="title" 
            name="title" 
            value="<?= htmlspecialchars($old['title'] ?? '') ?>"
            style="width: 100%; padding: 0.5rem;"
            required>
        <?php if (isset($errors['title'])): ?>
            <span style="color: red; font-size: 0.8rem;"><?= htmlspecialchars($errors['title']) ?>
            </span>
        <?php endif; ?>
    </div>

    <div style="margin-bottom: 1rem;">
        <label for="content">Content:</label><br>
        <textarea 
            id="content" 
            name="content" 
            rows="5" 
            style="width: 100%; padding: 0.5rem;"
            required><?= htmlspecialchars($old['content'] ?? '') ?></textarea>
        <?php if (isset($errors['content'])): ?>
            <span style="color: red; font-size: 0.8rem;"><?= htmlspecialchars($errors['content']) ?>
            </span>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn">Publish Post</button>
</form>

<hr>
<a href="/ite3/home">Back to Home</a>
