<?php

use YesWiki\Bazar\Controller\FormController;
use YesWiki\Bazar\Service\FormManager;
use YesWiki\Bazar\Service\SearchManager;

if (isset($_REQUEST['demand'])) {
    if ('POST' == $_SERVER['REQUEST_METHOD'] && empty($_POST)) {
        $_POST = json_decode(file_get_contents('php://input'), true) ?? [];
    }

    // preparation des parametres passés
    $page = ($_REQUEST['page'] ?? '');
    $form = ($_REQUEST['id'] ?? ($_REQUEST['form'] ?? ''));
    $tags = ($_REQUEST['tags'] ?? '');
    $order = ($_REQUEST['order'] ?? 'alphabetique');
    $html = (isset($_REQUEST['html']) && '1' == $_REQUEST['html'] ? $_REQUEST['html'] : '');
    $list = ($_REQUEST['list'] ?? '');
    $idfiche = ($_REQUEST['id_fiche'] ?? '');
    $pagetag = ($_REQUEST['pagetag'] ?? '');
    $is_semantic = ($_REQUEST['ld'] ?? '');

    $vSearchManager = $this->services->get(SearchManager::class);

    // On recupere les parametres query pour une requete specifique
    // NOTE : It doesn't seem to be used
    $query = ($_REQUEST['query'] ?? '');
    if (!empty($query)) {
        $vQueries = $vSearchManager->parseQuery($query);
    } else {
        $vQueries = [];
    }

    header('Content-type: '.($is_semantic ? 'application/ld+json' : 'application/json').'; charset=UTF-8');
    header('Access-Control-Allow-Origin: *');

    if ('OPTIONS' == $_SERVER['REQUEST_METHOD']) {
        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) && (
            'POST' == $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']
            || 'DELETE' == $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']
            || 'PUT' == $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']
        )) {
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Allow-Headers: X-Requested-With');
            header('Access-Control-Allow-Headers: Content-Type');
            header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT');
            header('Access-Control-Max-Age: 86400');
        }

        exit;
    }

    // différents services disponibles
    switch ($_REQUEST['demand']) {
        case 'lists':
            // listes bazar
            echo json_encode(baz_valeurs_liste($list));

            break;

        case 'entry':
            // données d'une fiche bazar
            if (empty($idfiche)) {
                echo json_encode(['error' => 'no id_fiche specified.']);
            } else {
                $wikipage = $this->LoadPage($idfiche);
                if ($wikipage) {
                    if ($this->HasAccess('read', $idfiche)) {
                        if (1 == $html) {
                            echo json_encode(['html' => baz_voir_fiche(0, $idfiche)]);
                        } else {
                            $decoded_entry = json_decode($wikipage['body'], true);
                            echo json_encode($decoded_entry);
                        }
                    } else {
                        echo json_encode(['error' => 'You have no right to access to entry \''.$idfiche.'\'.']);
                    }
                } else {
                    echo json_encode(['error' => 'id_fiche '.$idfiche.' not found.']);
                }
            }

            break;

        case 'template':
            // les templates bazar, pour afficher dans d'autres applis
            // on peut préciser dans l'url type=form (template formulaire) ou type=entry (template fiche)
            if (empty($form)) {
                echo json_encode(['error' => 'no form id specified.']);
            } else {
                $_REQUEST['id_typeannonce'] = $form;
                $tab_nature = baz_valeurs_formulaire($_REQUEST['id_typeannonce']);
                $GLOBALS['_BAZAR_']['typeannonce'] = $tab_nature['bn_label_nature'];
                $GLOBALS['_BAZAR_']['condition'] = $tab_nature['bn_condition'];
                $GLOBALS['_BAZAR_']['template'] = $tab_nature['bn_template'];
                $type = ($_REQUEST['type'] ?? 'form');
                if ('entry' == $type) { // template d'une fiche bazar
                    $res = '';
                    $formtemplate = '';
                    $tableau = formulaire_valeurs_template_champs($tab_nature['bn_template']);
                    for ($i = 0; $i < count($tableau); ++$i) {
                        if (
                            'liste' == $tableau[$i][0] || 'checkbox' == $tableau[$i][0]
                            || 'listefiche' == $tableau[$i][0] || 'checkboxfiche' == $tableau[$i][0]
                        ) {
                            $nom_champ = $tableau[$i][0].$tableau[$i][1].$tableau[$i][6];
                        } elseif ('image' == $tableau[$i][0] || 'fichier' == $tableau[$i][0]) {
                            $nom_champ = $tableau[$i][0].$tableau[$i][1];
                        } elseif ('titre' == $tableau[$i][0]) {
                            $nom_champ = 'bf_titre';
                        } else {
                            $nom_champ = $tableau[$i][1];
                        }

                        if ('titre' == $tableau[$i][0] || 'bf_titre' == $nom_champ) {
                            $res .= '<h2 class="entry-title">{{{bf_titre}}}</h2>'."\n\n";
                        } elseif ('image' == $tableau[$i][0]) {
                            $url = str_replace('wakka.php?wiki=', '', $this->config['base_url']);
                            $res .= '{{#if '.$nom_champ.'}}'."\n"
                                .'<img loading="lazy" class="img-responsive img-centered" src="'.$url.'cache/vignette_{{'.$nom_champ
                                .'}}" alt="{{'.$nom_champ.'}}">'."\n".'{{/if}}'."\n\n";
                        } elseif ('labelhtml' == $tableau[$i][0]) {
                            $res .= $tableau[$i][0]($formtemplate, $tableau[$i], 'html', []);
                        } elseif ('inscriptionliste' == $tableau[$i][0] || 'utilisateur_wikini' == $tableau[$i][0]) {
                        } elseif (
                            'liste' == $tableau[$i][0] || 'textelong' == $tableau[$i][0]
                            || 'jour' == $tableau[$i][0] || 'listedatefin' == $tableau[$i][0]
                            || 'listedatedeb' == $tableau[$i][0] || 'champs_mail' == $tableau[$i][0]
                            || 'lien_internet' == $tableau[$i][0]
                        ) {
                            $res .= '{{#if '.$nom_champ.'}}'."\n"
                                .'<div class="BAZ_rubrique" data-id="'.$nom_champ.'">'."\n"
                                .'<span class="BAZ_label">'.$tableau[$i][2].' :</span>'."\n"
                                .'<span class="BAZ_texte">{{{'.$nom_champ.'}}}</span>'."\n"
                                .'</div> <!-- /.BAZ_rubrique -->'."\n"
                                .'{{/if}}'."\n\n";
                        } elseif (function_exists($tableau[$i][0])) {
                            // TODO replace with BazarField
                            $texte = trim(
                                $tableau[$i][0](
                                    $formtemplate,
                                    $tableau[$i],
                                    'html',
                                    [$nom_champ => '{{'.$nom_champ.'}}']
                                )
                            );
                            if ('checkbox' == $tableau[$i][0]) {
                                $texte = preg_replace(
                                    '|<span class="BAZ_texte">.*</span>|Uis',
                                    '<span class="BAZ_texte">{{{'.$nom_champ.'}}}</span>',
                                    $texte
                                );
                            }
                            $res .= '{{#if '.$nom_champ.'}}'."\n".$texte."\n".'{{/if}}'."\n\n";
                        }
                    }
                    echo $res;
                } elseif ('form' == $type) { // template d'un formulaire bazar
                    $url = $this->href('json', $this->GetPageTag(), 'demand=save_entry');

                    // generation du formulaire
                    $form = $GLOBALS['wiki']->services->get(FormController::class)->create($form);
                    $form = preg_replace(
                        '~<div class="form-actions">.*</div>~Ui',
                        "\n".'<a href="#" class="btn btn-block btn-positive btn-save">'._t('BAZ_SAVE').'</a>',
                        $form
                    );
                    $form = preg_replace('~<div id="map".*>~Ui', "\n".'<div id="map">', $form);
                    echo json_encode(['html' => $form]);
                }
            }

            break;

        case 'forms':
            $formManager = $this->services->get(FormManager::class);
            if (is_array($form) && count($form) > 0) {
                $formsIds = array_filter($form, function ($id) {
                    return strval($id) == strval(intval($id));
                });
            } elseif (!empty($form)) {
                if (strval($form) === strval(intval($form))) {
                    $formsIds = [$form];
                } else {
                    $formsIds = [];
                }
            } else {
                $formsIds = [];
                $forms = $formManager->getAll();
            }

            if (1 == count($formsIds)) {
                $form = $formManager->getOne($formsIds[0]);
                if (!empty($form)) {
                    echo json_encode([0 => $form]);

                    break;
                }
                $forms = [];
            } elseif (count($formsIds) > 1) {
                $forms = $formManager->getMany($formsIds);
                $forms = array_filter($forms, function ($form) {
                    return !empty($form);
                });
            }

            if (empty($forms)) {
                echo json_encode(new ArrayObject());
            } else {
                // sort on label
                usort($forms, function ($a, $b) {
                    if (
                        !isset($a['bn_label_nature'])
                        || !isset($b['bn_label_nature'])
                        || $a['bn_label_nature'] == $b['bn_label_nature']
                    ) {
                        return 0;
                    }

                    return ($a['bn_label_nature'] < $b['bn_label_nature']) ? -1 : 1;
                });
                $forms = _convert($forms, 'UTF-8');
                echo json_encode($forms);
            }

            break;

        case 'entries':
            if (!empty($form)) {
                $forms = explode(',', $form);
                if (1 == count($forms)) {
                    header('Location: '.$this->href('', 'api/forms/'.$forms[0].'/entries'.($is_semantic ? '/json-ld' : '')));

                    break;
                }
                header('Location: '.$this->href('', 'api/entries'.($is_semantic ? '/json-ld' : ''), ['query' => 'id_typeannonce='.$form], false));

                break;
            }
            header('Location: '.$this->href('', 'api/entries'.($is_semantic ? '/json-ld' : '')));

            break;

        case 'pages':
            header('Location: '.$this->href('', 'api/pages'));

            break;

        case 'comments':
            // les commentaires wiki
            echo json_encode($this->LoadRecentComments());

            break;
    }
}
