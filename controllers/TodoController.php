<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/TodoModel.php';

/**
 * Class TodoController
 * Handles all user actions related to Todos.
 */
class TodoController
{
    /**
     * Displays the main page with a list of todos.
     * Handles filtering and searching.
     *
     * @return void
     */
    public function index()
    {
        $filter = $_GET['filter'] ?? 'all';
        $search = $_GET['search'] ?? '';

        $todoModel = new TodoModel();
        $todos = $todoModel->getAllTodos($filter, $search);

        include __DIR__ . '/../views/TodoView.php';
    }

    /**
     * Handles the creation of a new todo.
     * Validates input and sets a flash message.
     *
     * @return void
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title']);
            $description = $_POST['description'] ?? null;

            $todoModel = new TodoModel();

            if (empty($title)) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Gagal! Judul tidak boleh kosong.'];
            } elseif ($todoModel->checkTitleExists($title)) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Gagal! Judul todo sudah ada.'];
            } elseif ($todoModel->createTodo($title, $description)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Todo berhasil ditambahkan.'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Gagal menambahkan todo ke database.'];
            }
        }

        header('Location: ' . $this->buildRedirectUrl());
        exit();
    }

    /**
     * Handles the update of an existing todo.
     * Validates input and sets a flash message.
     *
     * @return void
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)$_POST['id'];
            $title = trim($_POST['title']);
            $description = $_POST['description'] ?? null;
            $is_finished = $_POST['is_finished'] ?? '0';

            $todoModel = new TodoModel();

            if (empty($title)) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Gagal! Judul tidak boleh kosong.'];
            } elseif ($todoModel->checkTitleExists($title, $id)) {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Gagal! Judul todo yang sama sudah ada.'];
            } elseif ($todoModel->updateTodo((int)$id, $title, $description, $is_finished)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Todo berhasil diperbarui.'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Gagal memperbarui todo.'];
            }
        }

        header('Location: ' . $this->buildRedirectUrl());
        exit();
    }

    /**
     * Handles the AJAX request to update the sort order of todos.
     *
     * @return void
     */
    public function updateOrder()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Invalid request method.'], 405);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $orderedIds = $data['order'] ?? [];

        if (empty($orderedIds)) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'No order data provided.'], 400);
            return;
        }

        $todoModel = new TodoModel();
        if ($todoModel->updateOrder($orderedIds)) {
            $this->sendJsonResponse(['status' => 'success', 'message' => 'Order updated successfully.']);
        } else {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Failed to update order.'], 500);
        }
    }

    /**
     * Handles the deletion of a todo.
     *
     * @return void
     */
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
            $id = $_GET['id'];
            $todoModel = new TodoModel();
            if ($todoModel->deleteTodo((int)$id)) {
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Todo berhasil dihapus.'];
            } else {
                $_SESSION['flash_message'] = ['type' => 'danger', 'message' => 'Gagal menghapus todo.'];
            }
        }

        header('Location: ' . $this->buildRedirectUrl());
        exit();
    }

    /**
     * Builds a redirect URL with persistent filter and search parameters.
     *
     * @return string The constructed URL.
     */
    private function buildRedirectUrl()
    {
        $params = [
            'filter' => $_POST['filter'] ?? $_GET['filter'] ?? 'all',
            'search' => $_POST['search'] ?? $_GET['search'] ?? ''
        ];

        $params = array_filter($params, function ($value) {
            return $value !== '' && $value !== null;
        });

        return basename($_SERVER['PHP_SELF']) . '?' . http_build_query($params);
    }

    /**
     * Sends a JSON response.
     *
     * @param array $data The data to encode.
     * @param int $statusCode The HTTP status code.
     * @return void
     */
    private function sendJsonResponse(array $data, int $statusCode = 200)
    {
        header('Content-Type: application/json', true, $statusCode);
        echo json_encode($data);
    }
}
