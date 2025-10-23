<?php
require_once __DIR__ . '/../config.php';

class TodoModel
{
    /** @var PDO $pdo */
    private $pdo;

    public function __construct()
    {
        $dsn = 'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME;
        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            // Sebaiknya di-log, bukan die() di production
            die('Koneksi database gagal: ' . $e->getMessage());
        }
    }

    public function getAllTodos($filter = 'all', $search = '')
    {
        $query = 'SELECT id, title, description, is_finished, created_at, updated_at FROM todo';
        $whereClauses = [];
        $bindings = [];

        if ($filter === 'finished') {
            $whereClauses[] = 'is_finished = TRUE';
        } elseif ($filter === 'unfinished') {
            $whereClauses[] = 'is_finished = FALSE';
        }

        if (!empty($search)) {
            $whereClauses[] = '(title ILIKE :search OR description ILIKE :search)';
            $bindings[':search'] = '%' . $search . '%';
        }

        if (!empty($whereClauses)) {
            $query .= ' WHERE ' . implode(' AND ', $whereClauses);
        }

        $query .= ' ORDER BY sort_order ASC, created_at DESC';
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($bindings);
        
        return $stmt->fetchAll();
    }

    public function getTodoByTitle($title)
    {
        $stmt = $this->pdo->prepare('SELECT id, title FROM todo WHERE title = :title');
        $stmt->execute([':title' => $title]);
        return $stmt->fetch();
    }

    public function createTodo($title, $description)
    {
        try {
            $this->pdo->beginTransaction();
            
            // Dapatkan nilai sort_order tertinggi + 1
            $stmt = $this->pdo->query("SELECT MAX(sort_order) FROM todo");
            $maxOrder = $stmt->fetchColumn();
            $newOrder = ($maxOrder === null) ? 1 : $maxOrder + 1;

            $stmt = $this->pdo->prepare('INSERT INTO todo (title, description, sort_order) VALUES (:title, :description, :sort_order)');
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':sort_order' => $newOrder
            ]);
            
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            // Sebaiknya di-log
            return false;
        }
    }

    public function updateTodo($id, $title, $description, $status)
    {
        $stmt = $this->pdo->prepare('UPDATE todo SET title = :title, description = :description, is_finished = :is_finished, updated_at = NOW() WHERE id = :id');
        return $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':is_finished' => $status,
            ':id' => $id
        ]);
    }

    public function deleteTodo($id)
    {
        $stmt = $this->pdo->prepare('DELETE FROM todo WHERE id = :id');
        return $stmt->execute([':id' => $id]);
    }

    public function updateOrder($orderedIds)
    {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("UPDATE todo SET sort_order = :order WHERE id = :id");
            foreach ($orderedIds as $index => $id) {
                $order = $index + 1;
                $stmt->execute([':order' => $order, ':id' => $id]);
            }
            $this->pdo->commit();
            return true;
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            // Sebaiknya di-log
            return false;
        }
    }
}