<?php
require_once __DIR__ . '/../../security.php';
require_localhost('ADMINER');

function adminer_object() {
    // Charger uniquement le plugin Frames (indispensable pour l'affichage en iframe)
    include_once "./plugins/frames.php";

    return new \Adminer\Plugins([
        new AdminerFrames(),
    ]);
}

// Inclure Adminer 5.4.2
include "./adminer-5.4.2-mysql.php";
?>
