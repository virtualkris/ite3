<?php
// The namespace MUST match the directory structure and the class name MUST match the file name for the autoloader to work correctly.
namespace App\Controllers;

use App\Models\Post;

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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postModel = new Post();
            $postModel->create($_POST['title'], $_POST['content']);
            header('Location: /');
            exit;
        }

        $this->render('create', [
            'title' => 'Create New Post'
        ]);
    }

    public function store() {
        $this->checkAuth();
        // This method can be used for handling form submissions if needed
        $title = $_POST['title'];
        $content = $_POST['content'];

        if (!empty($title) && !empty($content)) {
            $postModel = new Post();
            $postModel->create($title, $content);
        }

        header('Location: /ite3/home');
        exit;
    }

    public function edit($id) {
        $this->checkAuth();
        $postModel = new Post();
        $post = $postModel->find($id);

        if (!$post) {
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

        if (!empty($id) && !empty($title) && !empty($content)) {
            $postModel = new Post();
            $postModel->update($id, $title, $content);
        }

        header('Location: /ite3/home');
        exit;
    }

    public function delete($id) {
        $this->checkAuth();
        $postModel = new Post();
        $postModel->delete($id);

        header('Location: /ite3/home');
        exit;
    }
    
}