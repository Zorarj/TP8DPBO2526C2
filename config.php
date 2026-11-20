<?php
// Konfigurasi database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tp_mvc25');

// Autoload: class dicari otomatis di folder models, views, dan controllers
spl_autoload_register(function (string $class) {
    foreach (['models', 'views', 'controllers'] as $dir) {
        $file = __DIR__ . "/$dir/$class.php";
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});
