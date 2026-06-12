<?php

use YesWiki\Core\Controller\CsrfTokenController;
use YesWiki\Core\Service\DbService;

if (($this->UserIsOwner() || $this->UserIsAdmin())
    && isset($_GET['eraselink'])
    && 'oui' === $_GET['eraselink']
    && isset($_GET['confirme'])
    && ('oui' === $_GET['confirme'])
) {
    try {
        if ($this->services->get(CsrfTokenController::class)->checkToken('main', 'POST', 'csrf-token', false)) {
            $tag = $this->GetPageTag();
            $dbService = $this->services->get(DbService::class);
            $dbService->query("DELETE FROM {$dbService->prefixTable('links')} WHERE to_tag = '".$dbService->escape($tag)."'");
        }
    } catch (Throwable $th) {
        // do nothing
    }
}
