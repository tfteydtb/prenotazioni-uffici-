<?php
// models/resource.php

class Resource {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllResources() {
        $stmt = $this->pdo->query("SELECT * FROM resources WHERE stato = 'attiva' ORDER BY tipo, nome");
        return $stmt->fetchAll();
    }

    public function getResourceById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM resources WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
?>