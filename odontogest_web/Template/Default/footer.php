    </main><!-- /#mainContent -->

    <footer class="footer">
        &copy; <?= date('Y') ?> <?= APP_NAME ?> &mdash; Desarrollado por
        <a href="#" style="color:#1A56AB;font-weight:600;">DeskCod</a>
    </footer>

    <!-- Bootstrap 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if (!empty($extraJs)): foreach ($extraJs as $js): ?>
        <script src="<?= APP_URL . htmlspecialchars($js) ?>"></script>
    <?php endforeach; endif; ?>

    <?php if (!empty($_SESSION['flash'])): $f = $_SESSION['flash']; unset($_SESSION['flash']); ?>
    <script>
    document.addEventListener('DOMContentLoaded',()=>Swal.fire({
        icon:'<?= htmlspecialchars($f['type'] ?? 'info', ENT_QUOTES) ?>',
        title:'<?= htmlspecialchars($f['title'] ?? 'Aviso', ENT_QUOTES) ?>',
        text:'<?= htmlspecialchars($f['message'] ?? '', ENT_QUOTES) ?>',
        confirmButtonColor:'#1A56AB'
    }));
    </script>
    <?php endif; ?>
</body>
</html>
