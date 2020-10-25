<?php
// ------------------------------------------------------------------------- //
//               E-Xoops Digger: Advanced Search Engine                      //
//                < http://http://xoopsdigger.sf.net >                       //
// ------------------------------------------------------------------------- //
// Original Author: Matthias Studer
// Author Website : http://www.ired.org, http://xoopsdigger.sf.net
// License Type   : GPL: See /manual/LICENSES/GPL.txt
// ------------------------------------------------------------------------- //
function b_dig_show()
{
    $block = [];

    $block['title'] = 'Digger Search';

    $block['content'] = '<form method="post" action="' . XOOPS_URL . '/modules/xoopsdigger/index.php">' .
    '<table border="0" align="center">' .
    '<tr><td align="center"><input type="text" name="query" size="10"></td></tr>'
    . '<tr><td align="center"><input type="submit" value="Search"></td></tr>' .
    '</table></form>';

    return $block;
}
