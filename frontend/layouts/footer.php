</div> <!-- content -->
</div> <!-- layout -->

<footer class="site-footer">
    <hr class="footer-separator">
    <p>&copy; <?php echo date("Y"); ?> Student Internship Management System. All rights reserved.</p>
    <p>Version 1.0</p>
    <p>Developer: Isaack Changawa (zak)</p>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form').forEach(function(f) {
        f.addEventListener('submit', function() {
            var btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = 'Processing…';
            }
        });
    });
});
</script>
</body>
</html>
