<?php
// ------------------------------------------------------------------------- //
//               E-Xoops Digger: Advanced Search Engine                      //
//                < http://http://xoopsdigger.sf.net >                       //
// ------------------------------------------------------------------------- //
// Original Author: Matthias Studer
// Author Website : http://www.ired.org, http://xoopsdigger.sf.net
// License Type   : GPL: See /manual/LICENSES/GPL.txt
// ------------------------------------------------------------------------- //
require_once XOOPS_ROOT_PATH . '/modules/xoopsdigger/cache/config.php';
require_once XOOPS_ROOT_PATH . '/modules/xoopsdigger/cache/wordsep.php';
//reading word exclusion file only if not set
if (!isset($xd_excluded_word) && isset($diggerConf['exclude_file'])) {
    //$file=fopen(,'r');

    $xd_excluded_word = [];

    $lines = file(XOOPS_ROOT_PATH . '/modules/xoopsdigger/cache/' . $diggerConf['exclude_file']);

    foreach ($lines as $line) {
        $xd_excluded_word[mb_strtolower(trim($line))] = 1;
    }

    unset($lines);

    //fclose($file);
}
//---------------------------------------------------------------------------------------//
/**
 * Extracts words and return an array containing words (in lower case) as key and number of
 * apperance as value
 *
 * @param string $text text where we want to extract words
 * @param bool $remove_html wheter we should remove all html tags (including content)
 * @param bool $remove_bb wheter we should remove bbcode
 * @param bool $remove_ex_word wheter we should remove excluded words (not implemented yet)
 * @return array
 */
function getWords($text, $remove_html = true, $remove_bb = true, $remove_ex_word = true)
{
    global $xd_wordsep,$xd_excluded_word,$diggerConf;

    //removing html tags

    if ($remove_html) {
        $text = preg_replace('§<[^<]*>§', ' ', $text);
    }

    if ($remove_bb) {
        $text = preg_replace("§\[[^\[]*\]§", ' ', $text);
    }

    //Maybe move this array somewhere else

    $accent = ['À' => 'a', 'Á' => 'a', 'Â' => 'a' , 'Ã' => 'a' , 'Ç' => 'c' , 'È' => 'e' , 'É' => 'e' , 'Í' => 'i' , 'Ò' => 'o' , 'Ó' => 'o' , 'Ô' => 'o' , 'Õ' => 'o' , 'Ú' => 'u', 'ç' => 'c', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'í' => 'i', 'î' => 'i', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ú' => 'u', 'û' => 'u'];

    $text = strtr($text, array_flip(get_html_translation_table(HTML_ENTITIES)));

    //removing special chars

    $text = mb_strtolower(strtr($text, $accent));

    $text = trim(strtr($text, $xd_wordsep));

    //$text = trim($text);

    $words = explode(' ', $text);

    $result = [];

    //removing number and short words and exluded words

    $diggerConf['min_word'] = (int)$diggerConf['min_word'];

    //echo $diggerConf['min_word'];

    foreach ($words as $w) {
        if (mb_strlen($w) > $diggerConf['min_word'] && !is_numeric($w) && !isset($xd_excluded_word[$w])) {
            $result[$w] += 1;
        }
    }

    unset($words);

    return $result;
}
