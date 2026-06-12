<?php

// Execute le download des fichiers lier par l'action {{attach}}
// Necessite le fichier actions/attach.php pour fonctionner
// voir actions/attach.php ppour la documentation

if (!class_exists('Attach')) {
    include 'tools/attach/libs/Attach.php';
}
$att = new Attach($this);
$att->doDownload();
unset($att);
