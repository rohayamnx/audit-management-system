<?php
require_once("global.php");
error_reporting(E_ERROR | E_PARSE);
$conn = $_SESSION['conn'];
session_start();

$module_name = 'MASTER SETUP';
$script_name = 'MODULE';

doValidateUserAccess($script_name,$module_name);

$rvsby = getUserId();
$rvsdate = getCurrentTimeStamp();


$module_d = new ModuleMasterD();

if (empty($_POST)) {
    $_SESSION['show_update'] = false;
}

if (!empty($_GET['master_id'])) {
    $arr = $module_d->getModuleDesc($_GET['master_id']);
}

if(isset($_POST["save"])){
	$proceed = true;

	if($_POST['module_d_desc'] == "") {
		$errMsg = "Child Module can not be empty";
		$proceed = false;
	}

	if ($proceed) {

		$res = $module_d->insertModuleD($_POST, $_GET['master_id'], $rvsby, $rvsdate);
		if($res){
			$errMsg = "New Data Added Sucessfully..!";
		}else{
			$errMsg = "Data Fail to Add..!";
		}
		$url="ms_module_detail.php?master_id=$_GET[master_id]";
		header('Location:'. $url);
	}
}elseif(isset($_POST["update"])){

	$proceed = true;

	if ($_POST['module_d_desc'] == "") {
		$errMsg = "Child Module can not be empty";
		$proceed = false;
	}

	if ($proceed) {
		$res = $module_d->updateModuleD($_POST, $_GET['master_id'], $rvsby, $rvsdate);

		if($res){
			$errMsg = "Data Updated Sucessfully..!";
			$_POST = '';
			$_SESSION['show_update'] = false;
		}

		$url="ms_module_detail.php?master_id=$_GET[master_id]";
		header('Location:'. $url);
	}
}elseif (isset($_POST['edit_item'])){

    if (!empty($_POST['module_d_code'])){

        $_POST = $module_d->getModuleD($_POST['module_d_code']);

        //show update button
        $_SESSION['show_update'] = true;

		if (isset($_GET['Page'])) $Page = '';

    }

}elseif(isset($_POST['del_item']))  {

    if (!empty($_POST['module_d_code'])){
        $module_d->deleteModuleD($_POST['module_d_code'], $_GET['master_id']);

    }

}

//show account list
if (!isset($_POST['search']) && !isset($_GET['search'])) {

    if (isset($_GET['Page'])); $Page = '';
    $query = $module_d->getModuleListD($_GET['master_id']);

}

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
<?php
if($errMsg !=""){

		echo '<script>MsgStatus("'.$errMsg.'");</script>';	?>
		<div class="alert alert-danger alert-dismissable">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
			<a href="#" class="alert-link">Alert</a>:<?php echo $errMsg;?>
		</div><?php
}?>

<h2 align="center"><?php echo $module_name. " - ". $script_name ?></h2>
<form name="frm" id="frm" method="post" action="">
<input type="hidden" name="module_d_code" id="module_d_code" value="<?php echo $_POST['module_d_code']; ?>">
<input type="submit" name="edit_item" id="edit_btn" value="Edit" style="visibility: hidden;"/>
<input type="submit" name="del_item" id="delete_btn" value="Delete" style="visibility: hidden;"/>
<div class="panel panel-default">
<div class="panel-body">

<table width="100%" class="frm">
<tr>
	<td width="11%">Master Module :</td>
	<td>
	<input type="text" name="module_desc" size="50" maxlength="100" value="<?php echo $arr['module_desc'];?>" readonly>
	<input type="hidden" name="module_id" value="<?php echo $_POST['module_id'];?>">
	</td>
	<td width="11%">Child Module :</td>
	<td><input type="text" name="module_d_desc" size="50" maxlength="100" value="<?php echo $_POST['module_d_desc'];?>" onkeyup="javascript:cUpper(this);"></td>
	<td><input type="hidden" name="module_d_id" value="<?php echo $_POST['module_d_id'];?>"></td>

	<td height="23" colspan="3">
	<button type="submit" name="<?php echo (!$_SESSION['show_update']) ? "save" : "update" ;?>" value="<?php echo (!$_SESSION['show_update']) ? "Save" : "Update" ;?>" class="btn btn-primary btn-xs"><i class="fa fa-edit "></i><?php echo (!$_SESSION['show_update']) ? "Save" : "Update" ;?></button>
	<button type="button" name="btnback" value="Back" onclick="javascript:window.location='ms_module.php'" class="btn btn-primary btn-xs"><i class="fa fa-backward"></i> Back</button>

	</td>
</tr>
</table>
</div>
</div>
<br>

<div class="panel panel-default">
<div class="panel-heading">Search Result</div>
<div class="panel-body">
<table class="table table-striped table-bordered table-hover" id="dataTables-example">
<thead>
<tr>
	<td>No.</td>
	<td>Child ID</td>
	<td>Child Description</td>
	<td>Rvsby</td>
	<td>Rvsdate</td>
	<td></td>
</tr>
</thead>
<tbody>
<?php
	$cnt = 1;
	while($row = $query->fetch_array(MYSQLI_ASSOC)){
?>
		<tr>
		<td><?php echo $cnt; ?></td>
		<td><?php echo $row["mdetail_id"];?></td>
		<td><?php echo $row['mdetail_desc']; ?></td>
		<td><?php echo $row['rvsby']; ?></td>
		<td><?php echo $row['rvsdate']; ?></td>
		<td align="center">
		<button type="button" name="edit" value="Edit" onclick="javascript:editItems('<?php echo $row["mdetail_id"]; ?>');" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i> Edit</button>&nbsp;&nbsp;
		<button type="button" name="delete" value="Delete" onclick="javascript:deleteItems('<?php echo $row["mdetail_id"]; ?>');" class="btn btn-danger btn-xs"><i class="fa fa-delete"></i> Delete</button>
		</td>
	</tr>
<?php $cnt++;

	} ?>
</tbody>
</table>
</div>
</div>
</div>

</form>


<br>


</form>
</div>
</div>
</div>
<?php include('footer.php'); ?>
</div>
<?php

class ModuleMasterD {

  var $conn;

    function ModuleMasterD() {
        $this->conn = $_SESSION['conn'];
    }

	function insertModuleD($frm, $module_id, $rvsby, $rvsdate){

		$res = $this->conn->query("INSERT INTO ms_dmodule (mdetail_mstid, mdetail_desc, moduleurl, act_flag, rvsby, rvsdate) VALUES('$module_id', '".addslashes($frm['module_d_desc'])."', '', '1','$rvsby','$rvsdate')") or die($this->conn->error);

		if($res){
           return true;
		}else{
           return false;
		}

	}

	function updateModuleD($frm, $module_id, $rvsby, $rvsdate){

		$res = $this->conn->query("UPDATE ms_dmodule SET mdetail_desc = '".addslashes($frm['module_d_desc'])."', rvsby = '$rvsby', rvsdate = '$rvsdate'
								   WHERE mdetail_id = '$frm[module_d_id]' AND mdetail_mstid = '$module_id' AND mdetail_id !=''") or die($this->conn->error);

		if($res){
           return true;
		}else{
           return false;
		}

	}

	function deleteModuleD($module_d_id, $module_id)
    {
        $res = $this->conn->query("DELETE FROM ms_dmodule WHERE mdetail_id = '$module_d_id' AND mdetail_mstid = '$module_id' AND mdetail_id!=''") or die($this->conn->error);

        return true;

    }

	function getModuleD($module_d_id)
    {
        $res = $this->conn->query("SELECT mdetail_id AS module_d_id, mdetail_desc AS module_d_desc FROM ms_dmodule WHERE mdetail_id = '$module_d_id' LIMIT 1") or die($this->conn->error);

        if ($res->num_rows > 0) {
            return $res->fetch_array(MYSQLI_ASSOC);

        } else {
            return array();

        }
    }

	function getModuleListD($module_id)
    {
		$res = $this->conn->query("SELECT * FROM ms_dmodule WHERE mdetail_mstid = '$module_id' AND mdetail_mstid != ''") or die($this->conn->error);

        return $res;

    }

	function getModuleDesc($module_id)
    {
	   $res = $this->conn->query("SELECT module_desc FROM ms_module WHERE module_id = '$module_id' AND module_id !=''") or die($this->conn->error);
       if ($res->num_rows > 0) {
           return $res->fetch_array(MYSQLI_ASSOC);

        } else {
            return array();

        }
    }


}

?>
</body>
<script language="javascript">
function editItems(id){

	document.frm.module_d_code.value= id;
	document.getElementById("edit_btn").click();

}

function deleteItems(id){
	var c = confirm("Are you sure want to delete module id " + id + "?");
    if (c) {
        document.frm.module_d_code.value= id;
		document.getElementById("delete_btn").click();
    } else {
        return false;
    }

}


</script>
</html>
