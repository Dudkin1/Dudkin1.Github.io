<?
include_once '../../sys/inc/start.php';
include_once '../../sys/inc/compress.php';
include_once '../../sys/inc/sess.php';
include_once '../../sys/inc/home.php';
include_once '../../sys/inc/settings.php';
include_once '../../sys/inc/db_connect.php';
include_once '../../sys/inc/ipua.php';
include_once '../../sys/inc/fnc.php';
include_once '../../sys/inc/user.php';
if (isset($_GET['id']))$sid = intval($_GET['id']);
else $sid = $user['id'];
$ank = get_user($sid);

$set['title']="Друзья ".$ank['nick'].""; // заголовок страницы
include_once '../../sys/inc/thead.php';
title();
aut();

/*
==================================
Приватность станички пользователя
Запрещаем просмотр друзей
==================================
*/
	$uSet = mysql_fetch_array(mysql_query("SELECT * FROM `user_set` WHERE `id_user` = '$ank[id]'  LIMIT 1"));
	$frend=mysql_result(mysql_query("SELECT COUNT(*) FROM `frends` WHERE (`user` = '$user[id]' AND `frend` = '$ank[id]') OR (`user` = '$ank[id]' AND `frend` = '$user[id]') LIMIT 1"),0);
	$frend_new=mysql_result(mysql_query("SELECT COUNT(*) FROM `frends_new` WHERE (`user` = '$user[id]' AND `to` = '$ank[id]') OR (`user` = '$ank[id]' AND `to` = '$user[id]') LIMIT 1"),0);
if ($ank['id'] != $user['id'] && $user['group_access'] == 0)
{
	if (($uSet['privat_str'] == 2 && $frend != 2) || $uSet['privat_str'] == 0) // Начинаем вывод если стр имеет приват настройки
	{
	if ($ank['group_access']>1)echo "<div class='err'>".$ank['group_name']."</div>\n";
	echo "<div class='nav1'>";
		echo group($ank['id'])." ";
                echo user::nick($ank['id'],1,1,1);
		echo "</div>";
		echo "<div class='nav2'>";
                user::avatar($ank['id']);
		echo "</div>";
	}
	if ($uSet['privat_str'] == 2 && $frend != 2) // Если только для друзей
{
		echo '<div class="mess">';
		echo 'Просматривать друзей пользователя могут только его друзья!';
		echo '</div>';
		// В друзья
		if (isset($user))
		{
			echo '<div class="nav1">';
			if ($frend_new == 0 && $frend==0){
                 echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?add=".$ank['id']."'>Добавить в друзья</a><br />\n";
			}elseif ($frend_new == 1){
			echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?otm=$ank[id]'>Отклонить заявку</a><br />\n";
                      }elseif ($frend == 2){
			echo "<img src='/style/icons/druzya.png' alt='*'/> <a href='/user/frends/create.php?del=$ank[id]'>Удалить из друзей</a><br />\n";
			}
			echo "</div>";
		}
	include_once '../sys/inc/tfoot.php';
	exit;
	}
	if ($uSet['privat_str'] == 0) // Если закрыта
	{
		echo '<div class="mess">';
		echo 'Пользователь запретил просматривать его друзей!';
		echo '</div>';
	include_once '../sys/inc/tfoot.php';
	exit;
	}
}

//--------------------отмеченные---------------------//
if (isset($user) && $user['id']==$ank['id'])
{
if (isset($_GET['delete']))
{
foreach ($_POST as $key => $value)
{
if (preg_match('#^post_([0-9]*)$#',$key,$postnum) && $value='1')
{
$delpost[]=$postnum[1];
}}
if (isset($_POST['delete']))
{
if (isset($delpost) && is_array($delpost))
{
echo "<div class='mess'>Друзья: ";
for ($q=0; $q<=count($delpost)-1; $q++) {
if (mysql_result(mysql_query("SELECT COUNT(*) FROM `frends` WHERE (`user` = '$user[id]' AND `frend` = '$delpost[$q]') OR (`user` = '$delpost[$q]' AND `frend` = '$user[id]') LIMIT 1"),0)==0)
$warn[]='Этого пользователя нет в вашем списке контактов';
else
{
	if (mysql_result(mysql_query("SELECT COUNT(*) FROM `frends` WHERE (`user` = '$user[id]' AND `frend` = '$delpost[$q]') OR (`user` = '$delpost[$q]' AND `frend` = '$user[id]')"),0)>0)
	{
/*
		==========================
		Уведомления друзьям
		==========================
		*/
		mysql_query("INSERT INTO `notification` (`avtor`, `id_user`, `id_object`, `type`, `time`) VALUES ('$user[id]', '$delpost[$q]', '$user[id]', 'del_frend', '$time')");
	mysql_query("DELETE FROM `frends` WHERE `user` = '$user[id]' AND `frend` = '$delpost[$q]'");
	mysql_query("DELETE FROM `frends` WHERE `user` = '$delpost[$q]' AND `frend` = '$user[id]'");
	mysql_query("DELETE FROM `frends_new` WHERE `user` = '$delpost[$q]' AND `to` = '$user[id]'");
	mysql_query("DELETE FROM `frends_new` WHERE `user` = '$user[id]' AND `to` = '$delpost[$q]'");
	mysql_query("OPTIMIZE TABLE `frends`");
	mysql_query("OPTIMIZE TABLE `frends_new`");
	$msgno="К сожалению, пользователь [b]$user[nick][/b] удалил вас из списка друзей. ";
	mysql_query("INSERT INTO `mail` (`id_user`, `id_kont`, `msg`, `time`) values('0', '$delpost[$q]', '$msgno', '$time')");
	}
}
$ank_del = get_user($delpost[$q]);
echo "<font color='#395aff'><b>".$ank_del['nick']."</b></font>, ";
}
echo " удален(ы) из списка ваших друзей</div>";
}else{
$err[] = 'Не выделено ни одного контакта';
}}
}
}
//---------------------Panel---------------------------------//
$on_f=mysql_result(mysql_query("SELECT COUNT(*) FROM `frends` INNER JOIN `user` ON `frends`.`frend`=`user`.`id` WHERE `frends`.`user` = '$ank[id]' AND `frends`.`i` = '1' AND `user`.`date_last`>'".(time()-600)."'"), 0);
$f=mysql_result(mysql_query("SELECT COUNT(*) FROM `frends` WHERE `user` = '$ank[id]' AND `i` = '1'"), 0);
$add=mysql_result(mysql_query("SELECT COUNT(id) FROM `frends_new` WHERE `to` = '$ank[id]' LIMIT 1"), 0);
/*echo '<div style="background:white;"><div class="pnl2H">';
echo '<div class="linecd"><span style="margin:9px;">';
echo ''.($ank['id']==$user['id'] ? 'Мои друзья' : ' Друзья '.group($ank['id']).' '.user::nick($ank['id'],1,1,1).'').''; 
echo '</span> </div></div>';*/
/*
if ($set['web']==true) {
echo '<div class="mb4">
<nav class="acsw rnav_w"><ul class="rnav js-rnav  " style="padding-right: 45px;">';
echo '<li class="rnav_i"><a href="index.php?id='.$ank['id'].'" class="ai aslnk"><span class="wlnk"><span class="slnk">Все друзья</span></span> 
<i><font color="#999">'.$f.'</font></i></a></li>';
echo '<li class="rnav_i"><a href="online.php?id='.$ank['id'].'" class="ai alnk"><span class="wlnk"><span class="lnk">Онлайн
<i><font color="#999">'.$on_f.'</font></i></a></span></span></li> ';
if($ank['id']==$user['id']){ 
echo '<li class="rnav_i"><a href="new.php" class="ai alnk"><span class="wlnk"><span class="lnk">Заявки
<i><font color="#999">'.$add.'</font></i></a></span></span> </li>'; 
}
echo '</ul></nav></div></div>'; }
*/
echo "<div id='comments' class='menus'>";
echo "<div class='webmenu'>";
echo "<a href='index.php?id=$ank[id]' class='activ'>Все (".mysql_result(mysql_query("SELECT COUNT(*) FROM `frends` WHERE `user` = '$ank[id]' AND `i` = '1'"), 0).")</a>";
echo "</div>"; 

echo "<div class='webmenu last'>";
echo "<a href='online.php?id=$ank[id]'>Онлайн (".mysql_result(mysql_query("SELECT COUNT(*) FROM `frends` INNER JOIN `user` ON `frends`.`frend`=`user`.`id` WHERE `frends`.`user` = '$ank[id]' AND `frends`.`i` = '1' AND `user`.`date_last`>'".(time()-600)."'"), 0).")</a>";
echo "</div>"; 

if ($ank['id'] == $user['id'])
{
    echo "<div class='webmenu last'>";
    echo "<a href='new.php'>Заявки (".mysql_result(mysql_query("SELECT COUNT(id) FROM `frends_new` WHERE `to` = '$ank[id]' LIMIT 1"), 0).")</a>";
    echo "</div>"; 
}
echo "</div>";

//--------End Panel---------------------//

$k_post=mysql_result(mysql_query("SELECT COUNT(*) FROM `frends` WHERE `user` = '$ank[id]' AND `i` = '1'"), 0);
$k_page=k_page($k_post,$set['p_str']);
$page=page($k_page);
$start=$set['p_str']*$page-$set['p_str'];
$q = mysql_query("SELECT * FROM `frends` WHERE `user` = '$ank[id]' AND `i` = '1' ORDER BY time DESC LIMIT $start, $set[p_str]");
if (isset($user) && $user['id']==$ank['id'])
{
if ($k_post>0)
echo "<form method='post' action='?$page&amp;delete'>";
}

if ($k_post==0)
{
echo '<div class="mess">';
echo ' '.($ank['id']==$user['id'] ? 'У Вас ' : 'У '.$ank['nick'].' ').' нет друзей.';
echo '</div>';
}

while ($frend = mysql_fetch_assoc($q))
{
$frend=get_user($frend['frend']);

/*-----------зебра-----------*/ 
if ($num==0){
	echo '<div class="nav1">';
	$num=1;
}elseif ($num==1){
	echo '<div class="nav2">';
	$num=0;
}
/*---------------------------*/
echo '<table><td style="width:'.($webbrowser ? '85px;' : '55px;').'">';
$sql=mysql_query("SELECT `id`,`id_gallery`,`ras` FROM `gallery_foto` WHERE `id_user`='".$frend['id']."' AND `avatar`='1' LIMIT 1");
if(mysql_num_rows($sql)==1){
$foto=mysql_fetch_assoc($sql);
echo '<a href="/foto/'.$frend['id'].'/'.$foto['id_gallery'].'/'.$foto['id'].'/"><img class="friends" style="width:'.($webbrowser ? '110px;' : '50px;').'" src="/foto/foto0/'.$foto['id'].'.'.$foto['ras'].'"></a>';
}else{
echo '<img class="friends" style="width:'.($webbrowser ? '80px;' : '50px;').'" src="/style/icons/avatar.png">';
}
echo '</td><td style="width:80%;">';
if (isset($user) && $user['id']==$ank['id'])echo " <input type='checkbox' name='post_$frend[id]' value='1' /> ";
echo " ".group($frend['id'])." \n";
echo user::nick($frend['id'],1,1,1);
echo '<br/><img src="/style/icons/alarm.png"> '.($webbrowser ? 'Посл. активность:' : null ).' '.vremja($frend['date_last']).' </td><td style="width:18px;">';
if (isset($user)){	echo "<a href=\"/mail.php?id=$frend[id]\"><img src='/style/icons/pochta.gif' alt='*' /></a><br/>\n";	
		if ($ank['id']==$user['id'])			echo "<a href='create.php?del=$frend[id]'><img src='/style/icons/delete.gif' alt='*' /></a>";
}
echo '</td></table></div>';

}

if (isset($user) && $user['id']==$ank['id'])
{
if ($k_post>0)
{
echo "<div class='c2'>";
echo " Отмеченных друзей:<br />";
echo "<input value=\"Удалить\" type=\"submit\" name=\"delete\" />";
echo "</div>";
echo "</form>\n";
}
}
if ($k_page>1)str("?id=".$ank['id']."&",$k_page,$page); // Вывод страниц
include_once '../../sys/inc/tfoot.php';
?>