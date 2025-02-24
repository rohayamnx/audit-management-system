<?php
require_once("global.php");
require_once("user.php");
session_start();
$ipaddr = $_SERVER['REMOTE_ADDR'];

if(isLogin()) {
	if(isset($_SESSION['current_page']) && $_SESSION['current_page']!=""){
		$redirectstr = $_SESSION['current_page'];
		$_SESSION['current_page'] = null;
	}else{
		$redirectstr = 'main_menu.php';
	}
	header('Location:'. $redirectstr);
}

?>
<html>
<head>
<!--[if lte IE 7]><style>.main{display:none;} .support-note .note-ie{display:block;}</style><![endif]-->
<script type="text/javascript">

function focusonlogin(){
login.user_id.focus();
}

</script>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<link rel="stylesheet" type="text/css" href="css/main.css">
<link rel="stylesheet" href="includes/fonts/css/font-awesome.min.css">
<link href="css/font-awesome.css" rel="stylesheet" />
<link rel="shortcut icon" href="images/icon.ico" >
<title>AMS System</title>
</head>
<body >

<div id="head-content-normal"></div>
<div id="loginwrap">
<div id="login" >
<section id="main">
	<!--div id="login_icon"><p>SYSTEM <span>LOGIN</span></p></div!-->
	<div id="login_icon">
	<!-- <img src="images/logo.png"> -->
	<center><h1>AMS SYSTEM</h1></center>
	<img src="images/logo.jpeg">

	</div>

	<form method="post" name="login" class="form-1" action="login_validate.php" method="POST">
		<p class="field">
			<input type="text" name="user_id" value="<?php echo empty($_POST['user_id']) ? "" : $_POST['user_id'];?>" placeholder="Username or email">
			<i class="fa fa-user icon-large"></i>
		</p>
		<p class="field">
			<input type="password" name="user_pwd" value="<?php echo empty($_POST['user_pwd']) ? "" : $_POST['user_pwd'];?>" placeholder="Password">
			<i class="fa fa-lock icon-large"></i>
		</p>
		<p class="submit">
			<button type="submit" name="login"><i class="fa fa-arrow-right icon-large"></i></button>
		</p>
	</form>
</section>
</div>
<?php include('footer.php'); ?>
</div>
</div>
</body>

</html>
