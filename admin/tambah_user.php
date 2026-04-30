<?php
// admin/tambah_user.php — Tambah Pengguna Baru
require_once __DIR__ . '/../middleware/admin.php';

$errors = [];
$old    = ['nama' => '', 'username' => '', 'role' => 'user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama       = trim($_POST['nama']       ?? '');
    $username   = trim($_POST['username']   ?? '');
    $password   = $_POST['password']        ?? '';
    $konfirmasi = $_POST['konfirmasi']      ?? '';
    $role       = $_POST['role']            ?? '';

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

    if ($password === '')
        $errors['password'] = 'Password wajib diisi.';
    elseif (strlen($password) < 6)
        $errors['password'] = 'Password minimal 6 karakter.';

    if ($konfirmasi !== $password)
        $errors['konfirmasi'] = 'Konfirmasi password tidak cocok.';

    if (!in_array($role, ['admin', 'petugas', 'user']))
        $errors['role'] = 'Role tidak valid.';

    if (empty($errors)) {
        $pdo = getPDO();

        // Cek username unik
        $cek = $pdo->prepare('SELECT id FROM users WHERE username = :username');
        $cek->execute([':username' => $username]);
        if ($cek->fetch()) {
            $errors['username'] = 'Username sudah digunakan. Pilih username lain.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $pdo->prepare(
                'INSERT INTO users (nama, username, password, role)
                 VALUES (:nama, :username, :password, :role)'
            );
            $stmt->execute([
                ':nama'     => $nama,
                ':username' => $username,
                ':password' => $hash,
                ':role'     => $role,
            ]);

            setFlash('success', "Pengguna <strong>$nama</strong> berhasil ditambahkan.");
            redirect('user.php');
        }
    }
}

$pageTitle  = 'Tambah Pengguna';
$activePage = 'users';
require_once __DIR__ . '/layout/header.php';

/* Helper: class is-invalid dan pesan error */
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
    <h5 class="mb-0">Tambah Pengguna Baru</h5>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h6>
                    <i class="bi bi-person-plus me-2 text-primary"></i>
                    Form Data Pengguna
                </h6>
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
                               placeholder="Contoh: Budi Santoso"
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
                                   placeholder="Contoh: budi123"
                                   maxlength="50">
                            <?= $err('username') ?>
                        </div>
                        <div class="form-text">Hanya huruf, angka, dan underscore. 3–50 karakter.</div>
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
                    </div>

                    <hr>

                    <!-- Password -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" name="password" id="pwd"
                                   class="form-control <?= $cls('password') ?>"
                                   placeholder="Minimal 6 karakter">
                            <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePwd('pwd','eyePwd')">
                                <i class="bi bi-eye" id="eyePwd"></i>
                            </button>
                            <?= $err('password') ?>
                        </div>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Konfirmasi Password <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" name="konfirmasi" id="conf"
                                   class="form-control <?= $cls('konfirmasi') ?>"
                                   placeholder="Ulangi password">
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
                            <i class="bi bi-check-lg me-1"></i> Simpan Pengguna
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
