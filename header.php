<?php
require_once '../../mainfile.php';
require_once XOOPS_ROOT_PATH . '/header.php';
require_once XOOPS_ROOT_PATH.'/modules/xoopsdigger/cache/config.php';
require_once XOOPS_ROOT_PATH.'/modules/xoopsdigger/include/extractwords.php';
require_once XOOPS_ROOT_PATH.'/modules/xoopsdigger/class/class.searchtimer.php';
require_once XOOPS_ROOT_PATH.'/modules/xoopsdigger/class/class.exoopsdb.php';
require_once XOOPS_ROOT_PATH.'/modules/xoopsdigger/themes/'.$diggerConf['theme'].'.php';
$db = new ExoopsDB();

