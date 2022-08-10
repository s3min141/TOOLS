<?php
    displayError(false);
    $sess_allowed_sec = 3600 * 24 * 7; // 7 days
    ini_set("session.gc_maxlifetime", $sess_allowed_sec);
    session_set_cookie_params($sess_allowed_sec);
    session_cache_expire($sess_allowed_sec);
    session_name("semin_tools");
    session_start();
    
    // DB Credentials (MySQL)
    define("__DB_HOST__", "localhost");
    define("__DB_USER__", "s3min");
    define("__DB_PASS__", "semin31242");
    define("__DB_BASE__", "tools_database");

    // Base URL //
    define("__HOST__", "https://" . $_SERVER['SERVER_NAME']);

    // Hash Salt //
    define("__HASH_SALT__", "RFaJoxUvSmUvVS2f29o89tmQrv7p1hauvvw4A6nMfW2PdF6O2gm6ND0XAxmLG33rpdbsCX1QArXJRcgB");

    // Template Dir //
    define("__TEMPLATE__", $_SERVER['DOCUMENT_ROOT'] . "/template/");

    // Drive Dir //
    define("__DRIVE__", $_SERVER['DOCUMENT_ROOT'] . "/drive/");

    require_once "sql.php";
    $sql = new SQL();
    $sql->ConnectDB(__DB_HOST__, __DB_USER__, __DB_PASS__, __DB_BASE__);
    if (!$sql->CheckConn()) {
        die("DB is down");
    }
?>