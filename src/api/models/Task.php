<?php
namespace App\Models;

class Task {
    private $conn;
    private $table = 'tasks';

    public $id;
    public $user_id;
    public $task_type;
    public $status;
    public $completed_at;
    public $verified_by;
    public $verified_at;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . "
                SET
                    user_id = :user_id,
                    task_type = :task_type,
                    status = 'pending',
                    created_at = NOW()";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':task_type', $this->task_type);

        return $stmt->execute();
    }

    public function markCompleted() {
        $query = "UPDATE " . $this->table . "
                SET
                    status = 'completed',
                    completed_at = NOW()
                WHERE
                    id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':user_id', $this->user_id);

        return $stmt->execute();
    }

    public function verify($parent_id) {
        $query = "UPDATE " . $this->table . "
                SET
                    status = 'verified',
                    verified_by = :parent_id,
                    verified_at = NOW()
                WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':parent_id', $parent_id);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    public function getWeeklyTasks($user_id) {
        $query = "SELECT * FROM " . $this->table . "
                WHERE user_id = :user_id
                AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getPendingVerification() {
        $query = "SELECT t.*, u.first_name, u.email
                FROM " . $this->table . " t
                JOIN users u ON t.user_id = u.id
                WHERE t.status = 'completed'
                ORDER BY t.completed_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
