<?php

// Execute le gestion des fichiers lier par l'action {{attach}}
// Necessite le fichier actions/attach.php pour fonctionner
// voir actions/attach.php ppour la documentation

if ($this->HasAccess('write')) {
    if (!class_exists('Attach')) {
        include 'tools/attach/libs/Attach.php';
    }
    $att = new Attach($this);
    $att->doFilemanagerAction();
    unset($att);
} else {
    echo '<div class="alert alert-danger">'._t('ATTACH_NO_RIGHTS_TO_ACCESS_FILEMANAGER').'.</div>'."\n";
}
