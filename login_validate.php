<?php 
require_once("global.php");
require_once("user.php");
session_start();
$user = new User();

$user->user_id = addslashes($_POST['user_id']);
$user->user_pwd = addslashes($_POST['user_pwd']);


if(userLogin($user)) {

	if(isset($_SESSION['current_page']) && $_SESSION['current_page']!=""){
		$redirectstr = $_SESSION['current_page'];
		$_SESSION['current_page'] = null;
	}else{
		if(isset($_SESSION['current_page']) && $_SESSION['current_page']!=""){
			$redirectstr = $_SESSION['current_page'];
			$_SESSION['current_page'] = null;
		}else{
			$redirectstr = 'main_menu.php';
		}
	}		
	header('Location:'. $redirectstr);
}?>
<html>
<head>
<SCRIPT type="text/javascript">
	window.history.forward();
    function noBack() { 
		window.history.forward(); 
	}

</SCRIPT>	
</head>
<body onunload="noBack();" onpageshow="if (event.persisted) noBack();" onunload="">
</body>
</html>
	