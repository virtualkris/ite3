<h1>Create a New Post</h1>
<form action="/ite3/posts" method="POST">
    <div>
        <label for="title">Title:</label><br>
        <input type="text" id="title" name="title" required>
    </div>
    <div>
        <label for="content">Content:</label><br>
        <textarea id="content" name="content" rows="5" required></textarea>
    </div>
    <button type="submit">Create Post</button>
</form>