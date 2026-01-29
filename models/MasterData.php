<?php
class MasterData {
    private $conn;
    public function __construct($db) { $this->conn = $db; }

    public function getList($type) {
        $allowed = [
            'versions' => 'mu_versions',
            'types' => 'server_types',
            'points' => 'point_types',
            'resets' => 'reset_types'
        ];
        if (!array_key_exists($type, $allowed)) return null;

        $query = "SELECT * FROM " . $allowed[$type];
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}