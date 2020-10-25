<?php
// ------------------------------------------------------------------------- //
//               E-Xoops Digger: Advanced Search Engine                      //
//                < http://http://xoopsdigger.sf.net >                       //
// ------------------------------------------------------------------------- //
// Original Author: Matthias Studer
// Author Website : http://www.ired.org, http://xoopsdigger.sf.net
// License Type   : GPL: See /manual/LICENSES/GPL.txt
// ------------------------------------------------------------------------- //

//---------------------------------------------------------------------------------------//
/**
 * Write the current configuration to a file, specific modules information can be set or accessed through
 * the array : diggerMod
 */
function writeConfig()
{
    require_once XOOPS_ROOT_PATH . '/modules/xoopsdigger/cache/config.php';

    global $diggerConf,$diggerMod;

    $file = fopen(XOOPS_ROOT_PATH . '/modules/xoopsdigger/cache/config.php', 'wb');

    fwrite($file, "<?php\n");

    fwrite($file, '$diggerConf=' . arrayAsString($diggerConf) . ";\n\n");

    if (isset($diggerMod) && is_array($diggerMod)) {
        fwrite($file, '$diggerMod=' . arrayAsString($diggerMod) . ";\n");
    }

    fwrite($file, "?>\n");

    fclose($file);
}
//---------------------------------------------------------------------------------------//
/**
 * Utility function that return code for an array (this is a recursive function so it can write arrays
 * containing arrays.
 * @param array $array the array we want the code
 * @return string php array code
 */
function arrayAsString($array)
{
    $tmp = [];

    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $tmp[] = "\"$key\"=>" . arrayAsString($value);
        } else {
            $tmp[] = "\"$key\"=>\"$value\"";
        }
    }

    return 'array(' . implode(",\n", $tmp) . ")\n";
}
//Here are function for indexing documents

//---------------------------------------------------------------------------------------//
/**
 * index a given content into the database
 *
 * @param string $title the title of the document size must be <255 chars
 * @param string $link for the document THIS MUST BE UNIQUE and must start from modules (ie for news use news/article.php?storyid=$id
 * @param float $doc_weight the weight for this document modules weight must already be included
 * @param array $text the text that need to be indexed
 * @param array $weight array contening the weight for a part of text (base on same key)
 * @param int $date the date of the document to index (can be used to check update) as an int(10)
 * @param bool $remove_html wheter we should remove all html tags (including content)
 * @param bool $remove_bb wheter we should remove bbcode
 * @param bool $remove_ex_word wheter we should remove excluded words (not implemented yet)
 * @param mixed $modulename
 */
function index_content($title, $link, $doc_weight, $modulename, $text, $weight, $date = -1, $remove_html = true, $remove_bb = false, $remove_ex_word = true)
{
    global $db;

    $db->query('INSERT INTO ' . $db->prefix('xd_document') . " SET link='" . $GLOBALS['xoopsDB']->escape($link) . "', modname='$modulename', title='$title'" . (-1 != $date ? ", date='$date'" : '') . ", doc_weight=$doc_weight");

    //echo $db->error();

    $doc_id = $db->insert_id();

    priv_index_content($doc_id, $doc_weight, $text, $weight, $remove_html, $remove_bb, $remove_ex_word);

    $db->query('OPTIMIZE TABLE ' . $db->prefix('xd_engine') . ', ' . $db->prefix('xd_document') . ',' . $db->prefix('xd_keyword'));
}
//---------------------------------------------------------------------------------------//
/**
 * reindex/update a document DON'T USE index_content for an already indexed doc
 *
 * @param string $title the title of the document size must be <255 chars
 * @param string $link for the document THIS MUST BE UNIQUE and must start from modules (ie for news use news/article.php?storyid=$id
 * @param array $text the text that need to be indexed
 * @param array $weight array contening the weight for a part of text (base on same key)
 * @param int $date the date of the document to index (can be used to check update) as an int(10)
 * @param bool $remove_html wheter we should remove all html tags (including content)
 * @param bool $remove_bb wheter we should remove bbcode
 * @param bool $remove_ex_word wheter we should remove excluded words (not implemented yet)
 * @param mixed $modulename
 */
function reindex_content($title, $link, $modulename, $text, $weight, $date = -1, $remove_html = true, $remove_bb = false, $remove_ex_word = true)
{
    global $db;

    $result = $db->query('SELECT id,doc_weight FROM ' . $db->prefix('xd_document') . " WHERE link='" . $GLOBALS['xoopsDB']->escape($link) . "'");

    [$doc_id, $doc_weight] = $db->fetch_array($result);

    if (!isset($doc_id)) {
        die($db->error() . " while reindexing $link");
    }

    $db->query('UPDATE ' . $db->prefix('xd_document') . " SET link='" . $GLOBALS['xoopsDB']->escape($link) . "', modname='$modulename', title='$title'" . (-1 != $date ? ", date='$date'" : '') . ", doc_weight=$doc_weight WHERE id=$doc_id");

    $db->query('DELETE FROM ' . $db->prefix('xd_engine') . " WHERE doc_id='$doc_id'");

    priv_index_content($doc_id, $doc_weight, $text, $weight, $remove_html, $remove_bb, $remove_ex_word);

    $db->query('OPTIMIZE TABLE ' . $db->prefix('xd_engine') . ', ' . $db->prefix('xd_document') . ',' . $db->prefix('xd_keyword'));
}

//---------------------------------------------------------------------------------------//
/**
 * index a given content into the database
 *
 * @param string $link for the document THIS MUST BE UNIQUE and must start from modules (ie for news use news/article.php?storyid=$id
 */
function remove_document($link)
{
    global $db;

    $result = $db->query('SELECT id FROM ' . $db->prefix('xd_document') . " WHERE link='" . $GLOBALS['xoopsDB']->escape($link) . "'");

    [$doc_id] = $db->fetch_array($result);

    if (!isset($doc_id)) {
        die($db->error() . " while removing $link");
    }

    $db->query('DELETE FROM ' . $db->prefix('xd_document') . " WHERE id=$doc_id");

    @unlink(XOOPS_ROOT_PATH . '/modules/xoopsdigger/cache/text/' . $doc_id . '.txt');

    $db->query('DELETE FROM ' . $db->prefix('xd_engine') . " WHERE doc_id=$doc_id");

    $db->query('OPTIMIZE TABLE ' . $db->prefix('xd_engine') . ', ' . $db->prefix('xd_document'));
}
//---------------------------------------------------------------------------------------//
/**
 * set the global module weight to a new value
 *
 * @param float $oldWeight the old weight given to a module (default is 1) will fail if =0 (delete documents)
 * @param float $newWeight the new weight you want to give
 * @param string $modulename the name of the module
 */
function updateModuleWeight($oldWeight, $newWeight, $modulename)
{
    global $db;

    if (0 == $oldWeight || 0 == $newWeight) {
        return;
    }

    $trans = $newWeight / $oldWeight;

    $db->query('UPDATE ' . $db->prefix('xd_document') . " SET doc_weight=doc_weight*$trans WHERE modname='$modulename'");

    $result = $db->query('SELECT id FROM ' . $db->prefix('xd_document') . " WHERE modname='$modulename'");

    $sqlarray = [];

    while (list($doc_id) = $db->fetch_row($result)) {
        $sqlarray[] = "doc_id=$doc_id";
    }

    $GLOBALS['xoopsDB']->freeRecordSet($result);

    $db->query('UPDATE ' . $db->prefix('xd_engine') . " SET weight=weight*$trans WHERE " . implode(' OR ', $sqlarray));
}
//---------------------------------------------------------------------------------------//
/**
 * Set a new weight for a document
 *
 * @param float $newWeight the new weight you want to give
 * @param string $doc_link the link to this document
 */
function updateDocumentWeight($newWeight, $doc_link)
{
    global $db;

    if (0 == $newWeight) {
        return;
    }

    [$doc_id, $oldWeight] = $db->fetch_row($db->query('SELECT DISTINCT id,doc_weight FROM ' . $db->prefix('xd_document') . " WHERE link='$doc_link'"));

    if (0 == $oldWeight) {
        return;
    }

    $trans = $newWeight / $oldWeight;

    $db->query('UPDATE ' . $db->prefix('xd_document') . " SET doc_weight=doc_weight*$trans WHERE link='$doc_link'");

    $db->query('UPDATE ' . $db->prefix('xd_engine') . " SET weight=weight*$trans WHERE doc_id=$doc_id");
}

//---------------------------------------------------------------------------------------//
/**
 * Get most weighted words (not necessarly the one that appears the most) for a specific document
 *
 * @param string $link link to the document as id
 * @param int $min display start at min
 * @param int $max display end at max
 * @return array word as key and weight as value (already sorted)
 */
function getMostWeightedWordsFor($link, $min, $max)
{
    [$doc_id] = $db->fetch_row($db->query('SELECT id FROM ' . $db->prefix('xd_document') . " WHERE link='" . $GLOBALS['xoopsDB']->escape($link) . "'"));

    if (isset($doc_id)) {
        $tword = $db->prefix('xd_keyword');

        $tengine = $db->prefix('xd_engine');

        $results = $db->query("SELECT $tword.word, $tengine.weight FROM $tword, $tengine WHERE $engine.doc_id=$doc_id AND $tword.id=$tengine.word_id ORDER BY weight DESC LIMIT $min," . ($max - $min) . '');

        $ret = [];

        while ($wordinfo = $db->fetch_array($results)) {
            $ret[$wordinfo['word']] = $wordinfo['weight'];
        }

        return $ret;
    }
}
//---------------------------------------------------------------------------------------//
//DON'T LOOK BELOW THIS LINE unless you want to collaborate
//DON'T USE priv_index_content unless you're sure what you're doing
//---------------------------------------------------------------------------------------//

require_once XOOPS_ROOT_PATH . '/modules/xoopsdigger/include/extractwords.php';
//I TOLD YOU : DON'T LOOK BELOW THIS LINE !!
function priv_index_content($doc_id, $doc_weight, $text, $weight, $remove_html, $remove_bb, $remove_ex_word)
{
    global $db,$diggerConf;

    if (0 != $diggerConf['use_cache']) {
        $file = fopen(XOOPS_ROOT_PATH . '/modules/xoopsdigger/cache/text/' . $doc_id . '.txt', 'wb');

        if ($file) {
            if ($diggerConf['cache_size'] > 0) {
                fwrite($file, mb_substr(preg_replace('§<[^<]*>§', ' ', implode(" \n", $text)), 0, $diggerConf['cache_size']) . "\n");
            } else {
                fwrite($file, preg_replace('§<[^<]*>§', ' ', implode(" \n", $text)) . "\n");
            }

            fclose($file);
        }
    }

    //getting final weight

    $wordstoindex = [];

    foreach ($text as $key => $value) {
        //echo $value;

        $words = getWords($value);

        //print_r($words);

        foreach ($words as $w => $wcount) {
            $wordstoindex[$w] += $wcount * $weight[$key] * $doc_weight;
        }
    }

    unset($text);

    unset($words);

    unset($weight);

    //I TOLD YOU... you must be a developper or What so check this=>

    //This part need a good optimization, too many queries

    $sql_keyword = 'INSERT INTO ' . $db->prefix('xd_keyword') . '(word,twoletter) VALUES ';

    $sql_select_word = 'SELECT id FROM ' . $db->prefix('xd_keyword') . " WHERE word='";

    $sql_engine = 'INSERT INTO ' . $db->prefix('xd_engine') . '(doc_id,word_id,weight) VALUES ';

    $sqlarray_engine = [];

    $word_id = 0;

    foreach ($wordstoindex as $w => $wcount) {
        //Here we're using mysql_query to speed up a little

        $res = $GLOBALS['xoopsDB']->queryF($sql_select_word . $w . "'");

        if ($GLOBALS['xoopsDB']->getRowsNum($res) > 0) {
            [$word_id] = $GLOBALS['xoopsDB']->fetchRow($res);
        } else {
            $GLOBALS['xoopsDB']->queryF($sql_keyword . "('$w','" . mb_substr($w, 0, 2) . "')");

            $word_id = $GLOBALS['xoopsDB']->getInsertId();
        }

        $sqlarray_engine[] = "($doc_id,$word_id,$wcount)";
    }

    unset($wordstoindex);

    $sql_engine .= implode(', ', $sqlarray_engine);

    unset($sqlarray_engine);

    $db->query($sql_engine);

    //echo '<br>'.$sql.'<br >';
    //echo $db->error();
}
