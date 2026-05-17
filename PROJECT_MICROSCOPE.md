# DevBlog CMS Project Microscope

Last updated: 2026-05-17

This document explains how the current DevBlog CMS project fits together after the latest improvements: dotenv configuration, cleaner routing, slug-based post URLs, slug-based edit URLs, login validation, flash messages, toast notifications, and the updated frontend assets.

## 1. Project Shape

The project is a small custom PHP MVC application.

```text
Browser request
  -> public/index.php
  -> app/routes.php
  -> app/Core/Router.php
  -> app/Controllers/*
  -> app/Models/*
  -> app/Views/*
  -> app/Views/layouts/main.php
  -> browser response
```

Main responsibilities:

- `public/index.php`: front controller; every app request should pass through here.
- `app/routes.php`: maps URLs to controller methods.
- `app/Core/Router.php`: matches the current URL and calls the correct controller action.
- `app/Controllers`: request logic, validation, redirects, and rendering.
- `app/Models`: database queries.
- `app/Views`: page-specific HTML.
- `app/Views/layouts/main.php`: shared layout, navigation, CSS/JS loading, modal, toast container.
- `app/Helpers/Validator.php`: reusable validation helper.
- `app/Services/PostService.php`: slug generation.
- `app/Config/database.php`: PDO database connection.
- `.env`: local environment values such as app URL and database credentials.
- `.env.example`: template showing required environment values.

## 2. Environment Configuration

The app now loads environment variables through `vlucas/phpdotenv`.

In `public/index.php`:

```php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();
```

Important `.env` values:

```env
APP_URL=http://localhost/ite3
APP_BASE_PATH=ite3

DB_HOST=localhost
DB_NAME=devblog_db
DB_USER=root
DB_PASS=
```

How they are used:

- `APP_URL` is used in `app/Views/layouts/main.php` to build navigation links and asset URLs.
- `APP_BASE_PATH` is used in `public/index.php` to strip `/ite3` from the request path before route matching.
- Database variables are used by `app/Config/database.php`.

In `main.php`:

```php
$appUrl = rtrim($_ENV['APP_URL'] ?? '/ite3', '/');
```

Then links are built like:

```php
<a href="<?= htmlspecialchars($appUrl) ?>/home">Home</a>
```

This makes deployment easier later. For a real website, update `.env`:

```env
APP_URL=https://example.com
APP_BASE_PATH=
```

## 3. Entry Points

### Root `index.php`

File:

```text
index.php
```

Current job:

```php
header("Location: public/");
exit;
```

When visiting:

```text
http://localhost/ite3
```

this file redirects the browser to:

```text
http://localhost/ite3/public/
```

If you want `/ite3` to go directly to `/ite3/home`, this root redirect can be changed later.

### `public/index.php`

File:

```text
public/index.php
```

Current job:

1. Starts the PHP session.
2. Loads Composer autoloading.
3. Loads `.env`.
4. Enables debug display when `APP_DEBUG=true`.
5. Creates the router.
6. Loads `app/routes.php`.
7. Reads the browser URL.
8. Removes `APP_BASE_PATH`.
9. Converts empty routes to `home`.
10. Dispatches the route.

Important routing cleanup:

```php
$basePath = trim($_ENV['APP_BASE_PATH'] ?? 'ite3', '/');
if (!empty($basePath)) {
    $uri = preg_replace("#^" . preg_quote($basePath) . "/?#", '', $uri);
}
```

This is better than hardcoding `ite3/` in the URI parser.

## 4. Current Routes

File:

```text
app/routes.php
```

Current routes:

```php
$router->get('home', 'PostController@index');
$router->get('post/{slug}', 'PostController@show');
$router->get('posts/create', 'PostController@create');
$router->post('posts', 'PostController@store');
$router->get('posts/edit/{slug}', 'PostController@edit');
$router->post('posts/update', 'PostController@update');
$router->get('posts/delete/{id}', 'PostController@delete');
$router->post('posts/delete', 'PostController@delete');
$router->get('login', 'AuthController@showLoginForm');
$router->post('login', 'AuthController@login');
$router->get('logout', 'AuthController@logout');
```

Important current URL examples:

```text
GET  /ite3/home
GET  /ite3/post/my-first-post
GET  /ite3/posts/create
POST /ite3/posts
GET  /ite3/posts/edit/my-first-post
POST /ite3/posts/update
GET  /ite3/posts/delete/5
GET  /ite3/login
POST /ite3/login
GET  /ite3/logout
```

## 5. Router Improvements

File:

```text
app/Core/Router.php
```

The router now safely handles missing HTTP method groups:

```php
foreach ($this->routes[$method] ?? [] as $route => $controller) {
```

This avoids warnings if the request method has no registered routes.

Dynamic route placeholders now allow hyphenated slugs:

```php
$pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_-]+)', $route);
```

This is required for URLs like:

```text
/ite3/post/my-first-post
/ite3/posts/edit/my-first-post
```

Without `-` in the regex, those routes would not match.

## 6. Slug System

Slug support is one of the major recent additions.

### Slug Generation

File:

```text
app/Services/PostService.php
```

Current method:

```php
public static function generateSlug($title) {
    $slug = strtolower(trim($title));
    $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}
```

Example:

```text
My First Post!
```

becomes:

```text
my-first-post
```

### Database Storage

File:

```text
app/Models/Post.php
```

Posts are created with title, slug, and content:

```php
INSERT INTO posts (title, slug, content) VALUES (?, ?, ?)
```

Posts are updated with title, slug, and content:

```php
UPDATE posts SET title = ?, slug = ?, content = ? WHERE id = ?
```

There is also a slug lookup method:

```php
public function findBySlug($slug) {
    $stmt = $this->db->prepare("SELECT * FROM posts WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}
```

Important database note:

The `posts` table needs a `slug` column. Ideally, that column should be unique:

```sql
ALTER TABLE posts ADD UNIQUE (slug);
```

If two posts have the same title, they currently generate the same slug. A future improvement is to add automatic suffixes like `my-post-2`.

## 7. Public Post URLs

Public post reading now uses slugs.

Route:

```php
$router->get('post/{slug}', 'PostController@show');
```

Controller:

```php
public function show($slug) {
    $postModel = new Post();
    $post = $postModel->findBySlug($slug);

    if (!$post) {
        http_response_code(404);
        echo "Post not found.";
        return;
    }

    $this->render('post-show', [
        'title' => $post['title'],
        'post' => $post
    ]);
}
```

View:

```text
app/Views/post-show.php
```

Example URL:

```text
/ite3/post/my-first-post
```

The home page links each post title to its slug URL:

```php
<a href="/ite3/post/<?= htmlspecialchars($post['slug']) ?>">
```

## 8. Edit Post By Slug

Edit URLs also now use slugs.

Route:

```php
$router->get('posts/edit/{slug}', 'PostController@edit');
```

Controller:

```php
public function edit($slug) {
    $this->checkAuth();

    $postModel = new Post();
    $post = $postModel->findBySlug($slug);

    if (!$post) {
        http_response_code(404);
        echo "Post not found.";
        return;
    }

    $this->render('post-edit', [
        'title' => 'Edit Post',
        'post' => $post
    ]);
}
```

Home page edit link:

```php
<a href="/ite3/posts/edit/<?= htmlspecialchars($post['slug']) ?>">Edit</a>
```

Example URL:

```text
/ite3/posts/edit/my-first-post
```

Important design choice:

The edit page is opened by slug, but the edit form still submits the hidden numeric `id`.

```php
<input type="hidden" name="id" value="<?= htmlspecialchars($post['id']) ?>">
```

That is good because the title can change during an update. If the title changes, the slug changes too, but the numeric `id` still identifies the correct database row.

## 9. Post Controller Flow

File:

```text
app/Controllers/PostController.php
```

Current actions:

- `index()`: lists all posts.
- `show($slug)`: displays one public post by slug.
- `create()`: shows create form; login required.
- `store()`: validates input, generates slug, creates post; login required.
- `edit($slug)`: shows edit form by slug; login required.
- `update()`: validates input, regenerates slug, updates by id; login required.
- `delete($id = null)`: deletes by id; login required.

Protected actions call:

```php
$this->checkAuth();
```

If the user is not logged in:

```php
header('Location: /ite3/login');
exit;
```

## 10. Post Model

File:

```text
app/Models/Post.php
```

Current methods:

```php
all()
find($id)
findBySlug($slug)
create($title, $slug, $content)
update($id, $title, $slug, $content)
delete($id)
```

Model methods use prepared statements for user-provided values:

```php
$stmt = $this->db->prepare("SELECT * FROM posts WHERE slug = ?");
$stmt->execute([$slug]);
```

This is safer than concatenating user input into SQL.

## 11. Views

### Layout

File:

```text
app/Views/layouts/main.php
```

Current responsibilities:

- Defines `$appUrl` from `APP_URL`.
- Loads Google Fonts.
- Loads `public/css/style.css`.
- Shows nav links.
- Switches between Login and Logout based on `$_SESSION['user_id']`.
- Provides the delete confirmation modal.
- Provides the toast container.
- Renders the page-specific `$content`.
- Loads `public/js/app.js`.
- Shows flash messages as toasts.

Flash message output uses JSON encoding:

```php
showToast(<?= json_encode($_SESSION['flash']) ?>);
```

This is safer than placing raw text inside JavaScript quotes.

### Home

File:

```text
app/Views/home.php
```

Current responsibilities:

- Shows all posts.
- Links post titles to `/ite3/post/{slug}`.
- Links edit actions to `/ite3/posts/edit/{slug}`.
- Links delete actions to `/ite3/posts/delete/{id}`.
- Uses `htmlspecialchars()` or `htmlentities()` for output safety.
- Applies `.reveal` for scroll animation.

### Create

File:

```text
app/Views/create.php
```

Current responsibilities:

- Displays the create-post form.
- Submits to `POST /ite3/posts`.
- Refills old input after validation errors.
- Displays validation errors.

### Edit

File:

```text
app/Views/post-edit.php
```

Current responsibilities:

- Displays the edit form.
- Shows the current slug.
- Submits to `POST /ite3/posts/update`.
- Sends hidden `id` for reliable database updates.
- Displays validation errors.

### Public Post

File:

```text
app/Views/post-show.php
```

Current responsibilities:

- Displays one post.
- Shows its slug.
- Preserves line breaks in content with `nl2br()`.
- Links back to home.

### Login

File:

```text
app/Views/login.php
```

Current responsibilities:

- Displays username and password fields.
- Shows validation errors.
- Shows invalid-login error.
- Submits to `POST /ite3/login`.

## 12. Auth Flow

File:

```text
app/Controllers/AuthController.php
```

Current routes:

```php
$router->get('login', 'AuthController@showLoginForm');
$router->post('login', 'AuthController@login');
$router->get('logout', 'AuthController@logout');
```

Opening the login page:

```php
public function showLoginForm() {
    return $this->render('login');
}
```

Submitting login:

```php
Validator::required('username', $username);
Validator::required('password', $password);
```

User lookup:

```php
$userModel = new User();
$user = $userModel->findByUsername($username);
```

Password check:

```php
password_verify($password, $user['password'])
```

Successful login stores:

```php
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
```

Then redirects:

```php
header('Location: /ite3/home');
exit;
```

## 13. Validation

File:

```text
app/Helpers/Validator.php
```

Current methods:

```php
required($fieldName, $value)
minLength($fieldName, $value, $min)
numeric($fieldName, $value)
getErrors()
hasErrors()
clearErrors()
```

Recent improvement:

`hasErrors()` now exists:

```php
public static function hasErrors() {
    return !empty(self::$errors);
}
```

The existing test file verifies numeric and required validation:

```text
tests/ValidatorTest.php
```

## 14. Database Layer

File:

```text
app/Config/database.php
```

The app now reads database settings from `.env`:

```php
$host = $_ENV['DB_HOST'] ?? 'localhost';
$db = $_ENV['DB_NAME'] ?? 'devblog_db';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? '';
```

PDO is configured with:

```php
\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
\PDO::ATTR_EMULATE_PREPARES => false
```

This means database errors throw exceptions, fetched rows are associative arrays, and prepared statements are handled safely.

## 15. Frontend Assets

### CSS

File:

```text
public/css/style.css
```

Current features:

- CSS variables for colors, borders, shadows, and radius.
- Responsive `.container`.
- Navigation styling.
- Post card styling.
- Delete modal styling.
- Toast notification styling with a shrinking progress bar.
- Scroll reveal animation.
- Mobile media query.

Toast progress bar styling:

```css
.toast {
  position: relative;
  overflow: hidden;
}

.toast-progress {
  width: 100%;
  transition: width 3s linear;
}
```

The progress bar starts at full width and transitions to `0%` over the same 3 seconds that the toast stays visible.

### JavaScript

File:

```text
public/js/app.js
```

Current features:

- Initializes scroll reveal animations.
- Initializes delete confirmation modal.
- Provides `showToast(message)`.
- Creates a `.toast-progress` element inside each toast.
- Uses JavaScript to set the progress width to `0%`.
- Guards against missing DOM elements.

Toast progress bar flow:

```js
const progress = document.createElement("div");
progress.className = "toast-progress";

toast.appendChild(progress);

requestAnimationFrame(() => {
  progress.style.width = "0%";
});
```

`requestAnimationFrame()` gives the browser one moment to paint the progress bar at `100%` first. Then JavaScript changes the width to `0%`, and CSS handles the smooth countdown animation.

Delete links have class:

```html
class="delete delete-btn"
```

JavaScript intercepts those clicks, opens the modal, and only continues to the delete URL when the user confirms.

## 16. Flash Messages

Post create, update, and delete actions set flash messages:

```php
$_SESSION['flash'] = "Success! Action completed.";
```

The layout checks for the flash message:

```php
<?php if (isset($_SESSION['flash'])): ?>
```

Then displays it through JavaScript:

```php
showToast(<?= json_encode($_SESSION['flash']) ?>);
```

Then removes it:

```php
unset($_SESSION['flash']);
```

This makes the message appear once after a redirect.

The toast also includes a progress bar that shrinks during the 3-second countdown. The timing is shared between JavaScript and CSS:

```js
setTimeout(() => {
  toast.style.opacity = "0";
  setTimeout(() => toast.remove(), 500);
}, 3000);
```

```css
.toast-progress {
  transition: width 3s linear;
}
```

So the toast begins fading out when the progress bar reaches empty.

## 17. Important Current Flows

### List Posts

```text
GET /ite3/home
  -> PostController@index
  -> Post::all()
  -> home.php
  -> layouts/main.php
```

### Read One Post By Slug

```text
GET /ite3/post/my-first-post
  -> PostController@show('my-first-post')
  -> Post::findBySlug('my-first-post')
  -> post-show.php
  -> layouts/main.php
```

### Create Post

```text
GET /ite3/posts/create
  -> PostController@create
  -> checkAuth()
  -> create.php
```

```text
POST /ite3/posts
  -> PostController@store
  -> checkAuth()
  -> Validator::required()
  -> PostService::generateSlug()
  -> Post::create()
  -> flash message
  -> redirect /ite3/home
```

### Edit Post By Slug

```text
GET /ite3/posts/edit/my-first-post
  -> PostController@edit('my-first-post')
  -> checkAuth()
  -> Post::findBySlug()
  -> post-edit.php
```

```text
POST /ite3/posts/update
  -> PostController@update
  -> checkAuth()
  -> update by hidden id
  -> regenerate slug from title
  -> flash message
  -> redirect /ite3/home
```

### Delete Post

```text
GET /ite3/posts/delete/5
  -> PostController@delete(5)
  -> checkAuth()
  -> Post::delete(5)
  -> flash message
  -> redirect /ite3/home
```

### Login

```text
GET /ite3/login
  -> AuthController@showLoginForm
  -> login.php
```

```text
POST /ite3/login
  -> AuthController@login
  -> Validator checks username/password
  -> User::findByUsername()
  -> password_verify()
  -> set session
  -> redirect /ite3/home
```

## 18. Recent Fixes And Improvements

Recent project improvements include:

- Fixed a parse error in `post-edit.php`.
- Fixed route mismatches such as `/post/edit` vs `/posts/edit`.
- Fixed create form action to submit to `POST /ite3/posts`.
- Added slug generation for create and update.
- Added public post view by slug.
- Added edit-by-slug URLs.
- Updated router dynamic matching to allow hyphenated slugs.
- Fixed `AuthController` construction and login validation.
- Fixed `User` model to rely on the base `Model` database connection.
- Added `Validator::hasErrors()`.
- Added dotenv loading through Composer autoload.
- Added `APP_URL` usage in the main layout navigation and assets.
- Restored `.env.example`.
- Added toast notifications for flash messages.
- Added a shrinking progress bar to toast notifications.
- Added delete confirmation modal behavior.
- Added scroll reveal animation.
- Improved output escaping in views.
- Added safer JSON output for flash messages inside JavaScript.

## 19. Current Known Follow-Up Ideas

Useful next improvements:

- Replace remaining hardcoded `/ite3/...` links in views and controller redirects with an app URL helper.
- Add unique-slug handling so duplicate titles become `my-post`, `my-post-2`, `my-post-3`.
- Prefer POST for delete actions instead of deleting through GET links.
- Add a migration or SQL setup file for the `posts` and `users` tables.
- Add tests for slug generation and router matching.
- Add a 404 view instead of echoing plain text.
- Make `APP_BASE_PATH` empty-friendly for real production domains.

## 20. Best Study Order

Read the project in this order:

1. `public/index.php`
2. `.env`
3. `app/routes.php`
4. `app/Core/Router.php`
5. `app/Controllers/Controller.php`
6. `app/Controllers/PostController.php`
7. `app/Services/PostService.php`
8. `app/Models/Model.php`
9. `app/Config/database.php`
10. `app/Models/Post.php`
11. `app/Views/layouts/main.php`
12. `app/Views/home.php`
13. `app/Views/post-show.php`
14. `app/Views/create.php`
15. `app/Views/post-edit.php`
16. `app/Controllers/AuthController.php`
17. `app/Models/User.php`
18. `app/Views/login.php`
19. `public/js/app.js`
20. `public/css/style.css`

Core idea:

```text
Routes choose the controller.
Controllers coordinate validation, models, redirects, and views.
Models talk to the database.
Views display escaped output.
The layout wraps everything.
Services hold reusable business logic like slug generation.
```
