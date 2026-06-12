<?php

// TODO : a basculer dans __show.php

$destination = $this->GetParameter('destination');
if (empty($destination)) {
    echo _t('LANG_DESTINATION_REQUIRED');
}

$flagfile = 'tools/lang/presentation/images/'.$destination.'.png';

if (!file_exists($flagfile)) {
    $img = $destination; // we are using the iso code if no flag available
} else {
    $img = '<img loading="lazy" src="'.$flagfile.'" title="'.$destination.'" alt="'.$destination.' language">';
}

$wikireq = $_GET['wiki'] ?? null;

$currentMethod = empty($this->method) ? '' : '/'.$this->method;
$currentTag = (false !== strpos($wikireq, '/'))
        ? substr($wikireq, 0, -strlen($currentMethod))
        : $wikireq;

$queries = [];
parse_str($_SERVER['QUERY_STRING'], $queries);
unset($queries[$wikireq], $queries['wiki']);

$queries['lang'] = $destination;

// remove $_GET['lang'] because it is used by Href
if (isset($_GET['lang'])) {
    $previousLang = $_GET['lang'];
    unset($_GET['lang']);
}
// Todo : utiliser template
echo '<a href="'.$this->Href($wikireq === $currentTag ? '' : $this->method, $currentTag, $queries, false).'">'.$img.'</a>';

if (isset($previousLang)) {
    $_GET['lang'] = $previousLang;
    unset($previousLang);
}
