<!-- footer.php -->

            </div>

        </main>

    </div>

    <script>
        // DROPDOWN PROFILE
        const profileButton = document.getElementById('profileButton');
        const profileDropdown = document.getElementById('profileDropdown');

        profileButton.addEventListener('click', () => {
            profileDropdown.classList.toggle('hidden');
        });

        window.addEventListener('click', function(e) {

            if (!profileButton.contains(e.target) &&
                !profileDropdown.contains(e.target)) {

                profileDropdown.classList.add('hidden');

            }

        });

        // SWEETALERT LOGOUT
        function confirmLogout(event, url) {

            event.preventDefault();

            Swal.fire({
                title: 'Logout?',
                text: 'Yakin ingin keluar?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Ya, Logout',
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

</body>

</html>

<?php
ob_end_flush();
?>