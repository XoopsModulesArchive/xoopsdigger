<?php
$digModule = new DiggerModule();
class DiggerModule
{
    public function getDocumentInfo($link)
    {
        global $db;

        if (!preg_match("§mylinks\/singlelink.php\?lid=([0-9]+)§", $link, $malid)) {
            return;
        }

        $lid = $malid[1];

        $table = $db->prefix('mylinks_links');

        $tdesc = $db->prefix('mylinks_text');

        $res = $db->fetch_array($db->query("SELECT title,description,url,date FROM $table,$tdesc WHERE $table.lid=$lid AND $table.lid=$tdesc.lid"));

        $text = [$res['title'], $res['description']];

        echo $db->error();

        print_r($text);

        $weight = [1.5, 1.2];

        //if you want to index link content uncomment the following

        //You will need to have allow_url_fopen set to true (check your phpinfo)

        //in order to index it

        //$text[]=file_get_contents($contenturl);

        //$weight[]=1.0;

        $docinfo = [];

        $docinfo['title'] = $res['title'];

        $docinfo['doc_weight'] = 1.0;

        $docinfo['text'] = $text;

        $docinfo['weight'] = $weight;

        $docinfo['date'] = $res['date'];

        $docinfo['remove_html'] = true;

        $docinfo['remove_bb'] = false;

        $docinfo['remove_ex_word'] = true;

        //print_r($docinfo);

        return $docinfo;
    }

    public function getSqlQuery()
    {
        global $db;

        return 'SELECT lid,date FROM ' . $db->prefix('mylinks_links');
    }

    public function getLinkFromQuery($array)
    {
        return 'mylinks/singlelink.php?lid=' . $array['lid'];
    }

    public function getDateFromQuery($array)
    {
        return $array['date'];
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
        return 'mylinks/singlelink.php?lid=' . $param;
    }

    public function weightFormText($lang)
    {
        switch ($lang) {
            case 'french':
            return new XoopsFormText("Entrez l'id d'un lien ", '_xd_md_id', 2, 2, '');
                break;
            default:
                return new XoopsFormText('Select link id', '_xd_md_id', 2, 2, '');
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
