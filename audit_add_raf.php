<?php
require_once("audit.inc.php");
require_once("global.php");
session_start();
$module_name = 'TRANSACTION';
$script_name = 'AUDIT FINDINGS';
doValidateUserAccess($script_name,$module_name);

$trxBy = getUserId();
$trxDate = getCurrentTimeStamp();

$audit = new AuditModel();

if(empty($_POST)){
	$_POST['issue_id'] = $_GET['issue_id'];
	$_POST['review_list'] = $_GET['review_list'];
	$_POST['raf_no'] = $_GET['raf_no'];
	$_POST['status'] = $_GET['status'];

	$i = $_SESSION[$_POST['review_list']];
	if (empty($i)) $i = new ReviewObj();

	if($_POST['raf_no'] != "new"){
		$_POST['new_review'] = $i->getItemsRaf($_POST['issue_id'],$_POST['raf_no'])->{'review'};

	}

}


if(isset($_POST['add_review'])){

	$i = $_SESSION[$_POST['review_list']];
	if (empty($i)) $i = new ReviewObj();

	$proceed  = true;

	if (empty($_POST['new_review'])) {
		$errMsg = "Audit Findings can not be empty";
		$proceed = false;
	}

	if ($proceed) {

			$res = $audit->addReviewItem($i, $_POST);
			$_POST['new_review'] = "";

    }

	$_SESSION[$_POST['review_list']] = $i;
// exit();
	echo'<script>';
	echo'window.opener.AddReview();';
	echo'window.close();';
	echo'</script>';
}
?>
<html>
<header>
</header>
<body>
<div id="section">

	<div class="scrollbarsDemo">
<?php include("header_index.php") ?>
<div class="panel panel-default">
<div class="panel-body">
<form name="frm" id="frm" method="post"  action="">
<input type="hidden" name="issue_id"  value="<?php echo $_POST['issue_id']; ?>">
<input type="hidden" name="review_list" size="50" value="<?php echo $_POST['review_list']; ?>">
<input type="hidden" name="raf_no" size="10" value="<?php echo $_POST['raf_no']; ?>">
<input type="hidden" name="status" size="10" value="<?php echo $_POST['status']; ?>">
<?php
if($errMsg !=""){

		echo '<script>MsgStatus("'.$errMsg.'");</script>';	?>
		<div class="alert alert-danger alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
			<a href="#" class="alert-link">Alert</a>:<?php echo $errMsg;?>
		</div><?php
}?>
<table width="100%" class="frm" align="center">
	<tr>
		<td><u>Audit Findings : </u></td>
	</tr>
	<tr>
		<td><textarea id="new_review" name="new_review" rows="5" cols="70"><?php echo $_POST['new_review']; ?></textarea></td>
		<td></td>
	</tr>
	<tr>
		<td><?php
			if($_POST['status'] == 0){?>
					<input type="submit" name="add_review" value="<?php echo (empty($_POST['new_review'])) ? "AddReview" : "Update Review"; ?>" class="btn btn-primary btn-xs"><?php
			}?>
			<button type="button" name="btnback" value="Back" onclick="javascript:window.close();" class="btn btn-primary btn-xs"><i class="fa fa-backward"></i> Exit</button>
		</td>
	</tr>
	<tr><td height="20px"></td></tr>
</table>


<br>

</form>
</div>
</div>
<?php include('footer.php'); ?>
</div>
<script language="javascript">

function onChange(){
 document.getElementById("search_btn").click();

}

</script>
</body>

</html>
