<?
include_once '../sys/inc/start.php';
include_once '../sys/inc/compress.php';
include_once '../sys/inc/sess.php';
include_once '../sys/inc/home.php';
include_once '../sys/inc/settings.php';
$temp_set=$set;
include_once '../sys/inc/db_connect.php';
include_once '../sys/inc/ipua.php';
include_once '../sys/inc/fnc.php';
include_once '../sys/inc/adm_check.php';
include_once '../sys/inc/user.php';
user_access('adm_themes',null,'index.php?'.SID);
adm_check();
$set['title']='Темы оформления';
include_once '../sys/inc/thead.php';
title();

$opendirthem=opendir(H.'style/themes');
while ($themes2=readdir($opendirthem))
{
// запись всех тем в массив
if ($themes2=='.' || $themes2=='..')continue;
$themes3[]=$themes2;
}
closedir($opendirthem);

if (isset($_GET['delete']) && in_array("$_GET[delete]", $themes3) && isset($_GET['ok']))
{
$del_them=$_GET['delete'];
if ($del_them==$temp_set['set_them2'] || $del_them==$temp_set['set_them'])
$err='Тема используется по умолчанию';
else
{
if (@delete_dir(H.'style/themes/'.$del_them))
{
$themes3=NUll;
$opendirthem=opendir(H.'style/themes');
while ($themes2=readdir($opendirthem))
{
// запись всех тем в массив
if ($themes2=='.' || $themes2=='..')continue;
$themes3[]=$themes2;
}
closedir($opendirthem);

msg("Тема успешно удалена");
}

else
$err="Невозможно удалить тему";
}

}




err();
aut();







$k_post=sizeof($themes3);
$k_page=k_page($k_post,$set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
echo "<table class='post'>\n";

for($i=$start;$i<$k_post && $i<$set['p_str']*$page;$i++)
{
// постраничный вывод тем
$themes=$themes3[$i];
echo "   <tr>\n";
if ($set['set_show_icon']==2){
echo "  <td class='icon48' rowspan='2'>\n";
if (is_file(H.'style/themes/'.$themes.'/screen_48.png'))
echo "<img src='".H."style/themes/".$themes."/screen_48.png' alt='' /><br />\n";
else
echo "Нет";
echo "  </td>\n";
}

echo "  <td class='p_t'>\n";

echo ($name=@file_get_contents(H.'style/themes/'.$themes.'/them.name'))?$name:$themes;

echo "  </td>\n";
echo "   </tr>\n";
echo "   <tr>\n";
echo "  <td class='p_m'>\n";



echo "Папка с темой: <span title='/style/themes/$themes/'>$themes</span><br />\n";

// размер файла таблиц стилей
echo (is_file(H.'style/themes/'.$themes.'/style.css'))?"<a href='/style/themes/$themes/style.css'>style.css</a>: ".size_file(filesize(H.'style/themes/'.$themes.'/style.css'))."<br />\n":"Нет style.css<br />\n";


if ($themes==$temp_set['set_them'])
{
echo "По умолчанию для WAP<br />\n";
}

if ($themes==$temp_set['set_them2'])
{
echo "По умолчанию для WEB<br />\n";
}

echo 'Стоит у '.mysql_result(mysql_query("SELECT COUNT(*) FROM `user` WHERE `set_them` = '$themes' OR `set_them2` = '$themes'"),0)." чел.<br />\n";

echo "<a href='?delete=$themes&amp;page=$page'>Удалить</a><br />\n";

echo "  </td>\n";
echo "   </tr>\n";

}
echo "</table>\n";

if (isset($_GET['delete']) && in_array("$_GET[delete]", $themes3))
{
$del_them=$_GET['delete'];
echo "<div class='err'>\n";
if ($del_them==$temp_set['set_them2'] || $del_them==$temp_set['set_them'])
echo "Тема ".(($name=@file_get_contents(H.'style/themes/'.$del_them.'/them.name'))?$name:$del_them)." установлена по умолчанию<br />\n<a href='?page=$page'>Отмена</a><br />\n";
else
{
echo "Подтвердите удаление (".(($name=@file_get_contents(H.'style/themes/'.$del_them.'/them.name'))?$name:$del_them)."):<br />\n";
echo "<a href='?delete=$del_them&amp;page=$page&amp;ok'>Удалить</a> | <a href='?page=$page'>Отмена</a><br />\n";
}
echo "</div>\n";
}
if ($k_page>1)str('?',$k_page,$page); // Вывод страниц

echo "<div class='foot'>\n";

echo "&raquo;<a href='them_installer.php'>Установка тем</a><br />\n";
if (user_access('adm_panel_show'))
echo "&laquo;<a href='/adm_panel/'>В админку</a><br />\n";
echo "</div>\n";


include_once '../sys/inc/tfoot.php';
?>