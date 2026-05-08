# DevBlog CMS Project Microscope

This guide explains how the files in this project connect to each other. Think of the app as a small organism:

- `public/index.php` is the mouth/front door. Every browser request enters here.
- `app/Core/Router.php` is the nervous system. It decides which controller method should respond.
- Controllers in `app/Controllers` are the brain. They decide what to do.
- Models in `app/Models` are the hands that touch the database.
- Views in `app/Views` are the face. They produce the HTML the user sees.
- Helpers in `app/Helpers` are small reusable tools.
- `app/Config/database.php` is the database connection factory.
- `public/css` and `public/js` are browser assets loaded by the layout.

## 1. The Big Request Flow

When someone visits a page, the request usually moves like this:

```text
Browser URL
  -> public/index.php
  -> app/routes.php
  -> app/Core/Router.php
  -> a controller method
  -> a model if database data is needed
  -> a view
  -> app/Views/layouts/main.php
  -> HTML response back to the browser
```

Example: opening the create-post form.

```text
GET /ite3/posts/create
  -> public/index.php
  -> route: posts/create
  -> PostController@create
  -> checkAuth()
  -> render create.php
  -> wrap it in layouts/main.php
```

Example: submitting the create-post form.

```text
POST /ite3/posts
  -> public/index.php
  -> route: posts
  -> PostController@store
  -> Validator checks title/content
  -> Post model inserts into database
  -> redirect to /ite3/home
```

## 2. public/index.php

File:

```text
public/index.php
```

Function:

This is the front controller. In this project, all requests enter through this file. It starts the session, turns on error reporting, registers the autoloader, creates the router, loads your routes, and resolves the current URL.

Important snippet:

```php
session_start();
```

What it does:

This starts PHP sessions. Your login system depends on this because authentication is stored in:

```php
$_SESSION['user_id']
```

Important snippet:

```php
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
```

What it does:

This autoloader automatically loads class files when you use namespaced classes such as:

```php
use App\Controllers\PostController;
use App\Core\Router;
```

The autoloader turns this class name:

```text
App\Controllers\PostController
```

into this file path:

```text
app/Controllers/PostController.php
```

That is why your namespaces and folders must match.

Important snippet:

```php
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$uri = str_replace('ite3/', '', $uri);
```

What it does:

This reads the browser URL and removes `/ite3/` because your project is inside the XAMPP folder:

```text
C:\xampp\htdocs\ite3
```

So this URL:

```text
/ite3/posts/create
```

becomes this route key:

```text
posts/create
```

Important snippet:

```php
$router = new Router();
require __DIR__ . '/../app/routes.php';
$router->resolve($uri, $method);
```

What it does:

This creates the router, loads the route definitions, then asks the router to find the correct controller method for the current request.

## 3. app/routes.php

File:

```text
app/routes.php
```

Function:

This file connects URLs to controller methods.

Snippet:

```php
$router->get('home', 'PostController@index');
```

Meaning:

When the browser requests:

```text
GET /ite3/home
```

call:

```php
PostController::index()
```

Snippet:

```php
$router->get('posts/create', 'PostController@create');
```

Meaning:

When the browser requests:

```text
GET /ite3/posts/create
```

call:

```php
PostController::create()
```

Snippet:

```php
$router->post('posts', 'PostController@store');
```

Meaning:

When the create form submits:

```text
POST /ite3/posts
```

call:

```php
PostController::store()
```

Snippet:

```php
$router->get('posts/edit/{id}', 'PostController@edit');
```

Meaning:

This route has a dynamic value. A URL like:

```text
/ite3/posts/edit/7
```

calls:

```php
PostController::edit(7)
```

## 4. app/Core/Router.php

File:

```text
app/Core/Router.php
```

Function:

The router stores all registered routes and dispatches the current request to the correct controller method.

Snippet:

```php
protected $routes = [];
```

What it does:

Stores routes in an array, separated by request method.

Conceptually, it becomes something like:

```php
[
    'GET' => [
        'home' => 'PostController@index',
        'posts/create' => 'PostController@create',
    ],
    'POST' => [
        'posts' => 'PostController@store',
    ],
]
```

Snippet:

```php
public function get($uri, $controller) {
    $this->routes['GET'][$uri] = $controller;
}
```

How to use it:

Use this for pages that are opened in the browser:

```php
$router->get('about', 'PageController@about');
```

Snippet:

```php
public function post($uri, $controller) {
    $this->routes['POST'][$uri] = $controller;
}
```

How to use it:

Use this for form submissions:

```php
$router->post('contact', 'ContactController@send');
```

Snippet:

```php
$pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $route);
```

What it does:

This converts dynamic route placeholders into a regex pattern.

Example:

```text
posts/edit/{id}
```

becomes a pattern that can match:

```text
posts/edit/7
```

Snippet:

```php
list($controllerClass, $action) = explode('@', $controller);
```

What it does:

This splits:

```text
PostController@store
```

into:

```php
$controllerClass = 'PostController';
$action = 'store';
```

Snippet:

```php
$controllerClass = 'App\\Controllers\\' . $controllerClass;
```

What it does:

This adds the namespace so PHP can find the actual class:

```text
App\Controllers\PostController
```

Snippet:

```php
return call_user_func_array([$controllerInstance, $action], $matches);
```

What it does:

This actually calls the controller method. If the URL was:

```text
/ite3/posts/edit/7
```

then `$matches` contains `7`, and PHP calls:

```php
$controllerInstance->edit(7);
```

## 5. app/Controllers/Controller.php

File:

```text
app/Controllers/Controller.php
```

Function:

This is the base controller. Other controllers extend it so they can use `render()`.

Snippet:

```php
abstract class Controller {
```

What it means:

This class is meant to be inherited, not used directly.

Snippet:

```php
protected function render($viewName, $data = []) {
```

What it does:

This method loads a view and wraps it inside the main layout.

Example:

```php
$this->render('create', [
    'title' => 'Create New Post'
]);
```

loads:

```text
app/Views/create.php
```

Snippet:

```php
extract($data);
```

What it does:

This turns array keys into variables.

Example:

```php
[
    'title' => 'Create New Post',
    'errors' => $errors
]
```

becomes:

```php
$title
$errors
```

inside the view.

Snippet:

```php
ob_start();
include __DIR__ . "/../Views/{$viewName}.php";
$content = ob_get_clean();
```

What it does:

This captures the view output into a variable named `$content`.

Snippet:

```php
include __DIR__ . "/../Views/layouts/main.php";
```

What it does:

This loads the main layout. The layout uses `$content` to display the page-specific view.

## 6. app/Views/layouts/main.php

File:

```text
app/Views/layouts/main.php
```

Function:

This is the shared page shell. Every rendered view appears inside it.

Snippet:

```php
<link rel="stylesheet" href="/ite3/public/css/style.css">
```

What it does:

Loads your CSS.

Snippet:

```php
<a href="/ite3/home">Home</a>
<a href="/ite3/posts/create">Create Post</a>
```

What it does:

These links let the user navigate to the home page and create-post page.

Snippet:

```php
<?php if (isset($_SESSION['user_id'])): ?>
    <a href="/ite3/logout">Logout</a>
<?php else: ?>
    <a href="/ite3/login">Login</a>
<?php endif; ?>
```

What it does:

Shows `Logout` when the user is logged in. Shows `Login` when no user session exists.

Snippet:

```php
<?php echo $content; ?>
```

What it does:

This is where the selected view gets inserted.

Snippet:

```php
<script src="/ite3/public/js/app.js"></script>
```

What it does:

Loads your JavaScript file.

## 7. app/Controllers/PostController.php

File:

```text
app/Controllers/PostController.php
```

Function:

This controller handles blog post features: listing, creating, editing, updating, and deleting posts.

Snippet:

```php
namespace App\Controllers;
```

What it does:

Places this class inside the `App\Controllers` namespace. This matches the folder:

```text
app/Controllers
```

Snippet:

```php
use App\Models\Post;
use App\Helpers\Validator;
```

What it does:

Imports the `Post` model and `Validator` helper so this controller can use them.

Snippet:

```php
class PostController extends Controller {
```

What it does:

`PostController` inherits from the base `Controller`, which gives it access to:

```php
$this->render()
```

### checkAuth()

Snippet:

```php
protected function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /ite3/login');
        exit;
    }
}
```

Function:

Protects pages that require login.

How it connects:

`create()`, `store()`, `edit()`, `update()`, and `delete()` all call this before doing protected actions.

### index()

Snippet:

```php
$postModel = new Post();
$posts = $postModel->all();
```

Function:

Creates the `Post` model, asks it for all posts, and stores them in `$posts`.

Snippet:

```php
$this->render('home', [
    'title' => 'Welcome to DevBlog CMS!',
    'posts' => $posts
]);
```

Function:

Loads `app/Views/home.php` and gives it `$title` and `$posts`.

How to use it:

Visit:

```text
/ite3/home
```

### create()

Snippet:

```php
$this->checkAuth();
```

Function:

Only logged-in users can open the create-post form.

Snippet:

```php
$this->render('create', [
    'title' => 'Create New Post'
]);
```

Function:

Shows the create-post form.

How to use it:

Visit:

```text
/ite3/posts/create
```

Note:

This method also contains a POST-handling block:

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postModel = new Post();
    $postModel->create($_POST['title'], $_POST['content']);
    header('Location: /');
    exit;
}
```

In your current routing, form submissions go to `store()`, not `create()`, because `app/routes.php` says:

```php
$router->post('posts', 'PostController@store');
```

So this POST block inside `create()` is probably leftover code and is not part of the normal create-post form flow.

### store()

Snippet:

```php
$title = $_POST['title'];
$content = $_POST['content'];
```

Function:

Reads submitted form values.

Snippet:

```php
Validator::clearErrors();
Validator::required('title', $title);
Validator::required('content', $content);
```

Function:

Clears old validation errors, then checks that title and content are not empty.

Snippet:

```php
if (Validator::hasErrors()) {
```

Function:

This is intended to check whether validation failed.

Important issue:

Your current `Validator.php` does not define `hasErrors()`. To make this work, add this method to `Validator`:

```php
public static function hasErrors() {
    return !empty(self::$errors);
}
```

Snippet:

```php
return $this->render('create', [
    'title' => 'Create New Post',
    'errors' => Validator::getErrors(),
    'old' => $_POST
]);
```

Function:

If validation fails, show the create form again, pass validation errors, and pass old input so the user does not lose what they typed.

Snippet:

```php
$postModel = new Post();
$postModel->create($title, $content);
```

Function:

If validation passes, insert the post into the database.

Snippet:

```php
header('Location: /ite3/home');
exit;
```

Function:

Redirects the user back to the home page after the post is created.

### edit($id)

Snippet:

```php
$post = $postModel->find($id);
```

Function:

Gets one post by ID.

Snippet:

```php
$this->render('post-edit', [
    'title' => 'Edit Post',
    'post' => $post
]);
```

Function:

Loads the edit form and passes the selected post into it.

How to use it:

Visit:

```text
/ite3/posts/edit/7
```

### update()

Snippet:

```php
$id = $_POST['id'];
$title = $_POST['title'];
$content = $_POST['content'];
```

Function:

Reads the submitted edit form.

Snippet:

```php
$postModel->update($id, $title, $content);
```

Function:

Updates the database row for that post.

### delete($id)

Snippet:

```php
$postModel->delete($id);
```

Function:

Deletes one post by ID.

How to use it:

Click the delete link on the home page:

```text
/ite3/posts/delete/{id}
```

## 8. app/Helpers/Validator.php

File:

```text
app/Helpers/Validator.php
```

Function:

This helper validates form data and stores validation messages.

Snippet:

```php
protected static $errors = [];
```

What it does:

Stores validation errors shared by the static methods.

Example:

```php
[
    'title' => 'Title is required!',
    'content' => 'Content is required!'
]
```

Snippet:

```php
public static function required($fieldName, $value) {
    if (empty(trim($value))) {
        self::$errors[$fieldName] = ucfirst($fieldName) . " is required!";
        return false;
    }
    return true;
}
```

Function:

Checks whether a field is empty.

How to use it:

```php
Validator::required('title', $_POST['title']);
```

If the value is empty, it stores:

```text
Title is required!
```

Snippet:

```php
public static function minLength($fieldName, $value, $min) {
```

Function:

Checks whether a value has at least a certain number of characters.

How to use it:

```php
Validator::minLength('title', $title, 5);
```

Snippet:

```php
public static function getErrors() {
    return self::$errors;
}
```

Function:

Returns all errors so the controller can pass them to the view.

Snippet:

```php
public static function clearErrors() {
    self::$errors = [];
}
```

Function:

Clears previous errors before validating a new request.

Recommended missing snippet:

```php
public static function hasErrors() {
    return !empty(self::$errors);
}
```

Function:

Returns `true` if there is at least one validation error.

## 9. app/Models/Model.php

File:

```text
app/Models/Model.php
```

Function:

This is the base model. Other models extend it so they automatically get a database connection.

Snippet:

```php
abstract class Model {
    protected $db;
```

What it does:

Defines a protected `$db` property. Child classes like `Post` and `User` can use it.

Snippet:

```php
public function __construct() {
    $this->db = Database::getConnection();
}
```

What it does:

Whenever you create a model:

```php
$postModel = new Post();
```

the base constructor gets a PDO database connection.

## 10. app/Config/database.php

File:

```text
app/Config/database.php
```

Function:

Creates and reuses a PDO database connection.

Snippet:

```php
private static $instance = null;
```

What it does:

Stores one shared database connection.

Snippet:

```php
if (!self::$instance) {
```

What it does:

Only creates the connection once. After that, it reuses the same connection.

Snippet:

```php
$host = 'localhost';
$db = 'devblog_db';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
```

Function:

These are the database settings for XAMPP/MySQL.

Snippet:

```php
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
```

Function:

Builds the PDO connection string.

Snippet:

```php
self::$instance = new \PDO($dsn, $user, $pass, $options);
```

Function:

Actually connects to MySQL.

## 11. app/Models/Post.php

File:

```text
app/Models/Post.php
```

Function:

Handles database actions for blog posts.

Snippet:

```php
class Post extends Model {
```

What it does:

`Post` inherits the database connection from `Model`.

### all()

Snippet:

```php
$stmt = $this->db->query("SELECT * FROM posts ORDER BY created_at DESC");
return $stmt->fetchAll();
```

Function:

Gets all posts from the database, newest first.

Used by:

```php
PostController::index()
```

### find($id)

Snippet:

```php
$stmt = $this->db->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$id]);
return $stmt->fetch();
```

Function:

Gets one post by ID.

Used by:

```php
PostController::edit($id)
```

### create($title, $content)

Snippet:

```php
$stmt = $this->db->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
return $stmt->execute([$title, $content]);
```

Function:

Creates a new blog post.

Used by:

```php
PostController::store()
```

### update($id, $title, $content)

Snippet:

```php
$stmt = $this->db->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
```

Function:

Updates an existing post.

Used by:

```php
PostController::update()
```

### delete($id)

Snippet:

```php
$stmt = $this->db->prepare("DELETE FROM posts WHERE id = ?");
```

Function:

Deletes a post.

Used by:

```php
PostController::delete($id)
```

## 12. app/Views/create.php

File:

```text
app/Views/create.php
```

Function:

Shows the create-post form.

Snippet:

```php
<form action="/ite3/posts" method="POST">
```

Function:

When submitted, this form sends data to:

```text
POST /ite3/posts
```

That route calls:

```php
PostController::store()
```

Snippet:

```php
<input type="text" id="title" name="title" value="<?= isset($old['title']) ? htmlspecialchars($old['title']) : '' ?>" required>
```

Function:

Creates the title field.

Important details:

- `name="title"` decides the key in `$_POST['title']`.
- `$old['title']` refills the field after validation fails.
- `htmlspecialchars()` protects the page from outputting raw HTML.
- `required` gives browser-side validation, but you still need server-side validation in `Validator`.

Snippet:

```php
<?php if (isset($errors['title'])): ?>
    <span style="color: red;"><?= $errors['title'] ?></span>
<?php endif; ?>
```

Function:

Displays the title validation error if the controller passed one.

Snippet:

```php
<textarea id="content" name="content" rows="5" required><?= isset($old['content']) ? htmlspecialchars($old['content']) : '' ?></textarea>
```

Function:

Creates the content field and refills it if validation fails.

## 13. app/Views/home.php

File:

```text
app/Views/home.php
```

Function:

Displays all blog posts.

Snippet:

```php
<?php foreach ($posts as $post): ?>
```

Function:

Loops through the posts passed by `PostController::index()`.

Snippet:

```php
<h3><?= htmlspecialchars($post['title']) ?></h3>
<p><?= htmlspecialchars($post['content']) ?></p>
```

Function:

Displays each post safely.

Snippet:

```php
<a href="/ite3/posts/edit/<?= $post['id'] ?>">Edit</a>
```

Function:

Creates a link to edit this specific post.

Snippet:

```php
<a href="/ite3/posts/delete/<?= $post['id'] ?>" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
```

Function:

Creates a delete link and asks for browser confirmation before continuing.

## 14. app/Views/post-edit.php

File:

```text
app/Views/post-edit.php
```

Function:

Shows the edit form for an existing post.

Snippet:

```php
<form action="/ite3/posts/update" method="POST">
```

Function:

Submits the edited post to:

```text
POST /ite3/posts/update
```

That route calls:

```php
PostController::update()
```

Snippet:

```php
<input type="hidden" name="id" value="<?= $post['id'] ?>">
```

Function:

Stores the post ID invisibly so the controller knows which post to update.

Snippet:

```php
<input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" required>
```

Function:

Shows the current title and lets the user edit it.

Snippet:

```php
<textarea name="content" required><?= htmlspecialchars($post['content']) ?></textarea>
```

Function:

Shows the current content and lets the user edit it.

## 15. app/Controllers/AuthController.php

File:

```text
app/Controllers/AuthController.php
```

Function:

Handles login and logout.

Snippet:

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
```

Function:

Checks whether the login form was submitted.

Snippet:

```php
$username = $_POST['username'];
$password = $_POST['password'];
```

Function:

Reads login form values.

Snippet:

```php
$userModel = new User();
$user = $userModel->findByUsername($username);
```

Function:

Uses the `User` model to look up the submitted username.

Snippet:

```php
if ($user && password_verify($password, $user['password'])) {
```

Function:

Checks that the user exists and that the submitted password matches the hashed password in the database.

Snippet:

```php
$_SESSION['user_id'] = $user['id'];
```

Function:

Logs the user in by storing their ID in the session.

Snippet:

```php
session_destroy();
```

Function:

Logs the user out by destroying the session.

## 16. app/Models/User.php

File:

```text
app/Models/User.php
```

Function:

Handles database actions for users.

Snippet:

```php
class User extends Model
```

What it does:

Gets the database connection from the base `Model`.

Snippet:

```php
$stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
return $stmt->fetch();
```

Function:

Finds one user by username.

Used by:

```php
AuthController::login()
```

## 17. app/Views/login.php

File:

```text
app/Views/login.php
```

Function:

Shows the login form and optionally displays a login error.

Snippet:

```php
<?php if (!empty($error)): ?>
```

Function:

Only displays the error paragraph if the controller passed an `$error`.

Snippet:

```php
<form action="/ite3/login" method="POST">
```

Function:

Submits the login form to:

```text
POST /ite3/login
```

That route calls:

```php
AuthController::login()
```

Snippet:

```php
<input type="text" id="username" name="username" required>
<input type="password" id="password" name="password" required>
```

Function:

These names become:

```php
$_POST['username']
$_POST['password']
```

inside `AuthController::login()`.

## 18. public/css/style.css

File:

```text
public/css/style.css
```

Function:

Controls the page styling.

Snippet:

```css
:root {
  --primary: #007bff;
  --background: #f8f9fa;
  --card: #ffffff;
}
```

Function:

Defines reusable CSS variables.

Snippet:

```css
.container {
  max-width: 1000px;
  margin: 0 auto;
  padding: 1rem;
}
```

Function:

Centers the layout and gives it spacing.

Snippet:

```css
@media (max-width: 768px) {
```

Function:

Applies different styles on smaller screens.

Note:

Your layout uses:

```html
<nav></nav>
```

but your CSS styles:

```css
.nav
```

Those are different. `.nav` targets an element with `class="nav"`. To style your current layout, use:

```css
nav {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
}
```

or change the HTML to:

```html
<nav class="nav"></nav>
```

## 19. public/js/app.js

File:

```text
public/js/app.js
```

Function:

Currently this only logs a message in the browser console.

Snippet:

```js
console.log("ðŸš€ DevBlog CMS Assets Loaded!");
```

Function:

Confirms that the JavaScript file loaded.

Note:

The text looks like an encoding issue. It was probably meant to be an emoji message. If you want ASCII only, you can change it to:

```js
console.log("DevBlog CMS Assets Loaded!");
```

## 20. Create-Post Flow Under The Microscope

This is the full create-post flow in order.

### Step 1: User opens the form

Browser:

```text
GET /ite3/posts/create
```

Route:

```php
$router->get('posts/create', 'PostController@create');
```

Controller:

```php
public function create() {
    $this->checkAuth();
    $this->render('create', [
        'title' => 'Create New Post'
    ]);
}
```

View:

```text
app/Views/create.php
```

Layout:

```text
app/Views/layouts/main.php
```

### Step 2: User submits the form

Form:

```php
<form action="/ite3/posts" method="POST">
```

Browser:

```text
POST /ite3/posts
```

Route:

```php
$router->post('posts', 'PostController@store');
```

Controller:

```php
public function store() {
    $this->checkAuth();
    $title = $_POST['title'];
    $content = $_POST['content'];
```

### Step 3: Controller validates input

```php
Validator::clearErrors();
Validator::required('title', $title);
Validator::required('content', $content);
```

If either field is empty, `Validator` stores errors.

### Step 4A: If validation fails

Controller:

```php
return $this->render('create', [
    'title' => 'Create New Post',
    'errors' => Validator::getErrors(),
    'old' => $_POST
]);
```

View:

```php
<?= isset($old['title']) ? htmlspecialchars($old['title']) : '' ?>
```

This puts the user's old title back into the form.

View:

```php
<?php if (isset($errors['title'])): ?>
    <span style="color: red;"><?= $errors['title'] ?></span>
<?php endif; ?>
```

This displays the validation error.

### Step 4B: If validation passes

Controller:

```php
$postModel = new Post();
$postModel->create($title, $content);
```

Model:

```php
$stmt = $this->db->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
$stmt->execute([$title, $content]);
```

Database:

```text
posts table receives a new row
```

Redirect:

```php
header('Location: /ite3/home');
exit;
```

## 21. Login Flow Under The Microscope

### Step 1: User opens login page

Browser:

```text
GET /ite3/login
```

Route:

```php
$router->get('login', 'AuthController@login');
```

Controller:

```php
$this->render('login', [
    'title' => 'Login'
]);
```

### Step 2: User submits login form

Browser:

```text
POST /ite3/login
```

Route:

```php
$router->post('login', 'AuthController@login');
```

Controller:

```php
$user = $userModel->findByUsername($username);
```

Model:

```php
SELECT * FROM users WHERE username = ?
```

Password check:

```php
password_verify($password, $user['password'])
```

Session:

```php
$_SESSION['user_id'] = $user['id'];
```

Result:

The user is logged in and can access protected post actions.

## 22. Edit-Post Flow Under The Microscope

### Step 1: User clicks Edit

Home view:

```php
<a href="/ite3/posts/edit/<?= $post['id'] ?>">Edit</a>
```

Browser:

```text
GET /ite3/posts/edit/7
```

Route:

```php
$router->get('posts/edit/{id}', 'PostController@edit');
```

Controller:

```php
$post = $postModel->find($id);
```

View:

```text
app/Views/post-edit.php
```

### Step 2: User submits update

Form:

```php
<form action="/ite3/posts/update" method="POST">
```

Route:

```php
$router->post('posts/update', 'PostController@update');
```

Controller:

```php
$postModel->update($id, $title, $content);
```

Model:

```php
UPDATE posts SET title = ?, content = ? WHERE id = ?
```

## 23. Delete-Post Flow Under The Microscope

Home view:

```php
<a href="/ite3/posts/delete/<?= $post['id'] ?>" onclick="return confirm('Are you sure you want to delete this post?');">Delete</a>
```

Browser:

```text
GET /ite3/posts/delete/7
```

Route:

```php
$router->get('posts/delete/{id}', 'PostController@delete');
```

Controller:

```php
$postModel->delete($id);
```

Model:

```php
DELETE FROM posts WHERE id = ?
```

Redirect:

```php
header('Location: /ite3/home');
```

## 24. How To Add A New Page

Example: add an About page.

Step 1: Add a route.

```php
$router->get('about', 'PageController@about');
```

Step 2: Create a controller.

```php
namespace App\Controllers;

class PageController extends Controller {
    public function about() {
        $this->render('about', [
            'title' => 'About'
        ]);
    }
}
```

Step 3: Create a view.

```text
app/Views/about.php
```

Step 4: Add a nav link.

```php
<a href="/ite3/about">About</a>
```

## 25. How To Add A New Validation Rule

Example: require title to be at least 5 characters.

In `PostController::store()`:

```php
Validator::clearErrors();
Validator::required('title', $title);
Validator::minLength('title', $title, 5);
Validator::required('content', $content);
```

In `create.php`, the existing error display still works:

```php
<?php if (isset($errors['title'])): ?>
    <span style="color: red;"><?= $errors['title'] ?></span>
<?php endif; ?>
```

## 26. Current Issues Worth Fixing

### Missing Validator::hasErrors()

`PostController::store()` calls:

```php
Validator::hasErrors()
```

but `Validator.php` does not define it yet.

Fix:

```php
public static function hasErrors() {
    return !empty(self::$errors);
}
```

### Duplicate URI parsing in public/index.php

`public/index.php` calculates `$uri` twice. It works, but the first calculation is repeated later.

### Leftover POST logic in PostController::create()

`create()` contains a POST-handling block, but your route sends POST `/posts` to `store()`. Keeping all create-submit logic in `store()` is clearer.

### CSS nav selector mismatch

Your HTML uses:

```html
<nav></nav>
```

Your CSS uses:

```css
.nav
```

Either change the CSS selector to `nav`, or add `class="nav"` to the HTML.

### JavaScript console text encoding

`public/js/app.js` contains:

```js
console.log("ðŸš€ DevBlog CMS Assets Loaded!");
```

This looks like a character encoding problem. It can be simplified to:

```js
console.log("DevBlog CMS Assets Loaded!");
```

## 27. Vocabulary Map

`namespace`

Names the folder-like identity of a class, such as `App\Controllers`.

`use`

Imports a class so you can write `new Post()` instead of `new App\Models\Post()`.

`Controller`

Receives requests, coordinates models/helpers, and renders views.

`Model`

Talks to the database.

`View`

Contains HTML/PHP used to display information.

`Route`

Maps a URL to a controller method.

`$_POST`

Contains submitted form data.

`$_SESSION`

Stores data that persists across requests for a user, such as login state.

`header('Location: ...')`

Redirects the browser to another URL.

`exit`

Stops the script after a redirect.

`htmlspecialchars()`

Escapes text before displaying it in HTML.

`prepare()` and `execute()`

Used together for safer SQL queries with user input.

## 28. Best Way To Study This Project

Read it in this order:

1. `public/index.php`
2. `app/routes.php`
3. `app/Core/Router.php`
4. `app/Controllers/Controller.php`
5. `app/Controllers/PostController.php`
6. `app/Models/Model.php`
7. `app/Config/database.php`
8. `app/Models/Post.php`
9. `app/Views/layouts/main.php`
10. `app/Views/home.php`
11. `app/Views/create.php`
12. `app/Helpers/Validator.php`
13. `app/Controllers/AuthController.php`
14. `app/Models/User.php`
15. `app/Views/login.php`

The most important idea:

```text
Routes decide which controller method runs.
Controllers decide what data is needed.
Models get or change database data.
Views display the result.
The layout wraps every view.
```
