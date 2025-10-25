<?php

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

/**
 * Class TodoModel
 * Manages all database operations for the 'todo' table.
 */
class TodoModel
{
    /** @var resource|false The PostgreSQL connection resource. */
    private $conn;

    /**
     * TodoModel constructor.
     * Establishes a database connection.
     */
    public function __construct()
    {
        $this->conn = pg_connect('host=' . DB_HOST . ' port=' . DB_PORT . ' dbname=' . DB_NAME . ' user=' . DB_USER . ' password=' . DB_PASSWORD);
        if (!$this->conn) {
            // In a real application, this should be handled by a more robust error handler/logger.
            error_log('Database connection failed: ' . pg_last_error());
            die('Koneksi database gagal. Silakan periksa log server.');
        }
    }

    /**
     * Retrieves all todos from the database, with optional filtering and searching.
     *
     * @param string $filter The status to filter by ('all', 'finished', 'unfinished').
     * @param string $search The search term to look for in title or description.
     * @return array An array of todo items.
     */
    public function getAllTodos(string $filter = 'all', string $search = ''): array
    {
        $query = 'SELECT * FROM todo';
        $conditions = [];
        $params = [];
        $paramIndex = 1;

        if ($filter === 'finished') {
            $conditions[] = 'is_finished = true';
        } elseif ($filter === 'unfinished') {
            $conditions[] = 'is_finished = false';
        }

        if (!empty($search)) {
            $conditions[] = '(title ILIKE $' . $paramIndex . ' OR description ILIKE $' . $paramIndex . ')';
            $params[] = '%' . $search . '%';
            $paramIndex++;
        }

        if (!empty($conditions)) {
            $query .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $query .= ' ORDER BY sort_order ASC, created_at DESC';

        $result = pg_query_params($this->conn, $query, $params);
        if (!$result) {
            error_log('Get all todos query failed: ' . pg_last_error($this->conn));
            return [];
        }

        return pg_fetch_all($result) ?: [];
    }

    /**
     * Checks if a todo with the given title already exists.
     *
     * @param string $title The title to check.
     * @param int|null $excludeId An optional ID to exclude from the check (used for updates).
     * @return bool True if the title exists, false otherwise.
     */
    public function checkTitleExists(string $title, ?int $excludeId = null): bool
    {
        $query = 'SELECT id FROM todo WHERE title = $1';
        $params = [$title];

        if ($excludeId !== null) {
            $query .= ' AND id <> $2';
            $params[] = $excludeId;
        }

        $result = pg_query_params($this->conn, $query, $params);
        return ($result && pg_num_rows($result) > 0);
    }

    /**
     * Updates the sort order for a list of todo IDs.
     *
     * @param array $todoIds An array of todo IDs in the desired order.
     * @return bool True on success, false on failure.
     */
    public function updateOrder(array $todoIds): bool
    {
        pg_query($this->conn, 'BEGIN');
        try {
            foreach ($todoIds as $index => $id) {
                $order = $index + 1;
                // Biarkan trigger database yang menangani updated_at
                $query = 'UPDATE todo SET sort_order = $1 WHERE id = $2';
                pg_query_params($this->conn, $query, [$order, $id]);
            }
            pg_query($this->conn, 'COMMIT');
            return true;
        } catch (Exception $e) {
            error_log('Update order failed: ' . $e->getMessage());
            pg_query($this->conn, 'ROLLBACK');
            return false;
        }
    }

    /**
     * Creates a new todo item in the database.
     *
     * @param string $title The title of the todo.
     * @param string|null $description The description of the todo.
     * @return bool True on success, false on failure.
     */
    public function createTodo(string $title, ?string $description): bool
    {
        $countQuery = 'SELECT COUNT(id) as total FROM todo';
        $countResult = pg_query($this->conn, $countQuery);
        $total = pg_fetch_assoc($countResult)['total'];
        $nextOrder = $total + 1;

        $query = 'INSERT INTO todo (title, description, sort_order) VALUES ($1, $2, $3)';
        $result = pg_query_params($this->conn, $query, [$title, $description, $nextOrder]);
        return $result !== false;
    }

    /**
     * Updates an existing todo item.
     *
     * @param int $id The ID of the todo to update.
     * @param string $title The new title.
     * @param string|null $description The new description.
     * @param string $is_finished The new status ('1' for finished, '0' for not).
     * @return bool True on success, false on failure.
     */
    public function updateTodo(int $id, string $title, ?string $description, string $is_finished): bool
    {
        // Convert form value ('1'/'0') to PostgreSQL boolean format ('t'/'f').
        $is_finished_pg = ($is_finished === '1') ? 't' : 'f';
        $query = 'UPDATE todo SET title=$1, description=$2, is_finished=$3 WHERE id=$4';
        $result = pg_query_params($this->conn, $query, [$title, $description, $is_finished_pg, $id]);
        return $result !== false;
    }

    /**
     * Deletes a todo item from the database.
     *
     * @param int $id The ID of the todo to delete.
     * @return bool True on success, false on failure.
     */
    public function deleteTodo(int $id): bool
    {
        $query = 'DELETE FROM todo WHERE id=$1';
        $result = pg_query_params($this->conn, $query, [$id]);
        return $result !== false;
    }
}
