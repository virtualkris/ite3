<?php
// The namespace MUST match the directory structure and the class name MUST match the file name for the autoloader to work correctly.
namespace App\Controllers;

use App\Models\Post;
use App\Helpers\Validator;
use App\Services\PostService;

class PostController extends Controller {
    // Class implementation
    protected function checkAuth() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /ite3/login');
            exit;
        }
    }
    public function index() {
        $postModel = new Post();
        $posts = $postModel->all();

        $this->render('home', [
            'title' => 'Welcome to DevBlog CMS!',
            'posts' => $posts
        ]);
    }

    public function create() {
        $this->checkAuth();

        $this->render('create', [
            'title' => 'Create New Post'
        ]);
    }

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

    public function store() {
        $this->checkAuth();
        // This method can be used for handling form submissions if needed
        $title = $_POST['title'];
        $content = $_POST['content'];

        Validator::clearErrors();
        Validator::required('title', $title);
        Validator::required('content', $content);

        if (Validator::getErrors()) {
            return $this->render('create', [
                'title' => 'Create New Post',
                'errors' => Validator::getErrors(),
                'old' => $_POST // Pass old input back to the view for repopulation
            ]);
        }

        $slug = PostService::generateSlug($title);

        $postModel = new Post();
        $postModel->create($title, $slug, $content);

        $_SESSION['flash'] = "Success! Action completed.";
        header('Location: /ite3/home');
        exit;

        
    }

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

    public function update() {
        $this->checkAuth();

        $id = $_POST['id'];
        $title = $_POST['title'];
        $content = $_POST['content'];

        Validator::clearErrors();
        Validator::required('title', $title);
        Validator::required('content', $content);

        if (Validator::getErrors()) {
            $postModel = new Post();
            $post = $postModel->find($id);
            return $this->render('post-edit', [
                'title' => 'Edit Post',
                'post' => $post,
                'errors' => Validator::getErrors()
            ]);
        }

        $slug = PostService::generateSlug($title);

        $postModel = new Post();
        $postModel->update($id, $title, $slug, $content);

        $_SESSION['flash'] = "Success! Action completed.";
        header('Location: /ite3/home');
        exit;
    }

    public function delete($id = null) {
        $this->checkAuth();

        $id = $id ?? ($_POST['id'] ?? null);
        if (!$id) {
            http_response_code(400);
            echo "Post id is required.";
            return;
        }

        $postModel = new Post();
        $postModel->delete($id);

        $_SESSION['flash'] = "Post deleted successfully!";
        header('Location: /ite3/home');
        exit;
    }
    
}
