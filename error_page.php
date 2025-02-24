<?php
require_once("global.php");
error_reporting(E_ERROR | E_PARSE);

session_start();

if ($_SESSION['error'] == "display_Message_InvalidAccess") { 

	$var1 = "Invalid Username or Password!";
	$var2 = "Please try again or contact the Administrator for Help!";
	$var3 = "Press Here to Login!";

}elseif($_SESSION['error']  == "display_Message_NoPremission") { 

	$var1 = "Authorization Denied!";
	$var2 = "Please contact the Administrator for permission request!";
	$var3 = "Press Here to Go Back!";

}elseif($_SESSION['error']  == "display_Message_NoExternal") { 

	$var1 = "Authorization Denied for External Link Access!";
	$var2 = "Please contact the Administrator for Help!";
	$var3 = "Press Here to Login!";

}elseif ($_SESSION['error']  == "display_Message_UserNotLogin") { 

	$var1 = "You have not Log In!";
	//$var2 = "Please Log In First!";
	$var3 = "Press Here to Login!";

}elseif ($_SESSION['error']  == "display_Message_LogoutSucessful") { 

	$var1 = "You have LogOut Successfully!";
	$var3 = "Press Here to Login!";

}

?>
<html>
<head>
<?php if($_SESSION['error']  != "display_Message_NoPremission") { ?>
<META HTTP-EQUIV="REFRESH" CONTENT="5; URL=login.php"><?php
}?>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<link rel="stylesheet" media="screen,projection" type="text/css" href="css/main.css" /> 
<style>
	#top_login{
	width: 100%;
	padding-top:20px;
	padding-bottom:80px;
	background: url(images/top_login.gif) repeat-x ;	
	}
	#logo{	
    margin:0 auto;	
 	width:100%;
    }
	.login{
	background-color: #FFF;
	border: 8px solid #efefef;
	width: 650px;
	text-align: left;
	}
</style>
</head>
<body>
<div id="section">
<?php include('header_index.php'); ?>

<div id="logo">
	<div align="center"><img src="images/warning.gif" width="230px"/></div>
</div>
<div  align="center" border=0>  
<table align="center" class="login_bk" border=0>
<tr align="center" height="50px">
<td><font size=4><?php echo $var1; ?></font></td>
</tr>
<tr>
<td align="center" height="50px"><?php echo $var2; ?></td>
</tr>
<tr>
<td align="center" height="50px">
<?php if($_SESSION['error']  != "display_Message_NoPremission") { ?>
[ <a href="login.php"><?php echo $var3; ?></a> ]<?php
}else{?>
<a href='main_menu.php'><?php echo $var3; ?></a><?php
}?>
</td>
</tr>
</table>
</div>
	
<?php include('footer.php'); ?>
</div>
</body>
</html>