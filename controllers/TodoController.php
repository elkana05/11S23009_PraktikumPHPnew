
<?php
require_once (__DIR__ . '/../models/TodoModel.php');

class TodoController
{
    /** @var TodoModel $todoModel */
    private $todoModel;

    /**
     * Constructor untuk menginisialisasi model.
     */
    public function __construct()
    {
        $this->todoModel = new TodoModel();
    }

    /**
     * Menampilkan halaman utama dengan daftar todo.
     * Mengelola filter dan pencarian.
     */
    public function index()
    {
        $filter = $_GET['filter'] ?? 'all'; // Default filter adalah 'all'
        $search = $_GET['search'] ?? '';   // Ambil keyword pencarian

        $todos = $this->todoModel->getAllTodos($filter, $search);
        include (__DIR__ . '/../views/TodoView.php');
    }

    /**
     * Membuat todo baru berdasarkan data dari form POST.
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['activity']); // Di form masih menggunakan nama 'activity'
            $description = trim($_POST['description']);
 
            $validationErrors = $this->validateTodo($title);
            if (empty($validationErrors)) {
                if ($this->todoModel->createTodo($title, $description)) {
                    $this->setFlashMessage('success', 'Berhasil menambahkan todo baru.');
                } else {
                    $this->setFlashMessage('danger', 'Gagal menambahkan todo baru.');
                }
            }
        }
        $this->redirect('index.php');
    }

    /**
     * Memperbarui todo yang ada berdasarkan data dari form POST.
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $title = trim($_POST['activity']); // Di form masih menggunakan nama 'activity'
            $description = trim($_POST['description']);
            $status = isset($_POST['status']) ? (bool)$_POST['status'] : false;
 
            $validationErrors = $this->validateTodo($title, $id);
            if (empty($validationErrors)) {
                if ($this->todoModel->updateTodo($id, $title, $description, $status)) {
                    $this->setFlashMessage('success', 'Todo berhasil diperbarui.');
                } else {
                    $this->setFlashMessage('danger', 'Gagal memperbarui todo.');
                }
            }
        }
        $this->redirect('index.php');
    }

    /**
     * Menghapus todo berdasarkan ID dari query string.
     * Catatan Keamanan: Operasi destruktif (seperti delete) sebaiknya menggunakan metode POST
     * untuk mencegah penghapusan tidak sengaja oleh crawler atau pre-fetching browser.
     * Perubahan ini memerlukan modifikasi pada view (menggunakan form atau JavaScript).
     */
    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
            $id = $_POST['id'];
            if ($this->todoModel->deleteTodo($id)) {
                $this->setFlashMessage('success', 'Todo berhasil dihapus.');
            } else {
                $this->setFlashMessage('danger', 'Gagal menghapus todo.');
            }
        }
        $this->redirect('index.php');
    }

    /**
     * Memperbarui urutan todo (drag-and-drop).
     */
    public function updateOrder()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $orderedIds = $input['order'];

            header('Content-Type: application/json');
            if ($this->todoModel->updateOrder($orderedIds)) {
                echo json_encode(['status' => 'success', 'message' => 'Urutan berhasil diperbarui.']);
            } else {
                http_response_code(500);
                echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui urutan.']);
            }
            exit();
        }
    }

    /**
     * Helper function untuk redirect.
     * @param string $url
     */
    private function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit();
    }

    /**
     * Mengatur flash message di session.
     * @param string $type Tipe pesan (e.g., 'success', 'danger').
     * @param string $message Isi pesan.
     */
    private function setFlashMessage(string $type, string $message): void
    {
        $_SESSION['flash_message'] = ['type' => $type, 'message' => $message];
    }

    /**
     * Memvalidasi data todo (judul).
     * @param string $title Judul todo.
     * @param int|null $currentId ID todo saat ini (untuk operasi update).
     * @return array Daftar pesan error. Kosong jika valid.
     */
    private function validateTodo(string $title, ?int $currentId = null): array
    {
        $errors = [];
        if (empty($title)) {
            $errors[] = 'Judul todo tidak boleh kosong.';
        }

        $existingTodo = $this->todoModel->getTodoByTitle($title);
        if ($existingTodo && $existingTodo['id'] != $currentId) {
            $errors[] = 'Todo dengan judul "' . htmlspecialchars($title) . '" sudah ada.';
        }

        // Jika ada error, langsung set flash message
        if (!empty($errors)) {
            $this->setFlashMessage('danger', 'Gagal! ' . implode(' ', $errors));
        }

        return $errors;
    }
}