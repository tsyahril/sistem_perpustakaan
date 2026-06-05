        </div> <!-- End Content -->

    </main>

</div>

<!-- Footer -->
<footer class="fixed bottom-0 left-72 right-0 h-14 bg-slate-900 border-t border-slate-800 flex items-center justify-between px-8 text-sm text-slate-400">

    <p>
        © <?= date('Y') ?> E-Perpus — Sistem Informasi Perpustakaan
    </p>

    <div class="flex items-center gap-2">
        <i class='bx bx-shield-quarter text-blue-400'></i>
        <span>Admin Panel</span>
    </div>

</footer>

<!-- Profile Dropdown Script -->
<script>
    const profileButton =
        document.getElementById('profileButton');

    const profileDropdown =
        document.getElementById('profileDropdown');

    profileButton.addEventListener('click', () => {

        profileDropdown.classList.toggle('hidden');

    });

    window.addEventListener('click', function(e) {

        if (!profileButton.contains(e.target) &&
            !profileDropdown.contains(e.target)) {

            profileDropdown.classList.add('hidden');
        }
    });
</script>


<!-- SWEET ALERT NOTIFICATION -->
<?php if (isset($_SESSION['success'])): ?>

<script>
    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: '<?= $_SESSION['success']; ?>',
        background: '#0f172a',
        color: '#fff',
        confirmButtonColor: '#3b82f6'
    });
</script>

<?php unset($_SESSION['success']); ?>
<?php endif; ?>


<?php if (isset($_SESSION['error'])): ?>

<script>
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: '<?= $_SESSION['error']; ?>',
        background: '#0f172a',
        color: '#fff',
        confirmButtonColor: '#ef4444'
    });
</script>

<?php unset($_SESSION['error']); ?>
<?php endif; ?>


<!-- CONFIRM DELETE -->
<script>
    function confirmDelete(event, url) {

        event.preventDefault();

        Swal.fire({
            title: 'Yakin?',
            text: 'Data akan dihapus permanen!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            background: '#0f172a',
            color: '#fff'
        }).then((result) => {

            if (result.isConfirmed) {
                window.location.href = url;
            }

        });
    }
</script>


<!-- CONFIRM LOGOUT -->
<script>
    function confirmLogout(event, url) {

        event.preventDefault();

        Swal.fire({
            title: 'Keluar?',
            text: 'Anda akan logout dari sistem',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Logout',
            cancelButtonText: 'Batal',
            background: '#0f172a',
            color: '#fff'
        }).then((result) => {

            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Berhasil Logout',
                    text: 'Sampai jumpa kembali 👋',
                    icon: 'success',
                    background: '#0f172a',
                    color: '#fff',
                    showConfirmButton: false,
                    timer: 1200
                });

                setTimeout(() => {
                    window.location.href = url;
                }, 1200);
            }

        });
    }
</script>

</body>

</html>