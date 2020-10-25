<?php
// $Id: xoops_version.php,v 1.1 2006/02/20 13:50:01 mikhail Exp $
// ------------------------------------------------------------------------- //
//               E-Xoops: Content Management for the Masses                  //
//                       < http://www.e-xoops.com >                          //
// ------------------------------------------------------------------------- //

// ------------------------------------------------------------------------- //
// Info
$modversion['name'] = _MI_XOOPSDIGGER_NAME;
$modversion['version'] = 0.21;
$modversion['description'] = _MI_XOOPSDIGGER_DESC;
$modversion['credits'] = 'PHPDig for some idea';
$modversion['author'] = 'Matthias Studer see http://xoopsdigger.sourceforge.net/';
$modversion['license'] = 'GPL: See /manual/LICENSES/GPL.txt';
$modversion['official'] = 0;

// ------------------------------------------------------------------------- //
// Base Info
$modversion['image'] = 'digger_slogo.jpg';
$modversion['dirname'] = 'xoopsdigger';

// ------------------------------------------------------------------------- //
// SQL
$modversion['sqlfile']['mysql'] = 'sql/mysql.sql';
$modversion['tables'][0] = 'xd_document';
$modversion['tables'][1] = 'xd_keyword';
$modversion['tables'][2] = 'xd_word_log';
$modversion['tables'][1] = 'xd_engine';

// ------------------------------------------------------------------------- //
// Blocks
$modversion['blocks'][1]['file'] = 'xoopsdigger.php';
$modversion['blocks'][1]['name'] = _MI_XOOPSDIGGER_NAME;
$modversion['blocks'][1]['description'] = _MI_XOOPSDIGGER_DESC;
$modversion['blocks'][1]['show_func'] = 'b_dig_show';

// ------------------------------------------------------------------------- //
// Admin
$modversion['hasAdmin'] = 1;
$modversion['adminindex'] = 'admin/index.php';
$modversion['adminmenu'] = 'admin/menu.php';

// ------------------------------------------------------------------------- //
// Main contents
$modversion['hasMain'] = 1;

// ------------------------------------------------------------------------- //
// Search
//I don't know if it's must be implemented
//$modversion['hasSearch']      = 1;
//$modversion['search']['file'] = 'include/search.inc.php';
//$modversion['search']['func'] = 'xoopsdigger_search';
