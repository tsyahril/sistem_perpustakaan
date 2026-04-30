<?php
// admin/edit_user.php — Edit Pengguna
require_once __DIR__ . '/../middleware/admin.php';

$pdo = getPDO();
$id  = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    setFlash('danger', 'ID pengguna tidak valid.');
    redirect('user.php');
}

$stmt = $pdo->prepare('SELECT id, nama, username, role FROM users WHERE id = :id');
$stmt->execute([':id' => $id]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('danger', 'Pengguna tidak ditemukan.');
    redirect('user.php');
}

$errors = [];
$old    = ['nama' => $user['nama'], 'username' => $user['username'], 'role' => $user['role']];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama       = trim($_POST['nama']       ?? '');
    $username   = trim($_POST['username']   ?? '');
    $role       = $_POST['role']            ?? '';
    $password   = $_POST['password']        ?? '';
    $konfirmasi = $_POST['konfirmasi']      ?? '';

    $old = compact('nama', 'username', 'role');

    // ── Validasi ──
    if ($nama === '')
        $errors['nama'] = 'Nama lengkap wajib diisi.';
    elseif (mb_strlen($nama) > 100)
        $errors['nama'] = 'Nama maksimal 100 karakter.';

    if ($username === '')
        $errors['username'] = 'Username wajib diisi.';
    elseif (!preg_match('/^[a-z0-9_]{3,50}$/i', $username))
        $errors['username'] = 'Username hanya boleh huruf, angka, underscore (3–50 karakter).';

    if (!in_array($role, ['admin', 'petugas', 'user']))
        $errors['role'] = 'Role tidak valid.';

    if ($password !== '') {
        if (strlen($password) < 6)
            $errors['password'] = 'Password minimal 6 karakter.';
        if ($konfirmasi !== $password)
            $errors['konfirmasi'] = 'Konfirmasi password tidak cocok.';
    }

    if (empty($errors)) {
        // Cek username unik (kecuali milik dirinya)
        $cek = $pdo->prepare(
            'SELECT id FROM users WHERE username = :username AND id != :id'
        );
        $cek->execute([':username' => $username, ':id' => $id]);
        if ($cek->fetch()) {
            $errors['username'] = 'Username sudah digunakan oleh pengguna lain.';
        } else {
            if ($password !== '') {
                $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                $stmt = $pdo->prepare(
                    'UPDATE users
                     SET    nama=:nama, username=:username, role=:role, password=:password
                     WHERE  id=:id'
                );
                $stmt->execute([
                    ':nama'     => $nama,
                    ':username' => $username,
                    ':role'     => $role,
                    ':password' => $hash,
                    ':id'       => $id,
                ]);
            } else {
                $stmt = $pdo->prepare(
                    'UPDATE users
                     SET    nama=:nama, username=:username, role=:role
                     WHERE  id=:id'
                );
                $stmt->execute([
                    ':nama'     => $nama,
                    ':username' => $username,
                    ':role'     => $role,
                    ':id'       => $id,
                ]);
            }

            // Sinkronkan nama di session jika mengedit akun sendiri
            if ($id === (int) $_SESSION['user_id']) {
                $_SESSION['nama'] = $nama;
            }

            setFlash('success', "Data pengguna <strong>$nama</strong> berhasil diperbarui.");
            redirect('user.php');
        }
    }
}

$pageTitle  = 'Edit Pengguna';
$activePage = 'users';
require_once __DIR__ . '/layout/header.php';

$cls = fn(string $f): string => isset($errors[$f]) ? 'is-invalid' : '';
$err = fn(string $f): string => isset($errors[$f])
    ? '<div class="invalid-feedback">' . e($errors[$f]) . '</div>'
    : '';
$val = fn(string $f): string => e($old[$f] ?? '');
?>

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="user.php" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i>
    </a>
    <h5 class="mb-0">Edit Pengguna</h5>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <div class="admin-avatar" style="width:34px;height:34px;font-size:.8rem">
                    <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                </div>
                <div>
                    <div class="fw-semibold" style="font-size:.9rem">
                        <?= e($user['nama']) ?>
                    </div>
                    <small class="text-muted">ID #<?= $user['id'] ?></small>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" autocomplete="off" novalidate>

                    <!-- Nama -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Nama Lengkap <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nama"
                               class="form-control <?= $cls('nama') ?>"
                               value="<?= $val('nama') ?>"
                               maxlength="100">
                        <?= $err('nama') ?>
                    </div>

                    <!-- Username -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Username <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-at text-muted"></i>
                            </span>
                            <input type="text" name="username"
                                   class="form-control <?= $cls('username') ?>"
                                   value="<?= $val('username') ?>"
                                   maxlength="50">
                            <?= $err('username') ?>
                        </div>
                    </div>

                    <!-- Role -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Role <span class="text-danger">*</span>
                        </label>
                        <select name="role" class="form-select <?= $cls('role') ?>">
                            <option value="user"    <?= $val('role') === 'user'    ? 'selected' : '' ?>>User</option>
                            <option value="petugas" <?= $val('role') === 'petugas' ? 'selected' : '' ?>>Petugas</option>
                            <option value="admin"   <?= $val('role') === 'admin'   ? 'selected' : '' ?>>Admin</option>
                        </select>
                        <?= $err('role') ?>
                        <?php if ($id === (int) $_SESSION['user_id']): ?>
                            <div class="form-text text-warning">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Anda sedang mengedit akun Anda sendiri.
                            </div>
                        <?php endif; ?>
                    </div>

                    <hr>

                    <p class="text-muted mb-3" style="font-size:.85rem">
                        <i class="bi bi-info-circle me-1"></i>
                        Biarkan kolom password kosong jika tidak ingin mengubah password.
                    </p>

                    <!-- Password Baru -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password Baru</label>
                        <div class="input-group">
                            <input type="password" name="password" id="pwd"
                                   class="form-control <?= $cls('password') ?>"
                                   placeholder="Kosongkan jika tidak diubah">
                            <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePwd('pwd','eyePwd')">
                                <i class="bi bi-eye" id="eyePwd"></i>
                            </button>
                            <?= $err('password') ?>
                        </div>
                    </div>

                    <!-- Konfirmasi Password Baru -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                        <div class="input-group">
                            <input type="password" name="konfirmasi" id="conf"
                                   class="form-control <?= $cls('konfirmasi') ?>"
                                   placeholder="Ulangi password baru">
                            <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePwd('conf','eyeConf')">
                                <i class="bi bi-eye" id="eyeConf"></i>
                            </button>
                            <?= $err('konfirmasi') ?>
                        </div>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <a href="user.php" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function togglePwd(fieldId, iconId) {
        const f = document.getElementById(fieldId);
        const i = document.getElementById(iconId);
        if (f.type === 'password') {
            f.type = 'text';
            i.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            f.type = 'password';
            i.classList.replace('bi-eye-slash', 'bi-eye');
        }
    }
</script>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
