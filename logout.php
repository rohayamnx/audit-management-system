<?php
require("global.php");
session_start();
$conn = $_SESSION['conn'];

$rvsby = getUserId();
$rvsdate = getCurrentTimeStamp();

if(!isset($_SESSION['user_session'])){
	echo display_Message_UserNotLogin();
}else{
	$upd_user = $conn->query("UPDATE ms_user SET last_login = '$rvsdate' WHERE user_id = '$rvsby'");
	session_unset();
	echo"<script language='JavaScript'>alert('You are Successfully Log out')</script>";
	$url = "login.php";
	echo'<meta http-equiv="refresh" content="0;URL='.$url.'">';
}

?>
