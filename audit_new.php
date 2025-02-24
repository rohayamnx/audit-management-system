<?php
require_once("audit.inc.php");
require_once("global.php");
error_reporting(E_ERROR | E_PARSE);
$conn = $_SESSION['conn'];
session_start();

$module_name = 'TRANSACTION';
$script_name = 'AUDIT FINDINGS';

doValidateUserAccess($script_name,$module_name);
$trxBy = getUserId();
$trxDate = getCurrentTimeStamp();

$audit = new AuditModel();
$org_arr = $audit->getOrganization();
$user_arr = $audit->getUser();
$scope_arr = $audit->getScope();

$position = $audit->getPosition($trxBy);

$curr_dd = date('Y-m-d');

if (empty($_POST) && empty($_GET['audit_id'])) {
	//define random session
	$_POST['review_list'] = 'review_'.substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',32)),0,32);

	$i = new ReviewObj();
	$_SESSION[$_POST['review_list']] = $i;

	$_POST['edit_flag'] = false;

	$_POST['crt_by'] = $trxBy;
	$_POST['crt_dd'] = $trxDate;


} if (empty($_GET['audit_id'])) {

		$dept_arr = $audit->getDepartment($_POST['org_name']);
		$sect_arr = $audit->getSection($_POST['org_name']);

} if (empty($_POST) && !empty($_GET['audit_id'])) {

	unset($_SESSION[$_POST['review_list']]);
	//define random session
	$_POST['review_list'] = 'review_'.substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',32)),0,32);

	if (empty($i)) $i = $audit->prepareReview($_POST['review_list']);
	$arr = $audit->getAudit($_GET['audit_id']);

	$_POST['crt_by'] = $arr['crt_by'];
	$_POST['crt_dd'] = $arr['crt_dd'];
	$_POST['org_name'] = $arr['org_name'];
	$_POST['dept_name'] = $arr['dept_name'];
	$_POST['sect_name'] = $arr['sect_name'];
	$_POST['scope_name'] = $arr['scope_name'];
	$_POST['status'] = $arr['status'];
	$_POST['date_from'] = $arr['date_from'];
	$_POST['date_to'] = $arr['date_to'];
	$_POST['approver_id'] = $arr['approver_id'];
	$_POST['signature'] = $arr['signature'];
	$_POST['reference_no'] = $arr['reference_no'];
	$_POST['team_id'] = $arr['team_id'];

	//check scope
	$res_check = $conn->query("SELECT * FROM ms_scope WHERE scope_name='$_POST[scope_name]'");
	if ($res_check->num_rows >0) {
		$_POST['scope_name'] = $arr['scope_name'];
	} else {
		$_POST['scope_name'] = 'OTHER';
		$_POST['other_scope'] = $arr['scope_name'];
	}


	// echo $_POST['signature'];

	$_POST['edit_flag'] = true;

}

if(isset($_POST['save']))
{
	$i = $_SESSION[$_POST['review_list']];
	if (empty($i)) $i = new ReviewObj();

	$i->updItemAll($_POST);

	$proceed = true;

	$raf_score_check = 0;
	foreach($i->getItemsAll() AS $issue_k=>$issue_arr) {
			foreach ($issue_arr AS $raf_k=>$raf_v){
					if ($raf_v->raf_score == "") {
						$raf_score_check += 1;
					}
			}
	}

	if($i->getItemCountAll() == 0) {
		 $proceed = false;
		 $errMsg = "Please add atleast one Audit Findings.";
	}elseif (empty($_POST['date_from'])) {
		$errMsg = "Please choose Date From";
		$proceed = false;
	}elseif (empty($_POST['date_to'])) {
		$errMsg = "Please choose Date To";
		$proceed = false;
	}elseif (empty($_POST['org_name'])) {
		$errMsg = "Please choose one Organization";
		$proceed = false;
	}else if (empty($_POST['dept_name'])) {
		$errMsg = "Please choose one Department";
		$proceed = false;
	}elseif (empty($_POST['sect_name'])) {
		$errMsg = "Please choose one Unit";
		$proceed = false;
	}elseif (empty($_POST['reference_no'])) {
		$errMsg = "Please key in Reference No";
		$proceed = false;
	}elseif (empty($_POST['scope_name'])) {
		$errMsg = "Please choose one Scope";
		$proceed = false;
	}elseif($_POST['scope_name'] == "OTHER" && $_POST['other_scope'] == ""){
		$errMsg = "Please Enter Your Other Scope";
		$proceed = false;
	}elseif (empty($_POST['approver_id'])) {
		$errMsg = "Please choose one Approver ID";
		$proceed = false;
	}elseif (empty($_POST['team_id'])) {
		$errMsg = "Please choose Team ID.";
		$proceed = false;
	}elseif ($raf_score_check > 0) {
		$errMsg = "Please choose Score for Every RAF No.";
		$proceed = false;
	}

	$_POST['signature'] == "";




	if ($proceed) {

		if (empty($_GET['audit_id'])) {
				$audit_id = $audit->getUniqueID($curr_dd);

				if(!empty($audit_id)){
					$res = $audit->insertAudit($i, $_POST, $audit_id, $trxBy, $trxDate);
				}
			  $url = "audit_new.php?audit_id=$audit_id";
		}else{

				 $res = $audit->updateAudit($i, $_POST, $_GET['audit_id'], $trxBy, $trxDate);
				 $url = "audit_new.php?audit_id=$_GET[audit_id]";
		}

		if($res){
			$errMsg = "New Audit Added Sucessfully..!";
			header('Location:'. $url);
		}else{
			$errMsg = "Data Fail to Add..!";
		}

	}
}elseif (isset($_POST['add_review'])) {

	$i = $_SESSION[$_POST['review_list']];
  if (empty($i)) $i = new ReviewObj();

	$i->updItemAll($_POST);

  // $proceed = true;
	//
	// if (empty($_POST['new_review'])) {
	// 	$errMsg = "Please add a review before add";
	// 	$proceed = false;
  // }
	//
	// if ($proceed) {
	//
	// 	$res = $audit->addReviewItem($i, $_POST, $_POST['new_number_o']);
	// 	$_POST['new_review'] = "";
	//
	//
	// }

	// $dept_arr = $audit->getDepartment($_POST['org_name']);
	// $sect_arr = $audit->getSection($_POST['org_name']);

	$_SESSION[$_POST['review_list']] = $i;

} else if (isset($_POST['delete_raf'])) {

	$i = $_SESSION[$_POST['review_list']];
  if (empty($i)) $i = new ReviewObj();

	$i->updItemAll($_POST);

  if (!empty($_POST['tmp_issue_id']) && !empty($_POST['tmp_raf_no'])){
				$i->deleteItem($_POST['tmp_issue_id'], $_POST['tmp_raf_no']);
				$_POST['tmp_issue_id'] = "";
				$_POST['tmp_raf_no'] = "";
  } else {
      $errMsg = "WARNING: Please select one item to delete.";
  }

    $_SESSION[$_POST['review_list']] = $i;


}elseif(isset($_POST['submit_audit'])){

	$i = $_SESSION[$_POST['review_list']];
	if (empty($i)) $i = new ReviewObj();

	$proceed = true;

	$raf_score_check = 0;
	foreach($i->getItemsAll() AS $issue_k=>$issue_arr) {
			foreach ($issue_arr AS $raf_k=>$raf_v){
					if ($raf_v->raf_score == "") {
						$raf_score_check += 1;
					}
			}
	}


	if($i->getItemCountAll() == 0) {
		 $proceed = false;
		 $errMsg = "Please add atleast one Audit Findings.";
	}elseif (empty($_POST['date_from'])) {
		$errMsg = "Please choose Date From";
		$proceed = false;
	}elseif (empty($_POST['date_to'])) {
		$errMsg = "Please choose Date To";
		$proceed = false;
	}elseif (empty($_POST['org_name'])) {
		$errMsg = "Please choose one Organization";
		$proceed = false;
	}else if (empty($_POST['dept_name'])) {
		$errMsg = "Please choose one Department";
		$proceed = false;
	}elseif (empty($_POST['sect_name'])) {
		$errMsg = "Please choose one Unit";
		$proceed = false;
	}elseif (empty($_POST['reference_no'])) {
		$errMsg = "Please key in Reference No";
		$proceed = false;
	}elseif (empty($_POST['scope_name'])) {
		$errMsg = "Please choose one Scope";
		$proceed = false;
	}elseif($_POST['scope_name'] == "OTHER" && $_POST['other_scope'] == ""){
		$errMsg = "Please Enter Your Other Scope";
		$proceed = false;
	}elseif (empty($_POST['approver_id'])) {
		$errMsg = "Please choose one Approver ID";
		$proceed = false;
	}elseif ($raf_score_check > 0) {
		$errMsg = "Please choose Score for Every RAF No.";
		$proceed = false;
	}elseif(empty($_POST['signature'])){
		$errMsg = "Please sign before submit audit";
		$proceed = false;
	}


	if ($proceed) {
		$res = $audit->updateAudit($i, $_POST, $_GET['audit_id'], $trxBy, $trxDate);
		$res_app = $audit->approve($_GET['audit_id'], $trxBy, $trxDate);

		unset($_SESSION[$_POST['review_list']]);

		$errMsg = "STATUS: Audit Trail is submitted!";
		$url = "audit_new.php?audit_id=$_GET[audit_id]";
		echo'<meta http-equiv="refresh" content="0;URL='.$url.'">';

	}



}elseif(isset($_POST['delete_audit'])){
	$i = $_SESSION[$_POST['review_list']];
	if (empty($i)) $i = new ReviewObj();

	$res = $audit->deleteAuditDetails($i, $_GET['audit_id'], $trxBy, $trxDate, true);
	// function deleteAuditDetails($i, $audit_id, $trxBy, $trxDate, $is_cancel=false)

	unset($_SESSION[$_POST['review_list']]);

	$errMsg = "STATUS: Audit Trail is deleted!";
	$url = "audit_list.php";
	echo'<meta http-equiv="refresh" content="0;URL='.$url.'">';
}

if(!empty($_POST) && !empty($_POST['org_name'])){
	$i = $_SESSION[$_POST['review_list']];
	if (empty($i)) $i = new ReviewObj();

	$dept_arr = $audit->getDepartment($_POST['org_name']);
	$sect_arr = $audit->getSection($_POST['org_name']);
}

if(!empty($_POST) && !empty($_POST['org_name'])){
	$i = $_SESSION[$_POST['review_list']];
	if (empty($i)) $i = new ReviewObj();

	$dept_arr = $audit->getDepartment($_POST['org_name']);
	$sect_arr = $audit->getSection($_POST['org_name']);
}


if(!empty($_POST)){
	$i = $_SESSION[$_POST['review_list']];
	if (empty($i)) $i = new ReviewObj();

	$i->updItemAll($_POST);
}

$issue_arr = $audit->getListIssue();
$rating_arr = $audit->getListRating();

$check_team = $audit->getTeam($trxBy);

if(empty($GET['audit_id'])){
	$team_arr = $audit->getTeam($_POST['crt_by']);
}

$is_disable = "";
$is_disable2 = false;
if($_POST['status'] >0){
	$is_disable = "disabled";
	$is_disable2 = true;
}

if($check_team != $team_arr){
	$is_disable = "disabled";
	$is_disable2 = true;

	$info = "This ID under $check_team. You are not allowed to edit this file.";
}



?>

<html>
<head>
<style>

		.sign{float: right; margin-bottom:10px;}
		.kbw-signature { width: 300px; height: 100px;}

		#sig canvas{

				width: 100% !important;

				height: auto;

		}

</style>
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
<?php
if($errMsg !=""){

		echo '<script>MsgStatus("'.$errMsg.'");</script>';	?>
		<div class="alert alert-danger alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
			<a href="#" class="alert-link">Alert</a>:<?php echo $errMsg;?>
		</div><?php
}

if($info !=""){?>
		<div class="alert alert-danger alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
			<a href="#" class="alert-link">Alert</a>:<?php echo $info;?>
		</div><?php
}?>

<h2 align="center"><?php echo $module_name. " - ". $script_name ?></h2>
<form name="frm" id="frm" method="post"  action="">
<input type="hidden" name="review_list" size="50" value="<?php echo $_POST['review_list']; ?>">
<input type="hidden" name="status" size="2" value="<?php echo $_POST['status']; ?>">
<input type="hidden" name="tmp_issue_id" size="20" value="<?php echo $_POST['tmp_issue_id']; ?>">
<input type="hidden" name="tmp_raf_no" size="20" value="<?php echo $_POST['tmp_raf_no']; ?>">
<div class="panel panel-default">
<div class="panel-body">
	<div class="form-group">
		<div class="col-lg-10">
			<div class="row">
				<label class="control-label col-lg-2" for="inputSuccess">Conducted By</label>
				<div class="col-lg-4">
					<input type="text" class="form-control"name="crt_by" size="10" maxlength="10" value="<?php echo $_POST['crt_by'];?>" readonly class="readonly">
				</div>
				<label class="control-label col-lg-2" for="inputSuccess">Report Issued On</label>
				<div class="col-lg-4">
					<input type="text" class="form-control" name="crt_dd" size="20" maxlength="20" value="<?php echo $_POST['crt_dd'];?>" readonly class="readonly">
				</div>
			</div>

		</div>
	</div><br><br>

	<div class="form-group">
		<div class="col-lg-10">
			<div class="row">
				<label for="inputEmail1" class="col-lg-2 control-label">Date From</label>
				<div class="col-lg-4">
					<input type="date" class="form-control" name="date_from" value="<?php echo $_POST['date_from'];?>">
				</div>
				<label for="inputEmail1" class="col-lg-2 control-label">Date To</label>
				<div class="col-lg-4">
					<input type="date" class="form-control" name="date_to" value="<?php echo $_POST['date_to'];?>">
				</div>
			</div>
		</div>
	</div><br><br>

	<div class="form-group">
		<div class="col-lg-10">
			<div class="row">
				<label for="inputEmail1" class="col-lg-2 control-label">Business Oblige Function</label>
				<div class="col-lg-4">
					<?php
						if($is_disable2 == false){?>
								<select name="org_name" onChange="javascript:Refresh();" class="form-control">
									<option value=""></option> <?php
						              if (!empty($org_arr)) {
						                    foreach ($org_arr AS $v) {?>
												<option value="<?php echo $v['org_name']; ?>" <?php echo ($_POST['org_name'] == $v['org_name']) ? "selected" : ""?>><?php echo $v['org_name'];?></option><?php
											}
									} ?>
						    </select><?php
						} else{?>
							<select name="org_name" class="form-control">
										<option value="<?php echo $_POST['org_name']; ?>" ><?php echo $_POST['org_name'];?></option>
							</select><?php

						}?>
				</div>
				<label for="inputEmail1" class="col-lg-2 control-label">Department/Branch</label>
				<div class="col-lg-4">
					<?php
						if($is_disable2 == false){?>
							<select name="dept_name" class="form-control" >
								<option value=""></option> <?php
					              if (!empty($dept_arr)) {
					                    foreach ($dept_arr AS $v) {?>
											<option value="<?php echo $v['dept_name']; ?>" <?php echo ($_POST['dept_name'] == $v['dept_name']) ? "selected" : ""?>><?php echo $v['dept_name'];?></option><?php
										}
								} ?>
					    </select><?php
						}else{?>
							<select name="dept_name" class="form-control">
										<option value="<?php echo $_POST['dept_name']; ?>" ><?php echo $_POST['dept_name'];?></option>
							</select><?php

						}?>
				</div>
			</div>
		</div>
	</div><br><br>

	<div class="form-group">
		<div class="col-lg-10">
			<div class="row">
				<label for="inputEmail1" class="col-lg-2 control-label">Unit</label>
				<div class="col-lg-4">
					<?php
						if($is_disable2 == false){?>
							<select name="sect_name" class="form-control">
				          <option value=""></option>
								  <?php if (!empty($sect_arr)) {
										foreach ($sect_arr AS $v) {
									?>
										<option value="<?php echo $v['sect_name']; ?>" <?php echo ($_POST['sect_name'] == $v['sect_name']) ? "selected" : ""?>><?php echo $v['sect_name'];?></option>
									<?php }
								  } ?>
							</select><?php
						}else{?>
							<select name="sect_name" class="form-control">
										<option value="<?php echo $_POST['sect_name']; ?>" ><?php echo $_POST['sect_name'];?></option>
							</select><?php

						}?>
				</div>
				<label for="inputEmail1" class="col-lg-2 control-label">Reference No</label>
				<div class="col-lg-4">
					<input type="text" name="reference_no" class="form-control" size="20" maxlength="50" value="<?php echo $_POST['reference_no'];?>">
				</div>
			</div>

		</div>
	</div><br><br>

	<div class="form-group">
		<div class="col-lg-10">
			<div class="row">
				<label for="inputEmail1" class="col-lg-2 control-label">Audit Scope</label>
				<div class="col-lg-4">
					<?php
						if($is_disable2 == false){?>
								<select name="scope_name" class="form-control" onChange="javascript:scope_change(this.value);">
					          <option value=""></option>
									  <?php if (!empty($scope_arr)) {
											foreach ($scope_arr AS $v) {
										?>
											<option value="<?php echo $v['scope_name']; ?>" <?php echo ($_POST['scope_name'] == $v['scope_name']) ? "selected" : ""?>><?php echo $v['scope_name'];?></option>
										<?php }
									  } ?>
								<option value="OTHER" <?php echo (($_POST['scope_name']=="OTHER")?"selected":"")?>>OTHER</option>
								</select><?php
							}else{?>
								<select name="scope_name" class="form-control">
											<option value="<?php echo $_POST['scope_name']; ?>" ><?php echo $_POST['scope_name'];?></option>
								</select><?php

							}?>
				</div>
				<label for="inputEmail1" class="col-lg-2 control-label">OTHER SCOPE</label>
				<div class="col-lg-4">
					<input type="text" name="other_scope"  size="50" maxlength="100" value="<?php echo $_POST['other_scope'];?>" <?php if(empty($_POST['other_scope'])){ echo "disabled"; }?> class="form-control">
				</div>
			</div>
		</div>
	</div><br><br>

	<div class="form-group">
		<div class="col-lg-10">
			<div class="row">
				<label for="inputEmail1" class="col-lg-2 control-label">Approver</label>
				<div class="col-lg-4">
					<?php
						if($is_disable2 == false){?>
						<select name="approver_id" class="form-control">
								<option value=""></option>
								<?php if (!empty($user_arr)) {
									foreach ($user_arr AS $v) {
								?>
									<option value="<?php echo $v['user_id']; ?>" <?php echo ($_POST['approver_id'] == $v['user_id']) ? "selected" : ""?>><?php echo $v['user_id'];?></option>
								<?php }
								} ?>
						</select><?php
						}else{?>
							<select name="approver_id" class="form-control">
										<option value="<?php echo $_POST['approver_id']; ?>" ><?php echo $_POST['approver_id'];?></option>
							</select><?php

						}?>
				</div>
				<label for="inputEmail1" class="col-lg-2 control-label">Team ID</label>
				<div class="col-lg-4"><?php
						if($is_disable2 == false){?>
						<select name="team_id" class="form-control">
								<?php if (!empty($team_arr)) {
									// foreach ($team_arr AS $v) {?>
									<option value="<?php echo $team_arr; ?>" <?php echo ($_POST['team_id'] == $team_arr) ? "selected" : ""?>><?php echo $team_arr;?></option><?php

							 }else{?>
								<option value="" >Please Set Team ID for this User (<?php echo $trxBy; ?>)!</option><?php
							} ?>
						</select><?php
						}else{?>
							<select name="team_id" class="form-control">
										<option value="<?php echo $_POST['team_id']; ?>" ><?php echo $_POST['team_id'];?></option>
							</select><?php

						}?>
				</div>
			</div>
		</div>
	</div><br><br>


<table width="100%" class="frm">


</table>
<input type="submit" name="add_review"  id="add_review" value="add_review" style="visibility: hidden;">

<?php

 //echo '<pre>';
 //print_r($i->getItemsAll());
 //echo '</pre>';

if (!empty($i) || $i != '') {?>
	<table width="100%" class="frm">
	<tr>
		<td>
		<table width="100%" class="table table-striped table-hover">
			<thead>
			<!--tr>
				<td colspan=8>The following documentation was reviewed : </td>
			</tr!-->
			<tr>
				<td>No</td>
				<td>Isu</td>
				<td>No. RAF</td>
				<td>Penarafan Risiko</td>
				<td>Skor/100</td>
				<td>Jumlah Skor</td>
				<td>Jumlah Skor<br>/Jumlah RAF</td>
				<td>Star Rating</td>
			</tr>
		</thead><?php
				$no_issue = 1;
				$tot_score_audit = 0;
				$score_audit = 0;
			 foreach($issue_arr AS $issue_id=>$issue_v){

				 ?>
				  <tr>
						<td><?php echo $no_issue; ?></td>
				 		<td><?php echo $issue_v['issue_name']; ?> </td>
						<td>


							<table border =0 style="margin-top: -2px;"><?php
								if($i->getItemCount($issue_id) > 0) {
									$raf_per_issue = 1;
									foreach($i->getItems($issue_id) AS $k=>$v) { ?>
											<tr >
												<td style="padding-bottom:33px"><?php
												  if($raf_per_issue == 1){?>
														<a href="javascript:newRAF('<?php echo $issue_id; ?>', 'new');"> <i class="fa fa-plus-circle fa-lg"></i></a><?php
													}else{

													}?>
												</td>
												<td style="padding-bottom:33px">
													<a href="javascript:newRAF('<?php echo $issue_id; ?>', '<?php echo $v->number_o; ?>');">RAF<?php echo $v->number_o; ?></a>
													<?php if($is_disable2== false){?>
															<button type="button" value="X" class="btn btn-danger btn-xs" onclick="javascript:deleteRAF('<?php echo $issue_id ?>', '<?php echo $v->number_o; ?>')"><i class="fa fa-trash-o"></i>x</button><?php
													}?>
												</td>
											</tr><?php
											$raf_per_issue++;
									 }
								}else{?>
									<tr>
									<td><a href="javascript:newRAF('<?php echo $issue_id; ?>', 'new');"> <i class="fa fa-plus-circle fa-lg"></i></a></td>
									</tr><?php
								}?>
							</table>



						</td>
						<td>
								<table border =0 style="margin-top: -2px;"><?php
									if($i->getItemCount($issue_id) > 0) {
										$raf_per_issue = 1;
										foreach($i->getItems($issue_id) AS $k=>$v) {
												$ikey = $issue_id."|".$v->number_o;
												$_POST['raf_risk_rating'][$ikey] = (empty($_POST['raf_risk_rating'][$ikey])) ? $v->raf_risk_rating : $_POST['raf_risk_rating'][$ikey];?>
												<tr>
													<td style="padding-bottom:20px">
														<select name="raf_risk_rating[<?php echo $ikey; ?>]" <?php if($is_disable2== false){?> onChange="javascript:Refresh('<?php echo $ikey; ?>');" <?php }?>>
														<option value="" <?php if ($_POST['raf_risk_rating'][$ikey] == "") echo "selected"; ?>>-Select-</option><?php
														foreach($rating_arr AS $rating_id=>$rating_v){?>

															<option value="<?php echo $rating_id; ?>" <?php if ($_POST['raf_risk_rating'][$ikey] !=""){if ($_POST['raf_risk_rating'][$ikey] == $rating_id){ echo "selected";  }}?> > <?php echo $rating_v['risk_rating']; ?><?php
														}?>
														</select>
													</td>
												</tr><?php

										}

									}?>
								</table>
						</td>
						<td><?php
						         $tot_score_issue = 0;
								 $avg_score_issue = 0;
							 		if($i->getItemCount($issue_id) > 0) {
										if($issue_id == 1){
											$score_start = 0;
											$score_end = 20;
										}elseif($issue_id == 2){
											$score_start = 10;
											$score_end = 30;
										}elseif($issue_id == 3){
											$score_start = 20;
											$score_end = 40;
										}else{
											$score_start = 50;
											$score_end = 100;
										}

										$cnt_raf = 0;
										foreach($i->getItems($issue_id) AS $k=>$v) {
											$ikey = $issue_id."|".$v->number_o;

										  $_POST['raf_score'][$ikey] = (empty($_POST['raf_score'][$ikey])) ? $v->raf_score : $_POST['raf_score'][$ikey];
											?>

											<select name="raf_score[<?php echo $ikey; ?>]" <?php if($is_disable2== false){?> onChange="javascript:Refresh('<?php echo $ikey; ?>');" <?php }?>>
											<option value="" <?php if ($_POST['raf_score'][$ikey] == "") echo "selected"; ?>>-Select-</option><?php
											for($m = $score_start;$m<=$score_end; $m++){?>

												<option value="<?php echo $m; ?>" <?php if ($_POST['raf_score'][$ikey] !=""){if ($_POST['raf_score'][$ikey] == $m){ echo "selected";  }}?> > <?php echo $m; ?><?php
											}?>
											</select><br><br>
											<?php
											$cnt_raf++;
											$tot_score_issue += $v->raf_score;
										}

										$avg_score_issue= number_format($tot_score_issue/$cnt_raf,2, '.', '');

									}else{
											echo "N/A";
											$tot_score_issue = "N/A";
											$avg_score_issue = 100;

									}
									$tot_score_audit += $avg_score_issue;

									?>

						</td>
						<td><?php
							echo $tot_score_issue;?>
						</td>
						<td><?php echo $avg_score_issue; ?></td>
						<td><?php
								foreach($rating_arr AS $rating_id=>$rating_v){
									if(($avg_score_issue >= $rating_v['score_min']) && ($avg_score_issue <=$rating_v['score_max'])){
										//echo $rating_v['score_min'].' score = '.$avg_score_issue.' max = '.$rating_v['score_max'].' star = '. $rating_v['total_star'].'<br>';
										// echo $rating_v['total_star'];
										echo '<center>';
										for($rc=1;$rc<=$rating_v['total_star'];$rc++){?>
											<img src="images/star_<?php echo $rating_v['star_name']; ?>.png" width=16 height=16>&nbsp;<?php
										}
										echo '</center>';
									}
								}
						?></td>
			</tr>
			<!-- <tr>
				<td colspan=2 align=left><button type="submit" name="del_review" value="Delete Selected" class="btn btn-danger btn-xs" <?php if($_POST['status'] >0){ echo "disabled"; }?>><i class="fa fa-trash-o"></i>Delete Selected</td>
			</tr> -->
			<?php
			$no_issue++;
		}


		$_POST['score_audit'] = number_format($tot_score_audit / ($no_issue-1),2, '.', '');?>
		<tr>
		<td colspan="6"><b>Jumlah Keseluruhan <?php //echo $tot_score_audit.' '.($no_issue-1).'<br>'; ?></b></td>
		<td><input type="hidden" name="audit_score" value="<?php echo $_POST['score_audit']; ?>"><b><?php echo $_POST['score_audit'] ; ?></b></td>
		<td align=center><?php
				foreach($rating_arr AS $rating_id=>$rating_v){

					if((intval($_POST['score_audit']) >= $rating_v['score_min']) && (intval($_POST['score_audit']) <=$rating_v['score_max'])){
						//echo $rating_v['score_min'].' score = '.$avg_score_issue.' max = '.$rating_v['score_max'].' star = '. $rating_v['total_star'].'<br>';
						//echo $rating_v['total_star'];

						// echo $rating_v['star_name'];
						for($rc=1;$rc<=$rating_v['total_star'];$rc++){?>
							<img src="images/star_<?php echo $rating_v['star_name']; ?>.png" width=16 height=16>&nbsp;<?php
						}
					}
				}
		?>
		</td>
		</tr>
		</table>

</td>
</tr>
<tr>
<td colspan="4"><?php
		if($_POST['approver_id'] == $trxBy && $position == 5){?>
			<div class="sign">

					<label class="" for="">Signature by <i><?php echo $_POST['approver_id'];?> </i>:</label>

					<br/><?php
					if(empty($_POST['signature'])){?>

								<div id="sig" ></div>

								<br/><br/>

								<button id="clear" class="btn btn-primary btn-xs">Clear Signature</button>


								<!-- <textarea id="signature64" name="signature" style="display: none"></textarea> -->
								<input type="hidden" id="signature64" name="signature" value="<?php echo $_POST['signature']; ?>"><?php
					}else{?>
						<img src="data:image/jpeg;base64,<?php echo base64_encode($_POST['signature'] );?>"/><?php
					}?>

			</div><?php
		}?>
</td>
</tr>
	<tr>
		<td colspan="4" align=center><?php
		 if($i->getItemCountAll() > 0) {?>
				<button type="button" value="Save" onclick="confirm_save()" class="btn btn-primary btn-xs" <?php if($is_disable2){ echo "disabled"; }?>><i class="fa fa-save "></i><?php echo (empty($_GET['audit_id'])) ? "Save" : "Update"; ?></button>
				<button type="button" onclick="confirm_delete()" class="btn btn-danger btn-xs" <?php if($is_disable2){ echo "disabled"; }?>><i class="fa fa-trash-o"></i>Delete</button><?php
						if(!empty($_GET['audit_id'])){

							if($_POST['approver_id'] == $trxBy && $position == 5){?>
									<button type="button" onclick="confirm_submit()" class="btn btn-primary btn-xs" <?php if($is_disable2){ echo "disabled"; }?>><i class="fa fa-cloud-upload"></i>Submit Audit</button><?php
							}
							if($_POST['status'] >=1){?>
									<button type="button" name="btnPrint" value="Print" onClick=javascript:print('<?php echo $_GET['audit_id']; ?>'); class="btn btn-info btn-xs"> <i class="fa fa-print "></i> Print PDF</button><?php
							}
				  }
			}?>
			<button type="button" name="btnback" value="Back" onclick="javascript:window.location='audit_list.php'" class="btn btn-primary btn-xs"><i class="fa fa-backward"></i> Exit</button>
			<input type="submit" name="save" id="save_btn" style="visibility: hidden;">
			<input type="submit" name="submit_audit" id="submit_btn" style="visibility: hidden;">
			<input type="submit" name="delete_audit" id="delete_btn" style="visibility: hidden;">
			<input type="submit" name="delete_raf" id="delete_raf_btn" style="visibility: hidden;">
		</td>
	</tr>
</table>
<?php } ?>
</div>
</div>
<br>
</form>
</div>
</div>
</div>
<?php include('footer.php'); ?>
</div>
<script type="text/javascript">

    var sig = $('#sig').signature({syncField: '#signature64', syncFormat: 'PNG'});

    $('#clear').click(function(e) {

        e.preventDefault();

        sig.signature('clear');

        $("#signature64").val('');

    });

</script>
<script language="javascript">

function deleteRAF(issue_id, raf_no){

	swal({   title: "Are you sure to Delete RAF"+raf_no+"?",
    // text: "Are you sure to submit Audit Form?",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#DD6B55",
    confirmButtonText: "Yes, delete my Audit Form!",
    cancelButtonText: "No, I am not sure!",
    closeOnConfirm: false,
    closeOnCancel: false },
    function(isConfirm){
        if (isConfirm) {
			//swal("Account Removed!", "Your account is removed permanently!", "success");
			document.frm.tmp_issue_id.value= issue_id;
			document.frm.tmp_raf_no.value= raf_no;
			document.getElementById("delete_raf_btn").click();
        } else {
            swal("Failed", "RAF"+raf_no+" is not Deleted!", "error");
			//return false;
         } });

}

function scope_change(value){
	if(value=="OTHER"){
		document.frm.other_scope.disabled = false;
	}else{
		document.frm.other_scope.disabled = true;
	}
}

function newRAF(issue_id,raf_no){

	var review_list = document.frm.review_list.value;
	var status = document.frm.status.value;
	url = 'audit_add_raf.php?issue_id=' + issue_id + '&review_list=' + review_list + '&raf_no=' + raf_no + '&status=' + status;

	theHeight = 300;
	theWidth = 600;
	if (theHeight > screen.availHeight) {
		theHeight = screen.availHeight;
	}
	if (theWidth > screen.availWidth) {
		theWidth = screen.availWidth;
	}
	myWin = window.open(url, "newwindow", "toolbar=no,menubar=yes,scrollbars=YES,resizable=YES,status=no,width=" + theWidth + ",height=" + theHeight + ",ScreenX=0,Left=0,ScreenY=0,Top=0");

	if (myWin.opener == null)
	myWin.opener = top;
}

function confirmCancel(){

    var c = confirm("Confirm Cancel?");
    if (c) {

        return true;
    } else {

        return false;
    }
}

function confirm_save(){

	swal({   title: "Are you sure to save?",
    // text: "Are you sure to save?",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#DD6B55",
    confirmButtonText: "Yes, save my Audit Form!",
    cancelButtonText: "No, I am not sure!",
    closeOnConfirm: false,
    closeOnCancel: false },
    function(isConfirm){
        if (isConfirm) {
			//swal("Account Removed!", "Your account is removed permanently!", "success");
			document.getElementById("save_btn").click();
        } else {
            swal("Failed", "Audit Form is not save!", "error");
			//return false;
         } });

}

function confirm_submit(){

	swal({   title: "Are you sure to submit Audit Form?",
    // text: "Are you sure to submit Audit Form?",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#DD6B55",
    confirmButtonText: "Yes, submit my Audit Form!",
    cancelButtonText: "No, I am not sure!",
    closeOnConfirm: false,
    closeOnCancel: false },
    function(isConfirm){
        if (isConfirm) {
			//swal("Account Removed!", "Your account is removed permanently!", "success");
			document.getElementById("submit_btn").click();
        } else {
            swal("Failed", "Audit Form is not submitted!", "error");
			//return false;
         } });

}

function confirm_delete(){

	swal({   title: "Are you sure to Delete Audit Form?",
    // text: "Are you sure to submit Audit Form?",
    type: "warning",
    showCancelButton: true,
    confirmButtonColor: "#DD6B55",
    confirmButtonText: "Yes, delete my Audit Form!",
    cancelButtonText: "No, I am not sure!",
    closeOnConfirm: false,
    closeOnCancel: false },
    function(isConfirm){
        if (isConfirm) {
			//swal("Account Removed!", "Your account is removed permanently!", "success");
			document.getElementById("delete_btn").click();
        } else {
            swal("Failed", "Audit Form is not Deleted!", "error");
			//return false;
         } });

}

function Refresh(){
   document.frm.submit();
}

function AddReview(){
   document.getElementById("add_review").click();
}

function print(audit_id){
  url = "audit_pdf.php?audit_id=" + audit_id;
	window.open(url);
}



// function changeScore(ikey){
// 		var a = (document.frm["raf_score["+ikey+"]"].value*1);
// }

</script>
</body>

</html>
