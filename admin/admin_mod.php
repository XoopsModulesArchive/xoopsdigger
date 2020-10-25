<?php
require_once dirname(__DIR__, 3) . '/mainfile.php';
require_once __DIR__ . '/admin_header.php';
require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
if (file_exists('../language/' . $xoopsConfig['language'] . '/admin.php')) {
    include '../language/' . $xoopsConfig['language'] . '/admin.php';
} else {
    include '../language/english/admin.php';
}
$op = 'show_state';
if (!empty($_POST['op'])) {
    $op = $_POST['op'];
} elseif (!empty($_GET['op'])) {
    $op = $_GET['op'];
}
if (!empty($_POST['mod'])) {
    $mod = $_POST['mod'];
} elseif (!empty($_GET['mod'])) {
    $mod = urldecode($_GET['mod']);
} else {
    redirect_header('index.php');
}
require_once XOOPS_ROOT_PATH . "/modules/xoopsdigger/admin/modules/$mod/class.diggermodule.php";

xoops_cp_header();
if (!isset($diggerMod[$mod])) {
    $diggerMod[$mod] = [];

    $diggerMod[$mod]['weight'] = 1.0;
} elseif (!isset($diggerMod[$mod]['weight'])) {
    $diggerMod[$mod]['weight'] = 1.0;
}
if (1 == $_POST['post_xd_md_weight']) {
    updateDocumentWeight($_POST['_xd_md_weight'] * $diggerMod[$mod]['weight'], $digModule->updateWeightLink($_POST['_xd_md_id']));
}
$doclink = '';
if (!empty($_POST['link'])) {
    $doclink = $_POST['link'];
} elseif (!empty($_GET['link'])) {
    $doclink = urldecode($_GET['link']);
}
OpenTable();
    echo '<h2>' . _XD_MOD_ACTION . '</h2>';
    echo '<table border="0" align="center">';
    echo '<tr><td><a href="' . XOOPS_URL . '/modules/xoopsdigger/admin/admin_mod.php?mod=' . urlencode($mod) . '&op=show_state">' . _XD_MOD_ADMIN . '</a></td></tr>';
    echo '<tr><td><a href="' . XOOPS_URL . '/modules/xoopsdigger/admin/admin_mod.php?mod=' . urlencode($mod) . '&op=show_all&min=0">' . _XD_MOD_SHOW_ALL . '</a></td></tr>';
    if ($digModule->hasSpecialAction()) {
        $actions = $digModule->getSpecialAction();

        foreach ($actions as $key => $value) {
            echo '<tr><td><a href="' . XOOPS_URL . '/modules/xoopsdigger/admin/admin_mod.php?mod=' . urlencode($mod) . '&op=' . $value . "\">$key</a></td></tr>\n";
        }
    }
    echo '</table>';
CloseTable();
echo '<br><br>';

switch ($op) {
    case 'show_state':
        show_state();
        break;
    case 'delete':
        remove_document($doclink);
        show_state();
        break;
    case 'update':
        $docinfo = $digModule->getDocumentInfo($doclink);
        //print_r($docinfo['text']);
        reindex_content($docinfo['title'], $doclink, $mod, $docinfo['text'], $docinfo['weight'], $docinfo['date'], $docinfo['remove_html'], $docinfo['remove_bb'], $docinfo['remove_ex_word']);
        show_state();
        break;
    case 'index':
        $docinfo = $digModule->getDocumentInfo($doclink);
        index_content($docinfo['title'], $doclink, $docinfo['doc_weight'] * $diggerMod[$mod]['weight'], $mod, $docinfo['text'], $docinfo['weight'], $docinfo['date'], $docinfo['remove_html'], $docinfo['remove_bb'], $docinfo['remove_ex_word']);
        show_state();
        break;
    case 'show_all':
        show_all((!empty($_POST['min']) ? $_POST['min'] : (!empty($_GET['min']) ? $_GET['min'] : 0)));
        break;
    default:
        if ($digModule->hasSpecialAction()) {
            $digModule->processSpecialAction($op);
        } else {
            show_state();
        }
        break;
}

echo '<br><br>';
OpenTable();
$form = new XoopsThemeForm(_XD_MD_WEIGHT, 'post_new', 'admin_mod.php');
$form->setExtra("enctype='multipart/form-data'");
$form->addElement($digModule->weightFormText($xoopsConfig['language']));
$form->addElement(new XoopsFormText(_XD_MD_NEW_WEIGHT, '_xd_md_weight', 10, 10, '1.0'));
$form->addElement(new XoopsFormHidden('post_xd_md_weight', '1'));
$form->addElement(new XoopsFormHidden('mod', $mod));
$form->addElement(new XoopsFormButton('', 'submit', _XD_UPDATE_WEIGHT, 'submit'));
//$form->setRequired(array('_xd_md_id','_xd_md_weight'));
$form->display();
CloseTable();
xoops_cp_footer();
function show_state()
{
    global $db,$digModule,$mod;

    if (!$elemres = $db->query($digModule->getSqlQuery())) {
        return;
    }

    $elements = [];

    if ($digModule->hasDate()) {
        while ($elem = $db->fetch_array($elemres)) {
            $elements[$digModule->getLinkFromQuery($elem)] = $digModule->getDateFromQuery($elem);
        }
    }

    $GLOBALS['xoopsDB']->freeRecordSet($elemres);

    OpenTable();

    $tdoc = $db->prefix('xd_document');

    if ($docres = $db->query("SELECT link,date,doc_weight,title FROM $tdoc WHERE modname='$mod'")) {
        if ($digModule->hasDate()) {
            $updates = '<br><h2>' . _XD_DOC_UP . '</h2><table border="1"><tr><td><i>' . _XD_TITLE . '</i></td><td><i>' . _XD_WEIGHT . '</i></td><td><i>' . _XD_DOC_UP_ACTION . '</i></td></tr>';
        }

        $deletes = '<br><h2>' . _XD_DOC_DELETE . '</h2><table border="1"><tr><td><i>' . _XD_TITLE . '</i></td><td><i>' . _XD_WEIGHT . '</i></td><td><i>' . _XD_DOC_DELETE_ACTION . '</i></td></tr>';

        while ($doc = $db->fetch_array($docres)) {
            if (empty($elements[$doc['link']])) {
                $deletes .= '<tr><td>' . $doc['title'] . '</td><td>' . $doc['doc_weight'] . '</td><td><a href="' . XOOPS_URL . '/modules/xoopsdigger/admin/admin_mod.php?mod=' . urlencode($mod) . '&op=update&link=' . urlencode($doc['link']) . '">' . _XD_DELETE . '</a></td></tr>';
            } elseif ($digModule->hasDate() && $digModule->compareDate($doc['date'], $elements[$doc['link']]) > 0) {
                $updates .= '<tr><td>' . $doc['title'] . '</td><td>' . $doc['doc_weight'] . '</td><td><a href="' . XOOPS_URL . '/modules/xoopsdigger/admin/admin_mod.php?mod=' . urlencode($mod) . '&op=update&link=' . urlencode($doc['link']) . '">' . _XD_UPDATE . '</a></td></tr>';
            }

            $elements[$doc['link']] = -1;
        }

        $GLOBALS['xoopsDB']->freeRecordSet($docres);

        if ($digModule->hasDate()) {
            echo $updates . '</table><br><br>';
        }

        echo $deletes . '</table><br><br>';
    }

    echo '<br><h2>' . _XD_DOC_INDEX . '</h2><table border="1"><tr><td>' . _XD_TITLE . '</td><td>' . _XD_DOC_INDEX_ACTION . '</td></tr>';

    foreach ($elements as $key => $value) {
        if (-1 != $value) {
            echo '<tr><td>' . $key . '</td><td><a href="' . XOOPS_URL . '/modules/xoopsdigger/admin/admin_mod.php?mod=' . urlencode($mod) . '&op=index&link=' . urlencode($key) . '">' . _XD_INDEX . '</a></td></tr>';
        }
    }

    unset($elements);

    echo '</table><br>';

    CloseTable();
}
function show_all($min)
{
    global $db,$mod,$diggerMod;

    OpenTable();

    [$count] = $db->fetch_row($db->query('SELECT COUNT(*) as co FROM ' . $db->prefix('xd_document') . " WHERE modname='$mod'"));

    echo '<table border="0"><tr><td align="left">';

    if ($min > 0) {
        echo '<a href="' . XOOPS_URL . '/modules/xoopsdigger/admin/admin_mod.php?op=show_all&mod=' . urlencode($mod) . '&min=' . max(0, $min - 30) . '">' . _XD_PREVIOUS . '</a>';
    }

    echo "</td><td align='center' width='100%'>$count " . _XD_DOC_FOUND . '</td><td>';

    if ($min < $count - 30) {
        echo '<a href="' . XOOPS_URL . '/modules/xoopsdigger/admin/admin_mod.php?op=show_all&mod=' . urlencode($mod) . '&min=' . ($min + 30) . '">' . _XD_NEXT . '</a>';
    }

    echo '</td></tr></table>';

    $result = $db->query('SELECT title,doc_weight,link FROM ' . $db->prefix('xd_document') . " WHERE modname='$mod' ORDER BY doc_weight DESC LIMIT $min,30");

    echo '<table border="1" width="100%"><tr><td>' . _XD_TITLE . '</td><td>' . _XD_LOCAL_WEIGHT . '</td><td>' . _XD_GLOBAL_WEIGHT . '</td><td>' . _XD_FORCE_REINDEX . '</td></tr>';

    while ($doc = $db->fetch_array($result)) {
        echo '<tr><td>' . $doc['title'] . '</td><td>' . ($doc['doc_weight'] / $diggerMod[$mod]['weight']) . '</td><td>' . $doc['doc_weight'] . '</td><td><a href="' . XOOPS_URL . '/modules/xoopsdigger/admin/admin_mod.php?mod=' . urlencode($mod) . '&op=update&link=' . urlencode($doc['link']) . '">' . _XD_REINDEX . '</a></td></tr>';
    }

    echo '</table>';

    echo '<table border="0"><tr><td align="left">';

    if ($min > 0) {
        echo '<a href="' . XOOPS_URL . '/modules/xoopsdigger/admin/admin_mod.php?op=show_all&mod=' . urlencode($mod) . '&min=' . max(0, $min - 30) . '">' . _XD_PREVIOUS . '</a>';
    }

    echo '</td><td>';

    if ($min < $count - 30) {
        echo '<a href="' . XOOPS_URL . '/modules/xoopsdigger/admin/admin_mod.php?op=show_all&mod=' . urlencode($mod) . '&min=' . ($min + 30) . '">' . _XD_NEXT . '</a>';
    }

    echo '</td></tr></table>';

    CloseTable();
}
