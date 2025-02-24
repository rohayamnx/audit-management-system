<?php
require_once("global.php");
?>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<link rel="stylesheet" type="text/css" href="css/main.css">

<!-- <link href="css/bootstrap.min.css" rel="stylesheet"> -->
<!-- bootstrap theme -->
<link href="css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="includes/fonts/css/font-awesome.min.css">
<link href="css/font-awesome.css" rel="stylesheet" />
<link href="css/swal.css" rel="stylesheet">
<link rel="stylesheet" href="includes/jquery-ui.css">
<link href="js/dataTables/dataTables.bootstrap.css" rel="stylesheet"/>
<link rel="shortcut icon" href="images/icon.ico" >
<!-- <script src="includes/jquery-ui.js"></script>
<script src="includes/jquery-1.8.0.min.js"></script> -->

<script src="js/jquery.js"></script>


<script src="js/swal.js"></script>
<!-- <script src="js/jquery-1.10.2.js"></script> -->

<!--  for signature -->
<script src="asset/jquery.min.js"></script>
<script src="asset/jquery-ui.min.js"></script>
<script type="text/javascript" src="asset/jquery.signature.min.js"></script>

<script src="js/bootstrap.min.js"></script>
<script src="js/dataTables/jquery.dataTables.js"></script>
<script src="js/dataTables/dataTables.bootstrap.js"></script>
<link rel="stylesheet" href="cssmenu/menu_styles.css">
<link rel="stylesheet" href="calendar/metallic.css" type="text/css">

<link rel="stylesheet" type="text/css" href="asset/jquery.signature.css">

<script>
	$(document).ready(function () {
		$('#dataTables-example').dataTable();
	});
</script>
<style>
.loading {
	position: fixed;
	left: 0px;
	top: 0px;
	width: 100%;
	height: 100%;
	z-index: 9999;
	background: url('images/ajax-loader.gif') 50% 40% no-repeat rgb(249,249,249);
}
</style>
<link rel="shortcut icon" href="images/icon.ico" >
<title>AMS System</title>
<!--script type="text/javascript" src="calendar/jquery-1.12.0.js"></script!-->
<script type="text/javascript" src="calendar/zebra_datepicker.js"></script>
<script type="text/javascript" src="calendar/core.js"></script>
<script language="JavaScript">
$(document).ready(function() {

    // assuming the controls you want to attach the plugin to
    // have the "datepicker" class set
    $('input.datepicker').Zebra_DatePicker();

 });
$(window).load(function() {
	$(".loading").fadeOut("slow");
})

function cUpper(cObj){
	cObj.value=cObj.value.toUpperCase();
}
window.history.forward();
function noBack() {
	window.history.forward();
}
</script>
</head>
<body onunload="noBack();" onpageshow="if (event.persisted) noBack();" onunload="">
<div id="head-content">
<a href="main_menu.php" class="logo">AMS <span class="lite">SYSTEM</span></a>
<div class="top-nav notification-row">
<p><i><span class="lite"><?php echo getUserId(); ?>,</span></i> Last access : <?php echo getLastLogin();?>&nbsp;&nbsp;
<a href="logout.php" class="btn btn-danger square-btn-adjust btn-xs logout">Logout</a>
</div>
</div>
</body>
<html>
