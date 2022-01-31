<?php

echo "<!-- footer -->\n"; ?>
<div class="container">
    <hr id="footer-hr" />
    <footer>
        <p class="muted text-sm-center">
            <small><?php echo xlt('Patient Portal') . " v" . text((new OpenEMR\Services\VersionService())->asString()); ?> Copyright &copy; <?php echo date('Y'); ?> By
                sjpadgett@gmail.com License GPLv3
            </small>
        </p>
    </footer>
</div>
</body>
</html>
