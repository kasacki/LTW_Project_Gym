<footer>
    <p>&copy; 2026 FitLife Gym. LTW Project.</p>
</footer>
<?php if (isset($page_js)): ?>
    <?php
        $scriptName = basename($page_js);
        $scriptPath = __DIR__ . '/js/' . $scriptName . '.js';
        $scriptVersion = file_exists($scriptPath) ? filemtime($scriptPath) : time();
    ?>
    <script src="js/<?= htmlspecialchars($scriptName, ENT_QUOTES, 'UTF-8') ?>.js?v=<?= $scriptVersion ?>"></script>
<?php endif; ?>
</body>
</html>
