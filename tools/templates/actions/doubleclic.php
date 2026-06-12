<?php

$page = $this->GetParameter('page');
$isIframe = $this->GetParameter('iframe') && (!isset($_GET['iframelinks']) or '0' != $_GET['iframelinks']);
if ('show' == $this->GetMethod() && $this->HasAccess('write', $page)) {
    $method = $isIframe ? 'editiframe' : 'edit';
    // javascript du double clic (on peut passer en parametre une page wiki au editer en doublecliquant)
    if (!empty($page)) {
        echo 'ondblclick="document.location=\''.$this->href($method, $page).'\';" ';
    } else {
        echo 'ondblclick="document.location=\''.$this->href($method).'\';" ';
    }
}
