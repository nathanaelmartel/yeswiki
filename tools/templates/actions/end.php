<?php

require_once 'includes/YesWikiPerformable.php';

use YesWiki\Core\Service\Performer;
use YesWiki\Templates\Service\Utils;

// classe css supplémentaire
$elem = $this->GetParameter('elem');
if (empty($elem)) {
    echo '<div class="alert alert-danger"><strong>'._t('TEMPLATE_ACTION_END').'</strong> : '._t('TEMPLATE_ELEM_PARAMETER_REQUIRED').'.</div>'."\n";

    return;
}
$pagetag = $this->GetPageTag();
$body = $this->page['body'] ?? '';
// teste s'il y a bien un element de fermeture associé avant d'ouvrir une balise
if (!isset($GLOBALS['check_'.$pagetag])) {
    $GLOBALS['check_'.$pagetag] = [];
}
if (!isset($GLOBALS['check_'.$pagetag][$elem])) {
    $GLOBALS['check_'.$pagetag][$elem] = $this->services->get(Utils::class)->checkGraphicalElements($elem, $pagetag, $body);
}

echo $this->services->get(Performer::class)->run($elem, 'action', [], true);
