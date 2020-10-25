<?php
// ------------------------------------------------------------------------- //
//               E-Xoops Digger: Advanced Search Engine                      //
//                < http://http://xoopsdigger.sf.net >                       //
// ------------------------------------------------------------------------- //
// Original Author: Matthias Studer
// Author Website : http://www.ired.org, http://xoopsdigger.sf.net
// License Type   : GPL: See /manual/LICENSES/GPL.txt
// ------------------------------------------------------------------------- //

//Always leave this line as is
$digModule = new DiggerModule();
//---------------------------------------------------------------------------------------//
/**
 * Helping class for module administration
 *
 * Just copy and paste and implements all field
 */
class DiggerModule
{
    //---------------------------------------------------------------------------------------//
    /**
     * Return indexing information in an array
     *
     * array must have the followings keys
     * ['title'] title of the document
     * ['doc_weight'] weight of the document set it to 1.0
     * ['text'] the text that need to be indexed. It's an array wich have a corresponding key in the * weight array array['text'][0]<=>array['weight'][0]
     * ['weight'] the weight of each part of text
     * ['date'] Optionnal, if you use it to know when to update
     * ['remove_html'] do we need to remove html tags before indexing ? (set it to true if you have
     * html tags)
     * ['remove_bb' do we need to remove bbcode tags before indexing ? (set it to true if you have
     * bbcode tags) This is not well tested
     * ['remove_ex_word'] do we need to remove exluded words, set it to true unless you have a good
     * reason not to do it
     * @param string $link link is the link you give for indexing, It's a unique key
     * @return void
     */

    public function getDocumentInfo($link)
    {
    }

    //---------------------------------------------------------------------------------------//

    /**
     * A sql query from wich you must be able to retrive an UNIQUE link and optionnaly a date
     * @return string sql query
     */

    public function getSqlQuery()
    {
    }

    //---------------------------------------------------------------------------------------//

    /**
     * Retrive the link from a result of the query you give
     * @param array $array mysql_fetch_array result
     * @return string link
     */

    public function getLinkFromQuery($array)
    {
    }

    //---------------------------------------------------------------------------------------//

    /**
     * Retrieve the date from a result of the query you give
     * @param array $array mysql_fetch_array result
     * @return void (10) date
     */

    public function getDateFromQuery($array)
    {
    }

    //---------------------------------------------------------------------------------------//

    /**
     * Do we use date for updating ?
     * @return bool
     */

    public function hasDate()
    {
        return true;
    }

    //---------------------------------------------------------------------------------------//

    /**
     * Compare two date
     * @param mixed $indexedDate
     * @param mixed $givenDate
     * @return void >0 if need reindexing
     */

    public function compareDate($indexedDate, $givenDate)
    {
    }

    //---------------------------------------------------------------------------------------//

    /**
     * Return a XoopsFormElement
     * The element name must be '_xd_md_id' but you can put what you want in it.
     * Later you must be able to form an UNIQUE link from it
     * @param string $lang current language
     * @return void
     */

    public function weightFormText($lang)
    {
    }

    //---------------------------------------------------------------------------------------//

    /**
     * Return the link from $_POST['_xd_md_id']
     *
     * @param mixed $param
     * @return string UNIQUE link
     */

    public function updateWeightLink($param)
    {
    }

    //---------------------------------------------------------------------------------------//

    /**
     * Do we have special action ?
     *
     * @return bool
     */

    public function hasSpecialAction()
    {
        return false;
    }

    //---------------------------------------------------------------------------------------//

    /**
     * Return an array containing all specials actions
     * The key is the action keyword and value is it's name (visible part)
     * return an empty array if no special action
     *
     * @return array
     */

    public function getSpecialAction()
    {
        return [];
    }

    //---------------------------------------------------------------------------------------//

    /**
     * Process specials actions
     *
     * @param string $action a keyword (it can be an unknown keyword we dont check for this)
     */

    public function processSpecialAction($action)
    {
    }
}
