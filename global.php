<?php
ini_set('display_errors',1); // 1 - Sandbox, 0 - Production
require_once("user.php");

error_reporting(E_ERROR);
session_start();
$dbhost 	= 'localhost';
$dbuser 	= 'root';
$dbpasswd	= '';
$dbname		= 'ams';

if(!empty($_POST)){
	extract($_POST);
}

if(empty($_POST)){
	extract($_GET);
}

session_start();

//turn on off validate...
$validateAccess = true;

if(!($conn = mysqli_connect($dbhost, $dbuser, $dbpasswd, $dbname))) die("Failed to connect mysql. " . mysqli_error());

mysqli_set_charset('utf8',$conn);
$_SESSION['conn'] = $conn;

function isLogin(){
	return (isset($_SESSION['user_session']));
}

function userLogin($user){

	$userLogin = $user;
	if(!isset($_SESSION['user_session'])){

		$query = "SELECT * FROM ms_user WHERE user_id='$userLogin->user_id' AND user_pwd='".md5($userLogin->user_pwd)."'";

		if (!($user_result = mysqli_query($_SESSION['conn'], $query)) );
		if (mysqli_num_rows($user_result)  == 0 ) {

			display_Message_InvalidAccess();
			return false;

		}else{

			$user_arr = mysqli_fetch_array($user_result, MYSQLI_ASSOC);
			$userLogin->user_id = $user_arr["user_id"];
			$userLogin->user_name = $user_arr["user_name"];
			$userLogin->department = $user_arr["department"];
			$userLogin->position = $user_arr["position"];
			$userLogin->access_level = $user_arr["access_level"];
			$userLogin->email = $user_arr["email"];
			$userLogin->user_pwd = $user_arr["user_pwd"];
			$userLogin->user_pwd ='';
			$userLogin->last_login = $user_arr["last_login"];

			$timeStamp_query = mysqli_query($_SESSION['conn'],"SELECT FROM_UNIXTIME(UNIX_TIMESTAMP())");
			$tmp_arr = mysqli_fetch_array($timeStamp_result);
			$userLogin->loginDateTime = $tmp_arr[0];


			$upd_user1 = mysqli_query($_SESSION['conn'], "UPDATE ms_user SET act_flag = '1'
											, login_from='".$_SERVER['REMOTE_ADDR'].":".$_SERVER['SERVER_PORT']."@".$userLogin->loginDateTime ."'
											, rvsby = '$userLogin->user_id'
											, rvsdate = '$userLogin->loginDateTime'
									WHERE user_id = '$userLogin->user_id'");


			$_SESSION['user_session'] = $userLogin;
			return true;
		}
	}else{

		return true;
	}
}

function display_Message_InvalidAccess() {
	$_SESSION['error'] = "";
	$_SESSION['error'] = "display_Message_InvalidAccess";
	require_once("error_page.php");

}

function display_Message_NoPremission() {
	$_SESSION['error'] = "";
	$_SESSION['error'] = "display_Message_NoPremission";
	require_once("error_page.php");
}

function display_Message_NoExternal() {
	$_SESSION['error'] = "";
	$_SESSION['error'] = "display_Message_NoExternal";
	require_once("error_page.php");
}

function display_Message_UserNotLogin() {
	$_SESSION['error'] = "";
	$_SESSION['error'] = "display_Message_UserNotLogin";
	require_once("error_page.php");
}

function display_Message_LogoutSucessful(){
	$_SESSION['error'] = "";
	$_SESSION['error'] = "display_Message_LogoutSucessful";
	require_once("error_page.php");
}

function getUserLoginDateTime(){
	$loginDateTime="";
	if(isset($_SESSION['user_session'])){
		$userData = $_SESSION['user_session'] ;
		$loginDateTime = $userData->loginDateTime;
	}

	return ($loginDateTime);
}

function invalidate(){
	session_unset();
}

function doValidateUserAccess($script_name,$module_name) {
global $validateAccess;

	if($validateAccess){
		doQueryModule($module_name,$script_name);
		if(isLogin()){

				$userLogin = $_SESSION['user_session'] ;
				$query = "SELECT * FROM ms_halevel WHERE access_level='$userLogin->access_level'";
				if (!($result = mysqli_query($_SESSION['conn'],$query)) );
				if ( mysqli_num_rows($result) == 0 ) {
					echo display_Message_NoPremission();

					die();
				}else{
					$queryAccess = "SELECT * FROM ms_dalevel WHERE access_level='$userLogin->access_level' AND script_name='".addslashes($script_name)."'";

					if (!($resultAccess = mysqli_query($_SESSION['conn'], $queryAccess)) );
					if ( mysqli_num_rows($resultAccess) == 0 ) {
						echo  display_Message_NoPremission();
						die();
					}else{
						return true;
					}
				}

		}else{
			set_current_page();
			echo display_Message_UserNotLogin();
			die();
		}
	}
	return true;

}

function doQueryModule($moduleMasterdesc,$moduleDetaildesc){
	$query = mysqli_query($_SESSION['conn'],"SELECT * FROM ms_module WHERE module_desc = '$moduleMasterdesc'");
	$masterId=0;
	if($query){
		if(mysqli_num_rows($query)==0){
			//$masterModuleQuery = createQuery("INSERT INTO mmodule values( '','$moduleMasterdesc', ");
			//mysql_query($masterModuleQuery);

			//$masterModuleQuery = "SELECT * FROM ms_module WHERE module_desc = '$moduleMasterdesc'";
			//$query = mysql_query($masterModuleQuery);
		}

		//$masterId = mysqli_result($query,0,"module_id");


	}

	$detailModuleQuery = "SELECT * FROM ms_dmodule WHERE mdetail_desc = '$moduleDetaildesc'";
	$query = mysqli_query($_SESSION['conn'],$detailModuleQuery);

	if($query){
		if(mysqli_num_rows($query)==0){
			//$detailModuleQuery = mysqli_query($_SESSION['conn'], "INSERT INTO dmodule values( '','$masterId','$moduleDetaildesc','', ");
			//mysqli_query($detailModuleQuery);
		}
	}
}

function getUserId(){
	$userId="";
	if(isset($_SESSION['user_session'])){
		$userData = $_SESSION['user_session'] ;
		$userId = $userData->user_id;
		$userId = addslashes($userId);
	}
	return ($userId);
}

function getUserName(){
	$userName="";
	if(isset($_SESSION['user_session'])){
		$userData = $_SESSION['user_session'] ;
		$userName = $userData->user_name;
	}

	return ($userName);
}

function getLastLogin(){
	$last_login="";
	if(isset($_SESSION['user_session'])){
		$userData = $_SESSION['user_session'] ;
		$last_login = $userData->last_login;
	}
	return ($last_login);
}

function getCurrentTimeStamp(){
	$timeStamp_query = mysqli_query($_SESSION['conn'], "SELECT FROM_UNIXTIME(UNIX_TIMESTAMP())");
        $tmp_arr = mysqli_fetch_array($timeStamp_query);
	return($tmp_arr[0]);
}

function set_current_page(){
	$temp="";
	foreach($_GET as $key => $value){
		$temp.=$key."=".$value."&";
	}
	$temp = substr($temp,0,(strlen($temp)-1));
	$_SESSION['current_page'] = $_SERVER['PHP_SELF']."?".$temp;
}

function getCurrentMonth() {
	$timestamp = time();
	$date_time_array =  getdate($timestamp);

	$month =  $date_time_array["mon"];


	if (strlen($month)==1) $month = "0".$month;

	return $month;
}

function getCurrentYear() {
	$yy=date(Y);
	return $yy{2}.$yy{3};
}

function close_Mysql(){
	global $conn;
	if($conn)
	mysql_close($conn);
}


?>
