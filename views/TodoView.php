<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP - Aplikasi Todolist</title>
    <link href="/assets/vendor/bootstrap-5.3.8-dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="main-container">
    <div class="app-header d-flex justify-content-between align-items-center">
        <div>
            <h1 class="app-title">Manajer Daftar Tugas</h1>
            <p class="app-subtitle">Atur tugas Anda secara efisien dan profesional</p>
        </div>
        <button class="btn-add-todo" data-bs-toggle="modal" data-bs-target="#addTodo">
            + Tambah Tugas Baru
        </button>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_message']['type'] ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['flash_message']['message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_message']); // Hapus pesan setelah ditampilkan ?>
    <?php endif; ?>

    <div class="controls-wrapper">
        <div class="row g-3">
            <div class="col-lg-8">
                <form action="" method="GET" class="search-box">
                    <input type="hidden" name="filter" value="<?= htmlspecialchars($_GET['filter'] ?? 'all') ?>">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control"
                               placeholder="Cari berdasarkan judul atau deskripsi..."
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        <button class="btn" type="submit">Cari</button>
                    </div>
                </form>
            </div>
            <div class="col-lg-4">
                <div class="filter-buttons">
                    <a href="?filter=all&search=<?= urlencode($_GET['search'] ?? '') ?>"
                       class="filter-btn <?= (!isset($_GET['filter']) || $_GET['filter'] == 'all') ? 'active' : '' ?>">
                        Semua Tugas
                    </a>
                    <a href="?filter=unfinished&search=<?= urlencode($_GET['search'] ?? '') ?>"
                       class="filter-btn btn-danger-outline <?= (isset($_GET['filter']) && $_GET['filter'] == 'unfinished') ? 'active' : '' ?>">
                        Belum Selesai
                    </a>
                    <a href="?filter=finished&search=<?= urlencode($_GET['search'] ?? '') ?>"
                       class="filter-btn btn-success-outline <?= (isset($_GET['filter']) && $_GET['filter'] == 'finished') ? 'active' : '' ?>">
                        Selesai
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="todo-card">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col" style="width: 50px;">#</th>
                    <th scope="col">Judul</th>
                    <th scope="col">Deskripsi</th>
                    <th scope="col" style="width: 130px;">Status</th>
                    <th scope="col" style="width: 180px;">Tanggal Dibuat</th>
                    <th scope="col" style="width: 280px;">Aksi</th>
                </tr>
            </thead>
            <tbody id="todo-list-body">
            <?php if (!empty($todos)): ?>
                <?php foreach ($todos as $i => $todo): ?>
                <tr data-id="<?= $todo['id'] ?>">
                    <td><?= $i + 1 ?></td>
                    <td><strong><?= htmlspecialchars($todo['title']) ?></strong></td>
                    <td><?= htmlspecialchars($todo['description'] ?? '-') ?></td>
                    <td>
                        <?php if ($todo['is_finished'] === 't'): ?>
                            <span class="badge bg-success">Selesai</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Belum Selesai</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d M Y, H:i', strtotime($todo['created_at'])) ?></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-info"
                                    onclick='showModalDetailTodo(
                                        <?= json_encode($todo['title']) ?>,
                                        <?= json_encode($todo['description'] ?? 'Tidak ada deskripsi') ?>,
                                        <?= json_encode(($todo['is_finished'] === 't') ? "<span class=\"badge bg-success\">Selesai</span>" : "<span class=\"badge bg-danger\">Belum Selesai</span>") ?>,
                                        <?= json_encode(date('d F Y, H:i:s', strtotime($todo['created_at']))) ?>,
                                        <?= json_encode(!empty($todo['updated_at']) ? date('d F Y, H:i:s', strtotime($todo['updated_at'])) : 'Belum pernah diubah') ?>
                                    )'>Detail</button>
                            <button class="btn btn-sm btn-warning"
                                onclick='showModalEditTodo(<?= $todo['id'] ?>, <?= json_encode($todo['title']) ?>, <?= json_encode($todo['description'] ?? '') ?>, "<?= ($todo['is_finished'] === 't') ? '1' : '0' ?>")'>
                                Ubah
                            </button>
                            <button class="btn btn-sm btn-danger"
                                onclick='showModalDeleteTodo(<?= $todo['id'] ?>, <?= json_encode($todo['title']) ?>, "<?= htmlspecialchars($_GET['filter'] ?? 'all') ?>", "<?= urlencode($_GET['search'] ?? '') ?>")'>
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <div class="empty-state-icon">📋</div>
                            <h5>Tidak ada tugas yang ditemukan</h5>
                            <p class="text-muted">Mulai dengan menambahkan tugas pertama Anda!</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL ADD TODO -->
<div class="modal fade" id="addTodo" tabindex="-1" aria-labelledby="addTodoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addTodoLabel">Tambah Tugas Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?action=create" method="POST">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($_GET['filter'] ?? 'all') ?>">
                <input type="hidden" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="inputTitle" class="form-label">Judul Tugas</label>
                        <input type="text" name="title" class="form-control" id="inputTitle"
                            placeholder="contoh: Selesaikan dokumentasi proyek" required>
                    </div>
                    <div class="mb-3">
                        <label for="inputDescription" class="form-label">Deskripsi (Opsional)</label>
                        <textarea name="description" class="form-control" id="inputDescription"
                            placeholder="Tambahkan detail lebih lanjut tentang tugas ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Tugas</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL EDIT TODO -->
<div class="modal fade" id="editTodo" tabindex="-1" aria-labelledby="editTodoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTodoLabel">Ubah Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?action=update" method="POST">
                <input type="hidden" name="filter" value="<?= htmlspecialchars($_GET['filter'] ?? 'all') ?>">
                <input type="hidden" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                <input name="id" type="hidden" id="inputEditTodoId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="inputEditTitle" class="form-label">Judul Tugas</label>
                        <input type="text" name="title" class="form-control" id="inputEditTitle"
                            placeholder="contoh: Selesaikan dokumentasi proyek" required>
                    </div>
                    <div class="mb-3">
                        <label for="inputEditDescription" class="form-label">Deskripsi (Opsional)</label>
                        <textarea name="description" class="form-control" id="inputEditDescription"
                            placeholder="Tambahkan detail lebih lanjut tentang tugas ini..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="selectEditStatus" class="form-label">Status</label>
                        <select class="form-select" name="is_finished" id="selectEditStatus">
                            <option value="0">Belum Selesai</option>
                            <option value="1">Selesai</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Perbarui Tugas</button>
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
                <h5 class="modal-title" id="detailTodoLabel">Detail Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="detail-label">Judul</label>
                    <div class="detail-content" id="detailTitle"></div>
                </div>
                <div class="mb-3">
                    <label class="detail-label">Deskripsi</label>
                    <div class="detail-content" id="detailDescription" style="white-space: pre-wrap;"></div>
                </div>
                <div class="mb-3">
                    <label class="detail-label">Status</label>
                    <div id="detailStatus"></div>
                </div>
                <div class="mb-3">
                    <label class="detail-label">Tanggal Dibuat</label>
                    <div class="detail-content" id="detailCreatedAt"></div>
                </div>
                <div class="mb-3">
                    <label class="detail-label">Terakhir Diperbarui</label>
                    <div class="detail-content" id="detailUpdatedAt"></div>
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
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteTodoLabel">Hapus Tugas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <p>Anda akan menghapus tugas <strong class="text-danger" id="deleteTodoActivity"></strong>.</p>
                    <p class="text-muted">Tindakan ini tidak dapat dibatalkan. Apakah Anda yakin?</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a id="btnDeleteTodo" class="btn btn-danger">Ya, Hapus</a>
            </div>
        </div>
    </div>
</div>

<script src="/assets/vendor/bootstrap-5.3.8-dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script src="/assets/js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Inisialisasi fungsionalitas aplikasi
    const todoListBody = document.getElementById('todo-list-body');
    if (todoListBody && todoListBody.children.length > 1) {
        initializeSortable(todoListBody);
    }

    // Logika untuk notifikasi yang menghilang otomatis
    const flashAlert = document.querySelector('.alert-dismissible');
    if (flashAlert) {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(flashAlert);
            bsAlert.close();
        }, 4000); // Alert akan hilang setelah 4 detik
    }
});
</script>
</body>
</html>