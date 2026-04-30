    </div><!-- /.page-content -->
</div><!-- /#main-content -->

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    /* Toggle sidebar on mobile */
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
        document.getElementById('sidebar-overlay').classList.toggle('show');
    }

    /* Auto-dismiss flash alert setelah 4 detik */
    const flashEl = document.querySelector('.flash-container .alert');
    if (flashEl) {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(flashEl);
            bsAlert.close();
        }, 4000);
    }
</script>
</body>
</html>
