<?php

// Execute le gestion des fichiers lier par l'action {{attach}}
// Necessite le fichier actions/attach.php pour fonctionner
// voir actions/attach.php ppour la documentation

ob_start();
?>
<div class="page">
    <?php
    if ($this->UserIsOwner() || $this->UserIsAdmin()) {
        if (!class_exists('Attach')) {
            include 'tools/attach/libs/Attach.php';
        }
        $att = new Attach($this);
        $att->doFilemanager();
        unset($att);
    } else {
        echo $this->Format('//'._t('FILEMANAGER_ACTION_NEED_ACCESS').'//');
    }
?>
</div>
<?php
$output = ob_get_contents();
ob_end_clean();
echo $this->Header().$output.$this->Footer(); ?>
