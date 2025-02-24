<?php
require_once("global.php");
error_reporting(E_ERROR | E_PARSE);
$conn = $_SESSION['conn'];
session_start();

$module_name = '';
$script_name = 'MAIN MENU';

doValidateUserAccess($script_name,$module_name);

?>
<html>
<head>
</head>
<body>
<div id="section">
<?php include("header.php") ?>
<div id="leftcolumnwrap">
<div id="leftcolumn">
<?php include("menu.php") ?>
</div>
</div>
<div id="rightcolumnwrap">
<div id="rightcolumn">

<div class="scrollbarsDemo">

<div class="panel panel-default">
<div class="panel-body">
<?php
	$res_pending = $conn->query("SELECT COUNT(audit_id) AS cnt FROM audit_header WHERE status = 0") or die($conn->error);
	$cnt_pending = $res_pending->fetch_object()->cnt;

	$res_submitted= $conn->query("SELECT COUNT(audit_id) AS cnt FROM audit_header WHERE status = 1") or die($conn->error);
	$cnt_submitted= $res_submitted->fetch_object()->cnt;

	$res_rated= $conn->query("SELECT COUNT(audit_id) AS cnt FROM audit_header WHERE status = 2") or die($conn->error);
	$cnt_rated= $res_rated->fetch_object()->cnt;

	$res_app =  $conn->query("SELECT * FROM audit_header WHERE status = 0 AND approver_id = '".getUserId()."' ") or die($conn->error);


?>
<table width="100%" >
<tr>
<td width="20%" height="200px">
<div class="info-box orange-bg">
	<i class="fa fa-clock-o"></i>
	<div class="count"><?php echo $cnt_pending; ?></div>
	<div class="title">PENDING</div>
</div>
</td>
<td width="5%">&nbsp;</td>
<td width="20%" height="200px">
<div class="info-box green-bg">
	<i class="fa fa-cloud-upload"></i>
	<div class="count"><?php echo $cnt_submitted; ?></div>
	<div class="title">SUBMITTED</div>
</div>
</td>
<td width="5%">&nbsp;</td>
<td width="20%">
<div class="info-box blue-bg">
	<i class="fa fa-thumbs-o-up"></i>
	<div class="count"><?php echo $cnt_rated; ?></div>
	<div class="title">RATED</div>
</div>
</td>
<!--<td width="5%"></td>
<td width="20%">
<div class="info-box green-bg">
	<i class="fa fa-shopping-cart"></i>
	<div class="count"><?php echo $cnt_product; ?></div>
	<div class="title">PRODUCT</div>
</div>
</td> -->
</tr>
</table>

</div>
</div><br>

<div class="panel panel-default">
<div class="panel-body">
	<div class="row">
		<div class="task-progress pull-left">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font size=3px><b>Need Your Approval:- </b></font>
		</div>

	</div>
	<table class="table table-hover personal-task">
		<tbody><?php
	if($res_app->num_rows>0){
	while($row = $res_app->fetch_array(MYSQLI_ASSOC)){?>
	<tr>
	<td><a href="audit_new.php?audit_id=<?php echo $row['audit_id']; ?>"><?php echo $row['audit_id']; ?></a></td>
	<td><?php echo $row['reference_no']; ?></td>
	<td><?php echo $row['dept_name']; ?></td>
	<td><?php echo $row['sect_name']; ?></td>
	<td><?php echo $row['scope_name']; ?></td>
	<td>
	<span class="profile-ava">
	<img alt="" class="simple" src="img/avatar1_small.jpg">
	</span>
	</td>
	</tr><?php
	}
	}else{
	echo "There has no pending list";
	}?>

		</tbody>
	</table>

</div>
</div>


<?php include('footer.php'); ?>
</div>
</body>

</html>
