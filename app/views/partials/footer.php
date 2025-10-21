<?php

if (!empty($scripts) && is_array($scripts)) {
    foreach ($scripts as $script) {
        $src = $script;
        if (!str_starts_with($src, 'http')) {
            $src = rtrim(APP_BASE_URL ?: '', '/') . $script;
        }
        echo '<script src="' . htmlspecialchars($src, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" defer></script>';
    }
}
?>
</body>
</html>
