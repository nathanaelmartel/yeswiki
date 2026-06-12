<?php

use YesWiki\Templates\Service\Utils;

$title = htmlspecialchars($this->services->get(Utils::class)->getTitleFromBody($this->page), ENT_COMPAT | ENT_HTML5);
if ($title) {
    echo $title;
} else {
    echo $this->GetPageTag();
}
