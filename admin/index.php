<?php
// ------------------------------------------------------------------------- //
//               E-Xoops Digger: Advanced Search Engine                      //
//                < http://http://xoopsdigger.sf.net >                       //
// ------------------------------------------------------------------------- //
// Original Author: Matthias Studer
// Author Website : http://www.ired.org, http://xoopsdigger.sf.net
// License Type   : GPL: See /manual/LICENSES/GPL.txt
// ------------------------------------------------------------------------- //

require_once dirname(__DIR__, 3) . '/mainfile.php';
require_once XOOPS_ROOT_PATH . '/modules/xoopsdigger/admin/admin_header.php';
require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

if (file_exists('../language/' . $xoopsConfig['language'] . '/admin.php')) {
    include '../language/' . $xoopsConfig['language'] . '/admin.php';
} else {
    include '../language/english/admin.php';
}
xoops_cp_header();
if (1 == $_POST['post_xd_conf']) {
    $diggerConf['debug'] = (0 != (int)$_POST['_xd_debug'] ? 1 : 0);

    $diggerConf['min_word'] = (int)$_POST['_xd_min_word'];

    $diggerConf['use_cache'] = (0 != (int)$_POST['_xd_use_cache'] ? 1 : 0);

    $diggerConf['cache_size'] = (int)$_POST['_xd_cache_size'];

    $diggerConf['snippet'] = (int)$_POST['_xd_snippet'];

    $diggerConf['exclude_file'] = $_POST['_xd_exclude_file'];

    $diggerConf['theme'] = $_POST['_xd_theme'];

    //print_r($diggerConf);

    echo 'writting config';

    writeConfig();
}
if (1 == $_POST['post_xd_mod_weight']) {
    updateModuleWeight($diggerMod[$_POST['_xd_mod']]['weight'], $_POST['_xd_mod_weight'], $_POST['_xd_mod']);

    $diggerMod[$_POST['_xd_mod']]['weight'] = $_POST['_xd_mod_weight'];

    writeConfig();
}
OpenTable();
$moddir = opendir(XOOPS_ROOT_PATH . '/modules/xoopsdigger/admin/modules/');
echo '<h2>' . _XD_MODULE_ADMIN . '</h2><table border="1" align="center"><tr><td>Module</td><td>' . _XD_WEIGHT . '</td></tr>';
while (false !== ($file = readdir($moddir))) {
    if ('.' != $file && '..' != $file && is_dir(XOOPS_ROOT_PATH . '/modules/xoopsdigger/admin/modules/' . $file)) {
        if (!isset($diggerMod[$file])) {
            $diggerMod[$file] = [];

            $diggerMod[$file]['weight'] = 1.0;
        }

        echo '<tr><td>';

        if (@file_exists(XOOPS_ROOT_PATH . "/modules/xoopsdigger/admin/modules/$file/digger_version.php")) {
            require_once XOOPS_ROOT_PATH . "/modules/xoopsdigger/admin/modules/$file/digger_version.php";
        } else {
            echo "Warning $file has no digger_version file, assuming default value !!!";
        }

        if ('diggermodule' == $diggerMod[$file]['admin']) {
            echo '<a href="' . XOOPS_URL . '/modules/xoopsdigger/admin/admin_mod.php?mod=' . urlencode($file);
        } else {
            echo '<a href="' . XOOPS_URL . "/modules/xoopsdigger/admin/modules/$file/index.php";
        }

        echo '">' . ucwords($file) . '</a></td><td>' . $diggerMod[$file]['weight'] . "</td></tr>\n";
    }
}
echo '</table><br>';
CloseTable();
echo '<br><br>';
OpenTable();
$form = new XoopsThemeForm(_XD_MOD_WEIGHT, 'post_new', 'index.php');
$form->setExtra("enctype='multipart/form-data'");
$fmod = new XoopsFormSelect(_XD_CHOOSE_MOD, '_xd_mod');
foreach ($diggerMod as $key => $value) {
    $fmod->addOption($key);
}
$form->addElement($fmod);
$form->addElement(new XoopsFormText(_XD_MOD_WEIGHT, '_xd_mod_weight', 10, 10, ''));
$form->addElement(new XoopsFormHidden('post_xd_mod_weight', '1'));
$form->addElement(new XoopsFormButton('', 'submit', _XD_UPDATE_CONF, 'submit'));
$form->display();
CloseTable();
echo '<br><br>';
OpenTable();
$form = new XoopsThemeForm(_XD_ADMIN_CONFIG, 'post_new', 'index.php');
$form->setExtra("enctype='multipart/form-data'");
$fdebug = new XoopsFormCheckBox(_XD_DEBUG, '_xd_debug', $diggerConf['debug']);
$fdebug->addOption(1, '&nbsp;');
$form->addElement($fdebug);
$fcache = new XoopsFormCheckBox(_XD_USE_CACHE, '_xd_use_cache', $diggerConf['use_cache']);
$fcache->addOption(1, '&nbsp;');
$form->addElement($fcache);
$form->addElement(new XoopsFormText(_XD_CACHE_SIZE, '_xd_cache_size', 10, 10, $diggerConf['cache_size']));
$form->addElement(new XoopsFormText(_XD_MIN_WORD, '_xd_min_word', 2, 2, $diggerConf['min_word']));
$form->addElement(new XoopsFormText(_XD_EXCLUDE_FILE, '_xd_exclude_file', 60, 120, $diggerConf['exclude_file']));
$form->addElement(new XoopsFormText(_XD_SNIPPET, '_xd_snippet', 2, 2, $diggerConf['snippet']));
$ftheme = new XoopsFormSelect(_XD_CHOOSE_THEME, '_xd_theme');
$themedir = opendir(XOOPS_ROOT_PATH . '/modules/xoopsdigger/themes/');
while (false !== ($file = readdir($themedir))) {
    if ('.' != $file && '..' != $file && !is_dir(XOOPS_ROOT_PATH . '/modules/xoopsdigger/themes/' . $file)) {
        $pos = mb_strpos($file, '.');

        if (false !== $pos) {
            $ftheme->addOption(mb_substr($file, 0, $pos));
        }
    }
}
$ftheme->setValue($diggerConf['theme']);
$form->addElement($ftheme);
$form->addElement(new XoopsFormHidden('post_xd_conf', '1'));
$form->addElement(new XoopsFormButton('', 'submit', _XD_UPDATE_CONF, 'submit'));
//$form->setRequired(array('_xd_debug','_xd_use_cache','_xd_cache_size','_xd_exclude_file','_xd_snippet'));
$form->display();
CloseTable();
xoops_cp_footer();
