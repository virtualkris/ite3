<h1>Edit Post</h1>

<form action="/ite3/posts/update" method="POST" style="max-width: 600px;">
    <input 
        type="hidden" 
        name="id" 
        value="<?= htmlspecialchars($post['id']) ?>">
    
    <div style="margin-bottom: 1rem;">
        <label>Title:</label><br>
        <input 
            type="text"
            name="title"
            value="<?= htmlspecialchars($post['title']) ?>"
            style="width: 100%; padding: 0.5rem;"
            required>
        <small>Current Slug: <strong><?= htmlspecialchars($post['slug']) ?></strong> (Will be updated based on the title)
        </small>
        <?php if (isset($errors['title'])): ?>
            <span style="color: red; font-size: 0.8rem;"><?= htmlspecialchars($errors['title']) ?></span>
        <?php endif; ?>
    </div>

    <div style="margin-bottom: 1rem;">
        <label>Content:</label><br>
        <textarea name="content" rows="5" style="width: 100%; padding: 0.5rem;" required><?= htmlspecialchars($post['content']) ?></textarea>
        <?php if(isset($errors['content'])): ?>
            <span style="color: red; font-size: 0.8rem;"><?= htmlspecialchars($errors['content']) ?></span>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn">Update Post</button>
</form>

<hr>
<a href="/ite3/home">Cancel</a>
