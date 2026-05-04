<?php
namespace App\Models;

use App\Config\Database;

abstract class Model {
    protected $db;

    public function __construct() {
        // Get the database connection from the Database class
        $this->db = Database::getConnection();
    }
}