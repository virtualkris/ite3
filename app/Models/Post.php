<?php
namespace App\Models;

class Post extends Model {
    // Fetch all posts from the database
    public function all() {
        $stmt = $this->db->query("SELECT * FROM posts ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    // Fetch a single post by ID
    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM posts WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($title, $slug, $content) {
        $stmt = $this->db->prepare("INSERT INTO posts (title, slug, content) VALUES (?, ?, ?)");
        return $stmt->execute([
            $title,
            $slug,
            $content
        ]);
    }


    public function update($id, $title, $slug, $content) {
        $stmt = $this->db->prepare("UPDATE posts SET title = ?, slug = ?, content = ? WHERE id = ?");
        return $stmt->execute([
            $title,
            $slug,
            $content,
            $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM posts WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
