<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todolist - Aplikasi Modern</title>
    <link href="assets/vendor/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }
        .card {
            border: none;
            border-radius: 0.75rem;
        }
        .todo-item {
            background-color: #fff;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            margin-bottom: 0.75rem;
            padding: 1rem;
            transition: box-shadow 0.2s ease-in-out;
            display: flex;
            align-items: center;
        }
        .todo-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .todo-item .todo-actions {
            opacity: 0;
            transition: opacity 0.2s ease-in-out;
        }
        .todo-item:hover .todo-actions {
            opacity: 1;
        }
        .todo-title.finished {
            text-decoration: line-through;
            color: #6c757d;
        }
        .handle {
            cursor: grab;
        }
        .sortable-ghost {
            opacity: 0.4;
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="card shadow-sm">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="card-title mb-0 fw-bold">Todo List</h1>
                <button class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#addTodo">
                    <i class="bi bi-plus-lg"></i> Tambah Todo
                </button>
            </div>
            <hr />
            <?php
            // Tampilkan flash message jika ada
            if (isset($_SESSION['flash_message'])) {
                $flash = $_SESSION['flash_message'];
                echo '<div class="alert alert-' . $flash['type'] . ' alert-dismissible fade show" role="alert">';
                echo $flash['message'];
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                echo '</div>';
                unset($_SESSION['flash_message']); // Hapus pesan setelah ditampilkan
            }
            ?>
            <div class="row mb-3">
                <div class="col-md-8">
                    <!-- Tombol Filter -->
                    <div class="btn-group" role="group" aria-label="Filter todos">
                        <a href="?action=index&filter=all&search=<?= urlencode($search ?? '') ?>" class="btn btn-outline-secondary <?= ($filter === 'all' ? 'active' : '') ?>">Semua</a>
                        <a href="?action=index&filter=finished&search=<?= urlencode($search ?? '') ?>" class="btn btn-outline-success <?= ($filter === 'finished' ? 'active' : '') ?>">Selesai</a>
                        <a href="?action=index&filter=unfinished&search=<?= urlencode($search ?? '') ?>" class="btn btn-outline-danger <?= ($filter === 'unfinished' ? 'active' : '') ?>">Belum Selesai</a>
                    </div>
                </div>
                <div class="col-md-4">
                    <!-- Form Pencarian -->
                    <form action="index.php" method="GET">
                        <input type="hidden" name="action" value="index">
                        <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari judul atau deskripsi..." value="<?= htmlspecialchars($search ?? '') ?>">
                            <button class="btn btn-outline-primary" type="submit"><i class="bi bi-search"></i></button>
                        </div>
                    </form>
                </div>
            </div>
            
            <table class="table table-borderless table-hover">
                <thead>
                    <tr>
                        <th scope="col" style="width: 5%;"></th>
                        <th scope="col">Judul</th>
                        <th scope="col">Deskripsi</th>
                        <th scope="col">Status</th>
                        <th scope="col">Tanggal Dibuat</th>
                        <th scope="col">Terakhir Diubah</th>
                        <th scope="col">Tindakan</th>
                    </tr>
                </thead>
                <tbody id="todo-list-body">
                <?php if (!empty($todos)): ?>
                    <?php foreach ($todos as $i => $todo): ?>
                    <tr data-id="<?= $todo['id'] ?>" class="align-middle bg-light">
                        <td class="handle text-center text-muted"><i class="bi bi-grip-vertical"></i></td>
                        <td class="fw-semibold"><?= htmlspecialchars($todo['title']) ?></td>
                        <td class="text-muted small"><?= htmlspecialchars(mb_strimwidth($todo['description'] ?? '-', 0, 50, "...")) ?></td>
                        <td>
                            <?php if ($todo['is_finished']): ?>
                                <span class="badge text-bg-success"><i class="bi bi-check-circle-fill"></i> Selesai</span>
                            <?php else: ?>
                                <span class="badge text-bg-danger"><i class="bi bi-hourglass-split"></i> Belum Selesai</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small"><?= date('d M Y, H:i', strtotime($todo['created_at'])) ?></td>
                        <td class="text-muted small"><?= $todo['updated_at'] ? date('d M Y, H:i', strtotime($todo['updated_at'])) : '-' ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-info text-white" data-bs-toggle="tooltip" title="Detail"
                                        data-todo-title="<?= htmlspecialchars($todo['title']) ?>"
                                        data-todo-description="<?= htmlspecialchars($todo['description'] ?? 'Tidak ada deskripsi.') ?>"
                                        data-todo-created_at="<?= date('d F Y, H:i', strtotime($todo['created_at'])) ?>"
                                        data-todo-updated_at="<?= $todo['updated_at'] ? date('d F Y, H:i', strtotime($todo['updated_at'])) : 'Belum pernah diubah' ?>"
                                        onclick="showModalDetailTodo(this)"><i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-warning text-white" data-bs-toggle="tooltip" title="Ubah"
                                        onclick="showModalEditTodo(<?= $todo['id'] ?>, '<?= htmlspecialchars($todo['title']) ?>', <?= $todo['is_finished'] ? '1' : '0' ?>, '<?= htmlspecialchars($todo['description'] ?? '') ?>')">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Hapus"
                                        onclick="showModalDeleteTodo(<?= $todo['id'] ?>, '<?= htmlspecialchars($todo['title']) ?>')">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center p-5 bg-light">
                            <i class="bi bi-journal-check" style="font-size: 3rem; color: #ced4da;"></i>
                            <p class="mt-2 text-muted">Belum ada tugas. Saatnya bersantai!</p>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>

        </div>
    </div>
</div>

<!-- MODAL ADD TODO -->
<div class="modal fade" id="addTodo" tabindex="-1" aria-labelledby="addTodoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTodoLabel"><i class="bi bi-plus-circle-fill"></i> Tambah Data Todo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?action=create" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="inputActivity" class="form-label">Judul Aktivitas</label>
                        <input type="text" name="activity" class="form-control" id="inputActivity"
                            placeholder="Contoh: Belajar membuat aplikasi website sederhana" required>
                    </div>
                    <div class="mb-3">
                        <label for="inputDescription" class="form-label">Deskripsi (Opsional)</label>
                        <textarea name="description" class="form-control" id="inputDescription" rows="3"
                            placeholder="Tambahkan detail lebih lanjut tentang aktivitas..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-floppy"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT TODO -->
<div class="modal fade" id="editTodo" tabindex="-1" aria-labelledby="editTodoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTodoLabel"><i class="bi bi-pencil-fill"></i> Ubah Data Todo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?action=update" method="POST">
                <input name="id" type="hidden" id="inputEditTodoId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="inputEditActivity" class="form-label">Judul Aktivitas</label>
                        <input type="text" name="activity" class="form-control" id="inputEditActivity"
                            placeholder="Contoh: Belajar membuat aplikasi website sederhana" required>
                    </div>
                    <div class="mb-3">
                        <label for="inputEditDescription" class="form-label">Deskripsi (Opsional)</label>
                        <textarea name="description" class="form-control" id="inputEditDescription" rows="3"
                            placeholder="Tambahkan detail lebih lanjut tentang aktivitas..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="selectEditStatus" class="form-label">Status</label>
                        <select class="form-select" name="status" id="selectEditStatus">
                            <option value="0">Belum Selesai</option>
                            <option value="1">Selesai</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-floppy"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DETAIL TODO -->
<div class="modal fade" id="detailTodo" tabindex="-1" aria-labelledby="detailTodoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailTodoLabel"><i class="bi bi-card-text"></i> Detail Todo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5 id="detailTodoTitle" class="mb-3"></h5>
                <p id="detailTodoDescription" style="white-space: pre-wrap;"></p>
                <hr>
                <div>
                    <small class="text-muted d-block"><strong>Dibuat pada:</strong> <span id="detailTodoCreatedAt"></span></small>
                    <small class="text-muted d-block"><strong>Terakhir diubah:</strong> <span id="detailTodoUpdatedAt"></span></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DELETE TODO -->
<div class="modal fade" id="deleteTodo" tabindex="-1" aria-labelledby="deleteTodoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTodoLabel"><i class="bi bi-exclamation-triangle-fill text-danger"></i> Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    Kamu akan menghapus todo <strong class="text-danger" id="deleteTodoActivity"></strong>.
                    Apakah kamu yakin?
                </div>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="?action=delete" method="POST" class="m-0">
                    <input type="hidden" name="id" id="deleteTodoId">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash3-fill"></i> Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="assets/vendor/bootstrap-5.3.8-dist/js/bootstrap.bundle.min.js"></script>

<script>
function showModalEditTodo(todoId, activity, status, description = '') {
    document.getElementById("inputEditTodoId").value = todoId;
    document.getElementById("inputEditActivity").value = activity;
    document.getElementById("selectEditStatus").value = status;
    document.getElementById("inputEditDescription").value = description;

    var myModal = new bootstrap.Modal(document.getElementById("editTodo"));
    myModal.show();
}

function showModalDetailTodo(button) {
    const title = button.getAttribute('data-todo-title');
    const description = button.getAttribute('data-todo-description');
    const createdAt = button.getAttribute('data-todo-created_at');
    const updatedAt = button.getAttribute('data-todo-updated_at');

    document.getElementById("detailTodoTitle").textContent = title;
    document.getElementById("detailTodoDescription").textContent = description;
    document.getElementById("detailTodoCreatedAt").textContent = createdAt;
    document.getElementById("detailTodoUpdatedAt").textContent = updatedAt;

    var myModal = new bootstrap.Modal(document.getElementById("detailTodo"));
    myModal.show();
}

function showModalDeleteTodo(todoId, activity) {
    document.getElementById("deleteTodoActivity").innerText = activity;
    document.getElementById("deleteTodoId").value = todoId;
    var myModal = new bootstrap.Modal(document.getElementById("deleteTodo"));
    myModal.show();
}

document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('todo-list-body');
    const sortable = Sortable.create(el, {
        handle: '.handle', // Tentukan elemen mana yang menjadi handle untuk drag
        animation: 150,
        ghostClass: 'sortable-ghost',
        onStart: function (evt) {
            // Mengubah cursor saat mulai drag
            evt.from.style.cursor = 'grabbing';
        },
        onEnd: function (evt) {
            evt.from.style.cursor = 'grab'; // Kembalikan cursor
            const itemIds = [];
            const rows = el.querySelectorAll('tr');
            rows.forEach(row => {
                itemIds.push(row.getAttribute('data-id'));
            });

            // Kirim urutan baru ke server
            saveOrder(itemIds);
        }
    });

    function saveOrder(order) {
        fetch('?action=updateOrder', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ order: order }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                console.log('Sort order saved:', data.message);
            }
        })
        .catch(error => console.error('Error saving sort order:', error));
    }

    // Inisialisasi semua tooltip
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});
</script>
</body>
</html>
