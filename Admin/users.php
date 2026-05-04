<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';


requireAdmin(1);

// Flash message helper
function setFlash($type, $msg)
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// --- CRUD Handlers (PRG pattern) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // ADD
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $pass = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'user';
        if ($name && $email && $pass) {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $pic = null;
            if (!empty($_FILES['profile_pic']['name']) && $_FILES['profile_pic']['error'] === 0) {
                $dir = __DIR__ . '/../assets/uploads/';
                if (!is_dir($dir))
                    mkdir($dir, 0777, true);
                $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $fn = 'user_' . uniqid() . '.' . $ext;
                    move_uploaded_file($_FILES['profile_pic']['tmp_name'], $dir . $fn);
                    $pic = 'assets/uploads/' . $fn;
                }
            }
            $st = $conn->prepare("INSERT INTO users (name,email,password,role,profile_pic) VALUES (?,?,?,?,?)");
            $st->bind_param("sssss", $name, $email, $hash, $role, $pic);
            $st->execute() ? setFlash('success', 'User created successfully.') : setFlash('danger', 'Error: ' . $st->error);
            $st->close();
        } else {
            setFlash('danger', 'All fields are required.');
        }
        header('Location: users.php');
        exit;
    }

    // EDIT
    if ($action === 'edit') {
        $id = (int) $_POST['id'];
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'user';
        $sql = "UPDATE users SET name=?, email=?, role=?";
        $p = [$name, $email, $role];
        $t = "sss";
        if (!empty($_FILES['profile_pic']['name']) && $_FILES['profile_pic']['error'] === 0) {
            $dir = __DIR__ . '/../assets/uploads/';
            if (!is_dir($dir))
                mkdir($dir, 0777, true);
            $ext = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $fn = 'user_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['profile_pic']['tmp_name'], $dir . $fn);
                $sql .= ", profile_pic=?";
                $p[] = 'assets/uploads/' . $fn;
                $t .= "s";
            }
        }
        if (!empty($_POST['password'])) {
            $sql .= ", password=?";
            $p[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $t .= "s";
        }
        $sql .= " WHERE id=?";
        $p[] = $id;
        $t .= "i";
        $st = $conn->prepare($sql);
        $st->bind_param($t, ...$p);
        $st->execute() ? setFlash('success', 'User updated successfully.') : setFlash('danger', 'Error: ' . $st->error);
        $st->close();
        header('Location: users.php');
        exit;
    }

    // DELETE
    if ($action === 'delete') {
        $id = (int) $_POST['id'];
        $st = $conn->prepare("DELETE FROM users WHERE id=?");
        $st->bind_param("i", $id);
        $st->execute() ? setFlash('success', 'User deleted successfully.') : setFlash('danger', 'Error deleting user.');
        $st->close();
        header('Location: users.php');
        exit;
    }
}

// --- Fetch Users with Search/Sort/Pagination ---
$search = trim($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? 'newest';
$role_filter = $_GET['role'] ?? '';
$page = max(1, (int) ($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

$where = "WHERE 1=1";
$p = [];
$t = "";
if ($search) {
    $where .= " AND (name LIKE ? OR email LIKE ?)";
    $s = "%$search%";
    $p[] = $s;
    $p[] = $s;
    $t .= "ss";
}
if ($role_filter) {
    $where .= " AND role=?";
    $p[] = $role_filter;
    $t .= "s";
}

$orderBy = match ($sort) { 'oldest' => 'created_at ASC', 'name' => 'name ASC', default => 'created_at DESC'};

$cst = $conn->prepare("SELECT COUNT(*) as total FROM users $where");
if ($p)
    $cst->bind_param($t, ...$p);
$cst->execute();
$total = $cst->get_result()->fetch_assoc()['total'];
$cst->close();
$totalPages = max(1, ceil($total / $per_page));

$p2 = $p;
$t2 = $t;
$p2[] = $per_page;
$p2[] = $offset;
$t2 .= "ii";
$st = $conn->prepare("SELECT * FROM users $where ORDER BY $orderBy LIMIT ? OFFSET ?");
if ($t2)
    $st->bind_param($t2, ...$p2);
$st->execute();
$users = $st->get_result();
$st->close();

$showStart = $total ? $offset + 1 : 0;
$showEnd = min($offset + $per_page, $total);

function avatarColor($name)
{
    $c = ['#4F46E5', '#7C3AED', '#DB2777', '#DC2626', '#EA580C', '#D97706', '#16A34A', '#0891B2', '#2563EB', '#9333EA'];
    return $c[abs(crc32($name)) % count($c)];
}
function initials($name)
{
    $parts = explode(' ', trim($name));
    return strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
}

$qstr = http_build_query(array_filter(['search' => $search, 'sort' => $sort, 'role' => $role_filter]));
?>
<?php include 'sidebar.php'; ?>
<title>Users - Admin Panel</title>
<style>
    .main-content {
        flex: 1;
        padding: 2rem 2.5rem;
        min-height: 100vh;
        overflow-y: auto;
    }

    .page-header {
        margin-bottom: 1.5rem;
    }

    .page-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #111827;
        margin: 0;
    }

    .page-subtitle {
        color: #6b7280;
        font-size: 0.95rem;
        margin-top: 0.25rem;
    }

    .header-actions {
        display: flex;
        gap: 0.75rem;
        align-items: center;
    }

    .search-box {
        position: relative;
    }

    .search-box input {
        padding: 0.5rem 0.75rem 0.5rem 2.25rem;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 0.875rem;
        width: 260px;
        transition: border-color 0.2s;
        background: #fff;
    }

    .search-box input:focus {
        outline: none;
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .search-box i {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
    }

    .btn-add {
        background: #2563eb;
        color: #fff;
        border: none;
        padding: 0.5rem 1.25rem;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        transition: background 0.2s;
        cursor: pointer;
    }

    .btn-add:hover {
        background: #1d4ed8;
        color: #fff;
    }

    .controls-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .controls-left {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .controls-right {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.8rem;
        color: #6b7280;
    }

    .filter-btn {
        background: #fff;
        border: 1px solid #d1d5db;
        padding: 0.4rem 0.9rem;
        border-radius: 8px;
        font-size: 0.8rem;
        color: #374151;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.35rem;
        transition: all 0.2s;
    }

    .filter-btn:hover {
        border-color: #9ca3af;
        background: #f9fafb;
    }

    .filter-btn.active {
        border-color: #2563eb;
        color: #2563eb;
        background: #eff6ff;
    }

    .sort-select {
        background: #fff;
        border: 1px solid #d1d5db;
        padding: 0.4rem 0.75rem;
        border-radius: 8px;
        font-size: 0.8rem;
        color: #374151;
        cursor: pointer;
    }

    .pagination-arrows {
        display: flex;
        gap: 0.25rem;
    }

    .pagination-arrows button {
        background: #fff;
        border: 1px solid #d1d5db;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        color: #374151;
    }

    .pagination-arrows button:hover:not(:disabled) {
        border-color: #2563eb;
        color: #2563eb;
    }

    .pagination-arrows button:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .users-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e5e7eb;
    }

    .users-table thead th {
        padding: 0.75rem 1rem;
        font-size: 0.7rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 1px solid #e5e7eb;
        background: #f9fafb;
        text-align: left;
        white-space: nowrap;
    }

    .users-table tbody tr {
        transition: background 0.15s;
    }

    .users-table tbody tr:hover {
        background: #f9fafb;
    }

    .users-table tbody td {
        padding: 0.85rem 1rem;
        font-size: 0.875rem;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }

    .users-table tbody tr:last-child td {
        border-bottom: none;
    }

    .user-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: 600;
        font-size: 0.8rem;
        flex-shrink: 0;
        background-size: cover;
        background-position: center;
    }

    .user-name {
        font-weight: 600;
        color: #111827;
    }

    .role-badge {
        padding: 0.2rem 0.6rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        display: inline-block;
    }

    .role-admin {
        background: #dbeafe;
        color: #1e40af;
    }

    .role-user {
        background: #d1fae5;
        color: #065f46;
    }

    .action-btns {
        display: flex;
        gap: 0.35rem;
    }

    .action-btns button {
        background: none;
        border: 1px solid transparent;
        width: 32px;
        height: 32px;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        color: #6b7280;
    }

    .action-btns .btn-edit:hover {
        background: #eff6ff;
        color: #2563eb;
        border-color: #bfdbfe;
    }

    .action-btns .btn-del:hover {
        background: #fef2f2;
        color: #dc2626;
        border-color: #fecaca;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #9ca3af;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        display: block;
    }

    .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }

    .modal-header {
        border-bottom: 1px solid #f3f4f6;
        padding: 1.25rem 1.5rem;
    }

    .modal-title {
        font-weight: 700;
        font-size: 1.1rem;
    }

    .modal-body {
        padding: 1.5rem;
    }

    .modal-footer {
        border-top: 1px solid #f3f4f6;
        padding: 1rem 1.5rem;
    }

    .form-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.35rem;
    }

    .form-control,
    .form-select {
        border-radius: 8px;
        border: 1px solid #d1d5db;
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .alert {
        border-radius: 10px;
        font-size: 0.875rem;
        border: none;
    }

    .alert-success {
        background: #d1fae5;
        color: #065f46;
    }

    .alert-danger {
        background: #fef2f2;
        color: #991b1b;
    }
</style>

<!-- Main Content -->
<div class="main-content">
    <!-- Flash Message -->
    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
            <i class="bi bi-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?> me-2"></i>
            <?= htmlspecialchars($flash['msg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-start page-header">
        <div>
            <h1 class="page-title">Users</h1>
            <p class="page-subtitle">Manage and view your user base.</p>
        </div>
        <div class="header-actions">
            <form method="GET" class="search-box" id="searchForm">
                <i class="bi bi-search"></i>
                <input type="text" name="search" placeholder="Search users..." value="<?= htmlspecialchars($search) ?>">
                <?php if ($sort !== 'newest'): ?><input type="hidden" name="sort"
                        value="<?= htmlspecialchars($sort) ?>"><?php endif; ?>
                <?php if ($role_filter): ?><input type="hidden" name="role"
                        value="<?= htmlspecialchars($role_filter) ?>"><?php endif; ?>
            </form>
            <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-plus-lg"></i> Add
                User</button>
        </div>
    </div>

    <!-- Controls Bar -->
    <div class="controls-bar">
        <div class="controls-left">
            <a href="?<?= http_build_query(array_filter(['search' => $search, 'sort' => $sort])) ?>"
                class="filter-btn <?= !$role_filter ? 'active' : '' ?>">
                <i class="bi bi-funnel"></i> All
            </a>
            <a href="?<?= http_build_query(array_filter(['search' => $search, 'sort' => $sort, 'role' => 'admin'])) ?>"
                class="filter-btn <?= $role_filter === 'admin' ? 'active' : '' ?>">Admin</a>
            <a href="?<?= http_build_query(array_filter(['search' => $search, 'sort' => $sort, 'role' => 'user'])) ?>"
                class="filter-btn <?= $role_filter === 'user' ? 'active' : '' ?>">User</a>
            <form method="GET" class="d-inline" id="sortForm">
                <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                <?php if ($role_filter): ?><input type="hidden" name="role"
                        value="<?= htmlspecialchars($role_filter) ?>"><?php endif; ?>
                <select name="sort" class="sort-select" onchange="this.form.submit()">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Sort: Newest</option>
                    <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Sort: Oldest</option>
                    <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Sort: Name</option>
                </select>
            </form>
        </div>
        <div class="controls-right">
            <span>Showing <?= $showStart ?>–<?= $showEnd ?> of <?= $total ?></span>
            <div class="pagination-arrows">
                <?php $baseQ = array_filter(['search' => $search, 'sort' => $sort, 'role' => $role_filter]); ?>
                <a href="?<?= http_build_query(array_merge($baseQ, ['page' => $page - 1])) ?>"><button
                        <?= $page <= 1 ? 'disabled' : '' ?>><i class="bi bi-chevron-left"></i></button></a>
                <a href="?<?= http_build_query(array_merge($baseQ, ['page' => $page + 1])) ?>"><button
                        <?= $page >= $totalPages ? 'disabled' : '' ?>><i class="bi bi-chevron-right"></i></button></a>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <table class="users-table">
        <thead>
            <tr>
                <th>User</th>
                <th>Email</th>
                <th>Role</th>
                <th>Joined</th>
                <th style="width:100px">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($users->num_rows === 0): ?>
                <tr>
                    <td colspan="5">
                        <div class="empty-state"><i class="bi bi-people"></i>
                            <p>No users found.</p>
                        </div>
                    </td>
                </tr>
            <?php else:
                while ($u = $users->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="user-cell">
                                <?php if ($u['profile_pic']): ?>
                                    <div class="user-avatar"
                                        style="background-image:url('../<?= htmlspecialchars($u['profile_pic']) ?>')"></div>
                                <?php else: ?>
                                    <div class="user-avatar" style="background:<?= avatarColor($u['name']) ?>">
                                        <?= initials($u['name']) ?></div>
                                <?php endif; ?>
                                <span class="user-name"><?= htmlspecialchars($u['name']) ?></span>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="role-badge role-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                        <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-edit" onclick='openEdit(<?= json_encode($u) ?>)' title="Edit"><i
                                        class="bi bi-pencil"></i></button>
                                <button class="btn-del" onclick='openDelete(<?= $u["id"] ?>, <?= json_encode($u["name"]) ?>)'
                                    title="Delete"><i class="bi bi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; endif; ?>
        </tbody>
    </table>
</div><!-- /main-content -->
</div><!-- /d-flex wrapper from sidebar -->

<!-- Add User Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Full Name *</label><input type="text" name="name"
                            class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Email Address *</label><input type="email" name="email"
                            class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Password *</label><input type="password" name="password"
                            class="form-control" required minlength="6"></div>
                    <div class="mb-3"><label class="form-label">Role</label><select name="role" class="form-select">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select></div>
                    <div class="mb-3"><label class="form-label">Profile Picture</label><input type="file"
                            name="profile_pic" class="form-control" accept="image/*"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"
                        style="background:#2563eb;border:none;border-radius:8px;">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editId">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Full Name *</label><input type="text" name="name"
                            id="editName" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Email Address *</label><input type="email" name="email"
                            id="editEmail" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">New Password <small class="text-muted">(leave blank to
                                keep current)</small></label><input type="password" name="password" class="form-control"
                            minlength="6"></div>
                    <div class="mb-3"><label class="form-label">Role</label><select name="role" id="editRole"
                            class="form-select">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select></div>
                    <div class="mb-3"><label class="form-label">Profile Picture</label><input type="file"
                            name="profile_pic" class="form-control" accept="image/*"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"
                        style="background:#2563eb;border:none;border-radius:8px;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteId">
                <div class="modal-header">
                    <h5 class="modal-title">Delete User</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="deleteName"></strong>? This action cannot be undone.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger" style="border-radius:8px;">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function openEdit(u) {
        document.getElementById('editId').value = u.id;
        document.getElementById('editName').value = u.name;
        document.getElementById('editEmail').value = u.email;
        document.getElementById('editRole').value = u.role;
        new bootstrap.Modal(document.getElementById('editModal')).show();
    }
    function openDelete(id, name) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteName').textContent = name;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>
</body>

</html>