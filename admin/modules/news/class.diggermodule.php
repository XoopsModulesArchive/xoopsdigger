<?php
$digModule = new DiggerModule();
class DiggerModule
{
    public function getDocumentInfo($link)
    {
        global $db;

        require_once XOOPS_ROOT_PATH . '/modules/news/class/class.newsstory.php';

        if (!preg_match("§news\/article.php\?storyid=([0-9]+)§", $link, $malid)) {
            return;
        }

        $storyid = $malid[1];

        $story = new NewsStory($storyid);

        $text = [$story->title(), $story->hometext(), $story->bodytext()];

        $weight = [1.5, 1.2, 1.0];

        $docinfo = [];

        $docinfo['title'] = $story->title();

        $docinfo['doc_weight'] = 1.0;

        $docinfo['text'] = $text;

        $docinfo['weight'] = $weight;

        $docinfo['date'] = $story->published();

        $docinfo['remove_html'] = true;

        $docinfo['remove_bb'] = false;

        $docinfo['remove_ex_word'] = true;

        //print_r($docinfo);

        return $docinfo;
    }

    public function getSqlQuery()
    {
        global $db;

        return 'SELECT storyid,published FROM ' . $db->prefix('stories') . ' WHERE published>0';
    }

    public function getLinkFromQuery($array)
    {
        return 'news/article.php?storyid=' . $array['storyid'];
    }

    public function getDateFromQuery($array)
    {
        return $array['published'];
    }

    public function hasDate()
    {
        return true;
    }

    public function compareDate($indexedDate, $givenDate)
    {
        return (int)$givenDate - (int)$indexedDate;
    }

    public function updateWeightLink($param)
    {
        return 'news/article.php?storyid=' . $param;
    }

    public function weightFormText($lang)
    {
        switch ($lang) {
            case 'french':
            return new XoopsFormText("Entrez l'id d'une nouvelle ", '_xd_md_id', 2, 2, '');
                break;
            default:
                return new XoopsFormText('Select story id', '_xd_md_id', 2, 2, '');
        }
    }

    public function hasSpecialAction()
    {
        return false;
    }

    public function getSpecialAction()
    {
        return [];
    }

    public function processSpecialAction($action)
    {
    }
}
