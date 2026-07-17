</div> <!-- content -->
</div> <!-- layout -->

<footer class="site-footer">
    <hr class="footer-separator">
    <p>&copy; <?php echo date("Y"); ?> <?= __('Student Internship Management System') ?>. <?= __('All rights reserved.') ?></p>
    <p><?= __('Version 1.0') ?></p>
    <p><?= __('Developer: Isaack Changawa (zak)') ?></p>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form').forEach(function(f) {
        var submitted = false;
        f.addEventListener('submit', function() {
            if (submitted) return false;
            submitted = true;
            var btn = this.querySelector('button[type="submit"]');
            if (btn) {
                btn.innerHTML = 'Processing…';
            }
        });
    });
});
</script>
</body>
</html>
