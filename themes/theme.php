<?php
function xd_theme_result($snippet, $title, $modname, $link, $rank, $query)
{
    echo '<table border="0" width="100%"><tr><td align="left"><a href="' . XOOPS_URL . "/modules/$link" . '">' . ($title ?: $link) . "</a></td> <td align=\"right\">$rank</td></tr>";

    echo '<tr><td colspan="2">' . $snippet . '</td></tr>';

    echo '<tr width="100%"><td align="right"  colspan="2"><a href="' . XOOPS_URL . '/modules/xoopsdigger/index.php?query=' . urlencode($query) . '&mod=' . urlencode($modname) . '">' . _XD_LIMIT . ' ' . $modname . '</a></td></tr></table><br><hr>';
}
function xd_theme_begin($navbar, $resultcount, $time, $modules, $query)
{
    OpenTable();

    echo '<style type="text/css"><!--
		.xd_highlight{
			background-color : #FF7F50;
		}-->
	</style>';

    echo '<table border="0" width="100%"><tr><td align="left">';

    if ($navbar['current'] > 0) {
        echo '<a href="' . $navbar['page'][$navbar['current'] - 1] . '">' . _XD_PREVIOUS . '</a>';
    }

    echo '</td><td align="right">';

    if ($navbar['current'] < $navbar['numpage'] - 1) {
        echo '<a href="' . $navbar['page'][$navbar['current'] + 1] . '">' . _XD_NEXT . '</a>';
    }

    echo '</td></tr></table>';

    echo '<p align="center">' . _XD_SEARCH_RES . $query . '</p>';

    echo '<p align="center">' . sprintf(_XD_FOUND, $resultcount, $time) . '</p><hr><br>';
}
function xd_theme_end($navbar, $resultcount, $time, $modules, $query)
{
    //echo '<table border="0" width="100%"><tr><td align="left">';

    echo '<p align="center">';

    if ($navbar['current'] > 0) {
        echo '<a href="' . $navbar['page'][$navbar['current'] - 1] . '">' . _XD_PREVIOUS . '</a> |';
    }

    //echo '</td><td align="center" width="100%">';

    if ($navbar['numpage'] > 1) {
        foreach ($navbar['page'] as $num => $page) {
            if ($num == $navbar['current']) {
                echo " $num ";
            } else {
                echo " <a href=\"$page\">$num</a> ";
            }
        }
    }

    //echo '</td><td align="right">';

    if ($navbar['current'] < $navbar['numpage'] - 1) {
        echo '| <a href="' . $navbar['page'][$navbar['current'] + 1] . '">' . _XD_NEXT . '</a>';
    }

    //echo '</td></tr></table>';

    echo '</p>';

    CloseTable();
}
function xd_theme_search_form($query, $min, $max, $mod)
{
    require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';

    $form = new XoopsThemeForm(_XD_SEARCH, 'post_new', 'index.php');

    $form->setExtra("enctype='multipart/form-data'");

    $form->addElement(new XoopsFormText(_XD_QUERY, 'query', 30, 120, $query));

    $ftype = new XoopsFormSelect(_XD_CHOOSE_TYPE, 'type');

    $ftype->addOption('normal', _XD_T_NORMAL);

    $ftype->addOption('fuzzy', _XD_T_FUZZY);

    $ftype->addOption('exact', _XD_T_EXACT);

    $form->addElement($ftype);

    $fmod = new XoopsFormSelect('Number of results per page', 'numres');

    $fmod->addOption('1');

    $fmod->addOption('10');

    $fmod->addOption('25');

    $fmod->addOption('50');

    $form->addElement($fmod);

    $form->addElement(new XoopsFormButton('', 'submit', _XD_SEARCH, 'submit'));

    //$form->setRequired(array('type','query'));

    $form->display();

    echo '<br><br>';
}
