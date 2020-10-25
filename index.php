<?php
// ------------------------------------------------------------------------- //
//               E-Xoops Digger: Advanced Search Engine                      //
//                < http://http://xoopsdigger.sf.net >                       //
// ------------------------------------------------------------------------- //
// Original Author: Matthias Studer
// Author Website : http://www.ired.org, http://xoopsdigger.sf.net
// License Type   : GPL: See /manual/LICENSES/GPL.txt
// ------------------------------------------------------------------------- //
require __DIR__ . '/header.php';
$searchTimer = new SearchTimer();
function search($query, $modules, $type, $min, $resultperpage)
{
    global $db, $searchTimer,$diggerConf;

    $searchTimer->start('search');

    $searchTimer->start('preparation');

    $searchTimer->start('wordsextract');

    $words = getWords($query);

    $searchTimer->stop('wordsextract');

    $searchTimer->start('buildquery');

    //Operator is or

    //Querying for word_id

    $opOr = 0 == strcasecmp(trim($operator), 'or');

    switch ($type) {
    case 'exact':
        $searchBegin = '=\'';
        $searchEnd = '\'';
        break;
    case 'fuzzy':
        $searchBegin = ' LIKE \'%';
        $searchEnd = '%\'';
        break;
    default:
        $searchBegin = ' LIKE \'';
        $searchEnd = '%\'';
    }

    $tengine = $db->prefix('xd_engine');

    $tdoc = $db->prefix('xd_document');

    if (in_array('all', $modules, true)) {
        $modulesquery = '';
    } else {
        $modulesquery = '';

        foreach ($modules as $mod) {
            $modulesquery .= " AND $tdoc.modname='$mod'";
        }
    }

    $sql = "SELECT SQL_BIG_RESULT $tengine.doc_id,$tdoc.title, $tdoc.link,$tdoc.modname,sum($tengine.weight) as w FROM $tdoc,$tengine," . $db->prefix('xd_keyword') . ' as k'
    . " WHERE $tengine.word_id=k.id AND $tdoc.id=$tengine.doc_id $modulesquery AND (";

    $sqlarray = [];

    foreach ($words as $w => $wcount) {
        $twolet = mb_substr($w, 0, 2);

        $sqlarray[] = "(k.twoletter='$twolet' AND k.word" . $searchBegin . $w . $searchEnd . ')';
    }

    $sql .= implode(' OR ', $sqlarray) . ") GROUP BY $tengine.doc_id";

    //echo $sql.'<br><br>';

    unset($sqlarray);

    /*$sql="SELECT $tengine.doc_id, SUM(weight) as w WHERE ";
    while(list($word_id)=$db->fetch_row($wordresult)){
        $db->query($sql.
    }
    $sql="SELECT SQL_BIG_RESULT $tdoc.id";
    if(!$opOr)$sql.=', count(weight) as c, modname, title, link';
    $sql.=" FROM $tkey,$tdoc WHERE $tkey.doc_id=$tdoc.id AND ";

    $sql.=join(' OR ',$sqlarray).' GROUP BY doc_id';
    unset($sqlarray);*/

    $searchTimer->stop('buildquery');

    $searchTimer->stop('preparation');

    $searchTimer->start('resultsretriving');

    $searchTimer->start('sqlquery');

    $results = $db->query($sql);

    //echo $db->error()."   <br>";

    $searchTimer->stop('sqlquery');

    $docarray = [];

    $docinfo = [];

    //$last_doc_id=-1;

    $wordcount = count($words);

    while ($entry = $db->fetch_array($results)) {
        //if($opOr||$entry['c']>=$wordcount){

        if (!isset($docinfo[$entry['doc_id']])) {
            $docinfo[$entry['doc_id']] = [$entry['modname'], $entry['title'], $entry['link']];
        }

        $docarray[$entry['doc_id']] += $entry['w'];

        //}
    }

    $GLOBALS['xoopsDB']->freeRecordSet($results);

    $searchTimer->stop('resultsretriving');

    $searchTimer->start('sortingresults');

    arsort($docarray, SORT_NUMERIC);

    $searchTimer->stop('sortingresults');

    $counter = 0;

    $searchTimer->start('printingresults');

    $max_weight = -1;

    $resultcount = count($docarray);

    $searchTimer->stop('search');

    $time = $searchTimer->get('search');

    $wordsreg = implode('|', array_keys($words));

    $max = $min + $resultperpage;

    $navbar = buildNavigationBar($min, $resultcount, $resultperpage, $type, $modules, $query);

    xd_theme_begin($navbar, $resultcount, $time, $modules, $query);

    foreach ($docarray as $doc_id => $we) {
        if (-1 == $max_weight) {
            $max_weight = $we;
        }

        if ($counter >= $max) {
            break;
        }

        if ($counter >= $min) {
            xd_theme_result(getSnippet($doc_id, $wordsreg), $docinfo[$doc_id][1], $docinfo[$doc_id][0], $docinfo[$doc_id][2], number_format($we / $max_weight * 100, 2, '.', ' '), $query);
        }

        $counter++;
    }

    xd_theme_end($navbar, $resultcount, $time, $modules, $query);

    $searchTimer->stop('printingresults');

    if ('1' == $diggerConf['debug']) {
        $searchTimer->display();
    }
}
function getSnippet($doc_id, $wordsreg)
{
    global $diggerConf;

    if (0 == $diggerConf['use_cache']) {
        return '';
    }

    if (!@file_exists(XOOPS_ROOT_PATH . '/modules/xoopsdigger/cache/text/' . $doc_id . '.txt')) {
        return '';
    }

    $file = fopen(XOOPS_ROOT_PATH . '/modules/xoopsdigger/cache/text/' . $doc_id . '.txt', 'rb');

    $matchcount = 0;

    $text = '...';

    $regexp = "§\s.{0,50}(" . $wordsreg . ").{0,50}\s§i";

    while ($matchcount < $diggerConf['snippet'] && $line = fgets($file, 1024)) {
        if (preg_match($regexp, $line, $match)) {
            //print_r($match);

            $text .= str_replace($match[1], '<span class="xd_highlight">' . $match[1] . '</span>', $match[0]) . '...';

            $matchcount++;
        }
    }

    return $text;
}
function buildNavigationBar($min, $resultcount, $resultperpage, $type, $modules, $query)
{
    $navbar = ['page' => []];

    $numpage = ceil($resultcount / $resultperpage);

    $navbar['numpage'] = $numpage;

    for ($i = 0; $i < $numpage; $i++) {
        $currentmin = $i * $resultperpage;

        $currentmax = $currentmin + $resultperpage;

        if ($min >= $currentmin && $min < $currentmax) {
            $navbar['current'] = $i;
        }

        $navbar['page'][$i] = XOOPS_URL . '/modules/xoopsdigger/index.php?type=' . $type . '&mod=' . urlencode(implode('/', $modules)) . '&min=' . $currentmin . '&numres=' . $resultperpage . '&query=' . urlencode($query);
    }

    return $navbar;
}
$min = 0;
if (!empty($_POST['min'])) {
    $min = $_POST['min'];
} elseif (!empty($_GET['min'])) {
    $min = $_GET['min'];
}
if ($min < 0) {
    $min = 0;
}
$numres = 10;
if (!empty($_POST['numres'])) {
    $numres = $_POST['numres'];
} elseif (!empty($_GET['numres'])) {
    $numres = $_GET['numres'];
}
$type = 'normal';
if (!empty($_POST['type'])) {
    $type = $_POST['type'];
} elseif (!empty($_GET['type'])) {
    $type = $_GET['type'];
}

if (!empty($_POST['query'])) {
    $query = $_POST['query'];
} elseif (!empty($_GET['query'])) {
    $query = urldecode($_GET['query']);
}
if (!empty($_POST['mod'])) {
    $mod = explode('/', $_POST['mod']);
} elseif (!empty($_GET['mod'])) {
    $mod = explode('/', urldecode($_GET['mod']));
}
if (!isset($mod)) {
    $mod = ['all'];
}
xd_theme_search_form($query, $min, $numres, $mod);

if (isset($query)) {
    search($query, $mod, $type, $min, $numres);
}
require XOOPS_ROOT_PATH . '/footer.php';
