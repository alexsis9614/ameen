<?php
    define('STM_THEME_CHILD_VERSION', time());
    define('STM_THEME_CHILD_DIRECTORY', get_stylesheet_directory());
    define('STM_THEME_CHILD_DIRECTORY_URI', get_stylesheet_directory_uri());

    require_once __DIR__ . '/inc/enqueue.php';
    require_once __DIR__ . '/inc/otp.php';