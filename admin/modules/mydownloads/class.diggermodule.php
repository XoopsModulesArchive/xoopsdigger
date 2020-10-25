<?php
$digModule = new DiggerModule();
class DiggerModule
{
    public function getDocumentInfo($link)
    {
        global $db;

        if (!preg_match("§mydownloads\/singlefile.php\?lid=([0-9]+)§", $link, $malid)) {
            return;
        }

        $lid = $malid[1];

        echo $lid . '<br><br>';

        $table = $db->prefix('mydownloads_downloads');

        $tdesc = $db->prefix('mydownloads_text');

        $res = $db->fetch_array($db->query("SELECT title,description,url,date FROM $table,$tdesc WHERE $table.lid=$lid AND $table.lid=$tdesc.lid"));

        $text = [$res['title'], $res['description']];

        $weight = [1.5, 1.2];

        //Removed for xoops since there's no local file

        /*if(preg_match("§(.*)\.pdf§i",$url,$matches)){
            echo 'found pdf<br>';
            $contenturl=XOOPS_ROOT_PATH.'/modules/mydownloads/cache/files/'.$matches[1].'.html';
            if(@file_exists($contenturl)){
                $file=fopen($contenturl,'r');
                $text[]=fread($file,filesize($contenturl));
                $weight[]=1.0;
            }else echo 'Coundn\'t open '.$contenturl.'<br>';
        }else echo 'pdf not found<br>';*/

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

        return 'SELECT lid,date FROM ' . $db->prefix('mydownloads_downloads');
    }

    public function getLinkFromQuery($array)
    {
        return 'mydownloads/singlefile.php?lid=' . $array['lid'];
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
        return 'mydownloads/singlefile.php?lid=' . $param;
    }

    public function weightFormText($lang)
    {
        switch ($lang) {
            case 'french':
            return new XoopsFormText("Entrez l'id d'un download ", '_xd_md_id', 2, 2, '');
                break;
            default:
                return new XoopsFormText('Select download id', '_xd_md_id', 2, 2, '');
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
