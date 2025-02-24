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


$module = new ModuleMaster();

if (empty($_POST)) {
    $_SESSION['show_update'] = false;
}

if(isset($_POST["save"])){
	$proceed = true;

	if($_POST['module_desc'] == "") {
		$errMsg = "Module Description can not be empty";
		$proceed = false;
	}

	if ($proceed) {

		$res = $module->insertModule($_POST,$rvsby,$rvsdate);
		if($res){
			$errMsg = "New Data Added Sucessfully..!";
		}else{
			$errMsg = "Data Fail to Add..!";
		}
		$url="ms_module.php";
		header('Location:'. $url);
	}
}elseif(isset($_POST["update"])){

	$proceed = true;

	if ($_POST['module_desc'] == "") {
		$errMsg = "Module Description can not be empty";
		$proceed = false;
	}

	if ($proceed) {
		$res = $module->updateModule($_POST,$rvsby,$rvsdate);

		if($res){
			$errMsg = "Data Updated Sucessfully..!";
			$_POST = '';
			$_SESSION['show_update'] = false;
		}

		$url="ms_module.php";
		header('Location:'. $url);
	}
}elseif (isset($_POST['edit_item'])){

    if (!empty($_POST['module_code'])){

        $_POST = $module->getModule($_POST['module_code']);

        //show update button
        $_SESSION['show_update'] = true;

		if (isset($_GET['Page'])) $Page = '';
    }

}elseif(isset($_POST['del_item']))  {

    if (!empty($_POST['module_code'])){
        $module->deleteModule($_POST['module_code']);

    }
}

//show account list
if (!isset($_POST['search']) && !isset($_GET['search'])) {

    if (isset($_GET['Page'])); $Page = '';
    $query = $module->getModuleList();

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
<input type="hidden" name="module_code" id="module_code" value="<?php echo $_POST['module_code']; ?>">
<div class="panel panel-default">
<div class="panel-body">

<table width="80%" class="frm">
<tr>
	<td width="11%">Description :</td>
	<td><input type="text" name="module_desc" size="50" maxlength="100" value="<?php echo $_POST['module_desc'];?>" onkeyup="javascript:cUpper(this);"></td>
	<td><input type="hidden" name="module_id" value="<?php echo $_POST['module_id'];?>"></td>

	<td height="23" colspan="3">
	<button type="submit" name="<?php echo (!$_SESSION['show_update']) ? "save" : "update" ;?>" value="<?php echo (!$_SESSION['show_update']) ? "Save" : "Update" ;?>" class="btn btn-primary btn-xs"><i class="fa fa-edit "></i><?php echo (!$_SESSION['show_update']) ? "Save" : "Update" ;?></button>
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
	<td>Module ID</td>
	<td>Module Description</td>
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
		<td><?php echo $row["module_id"];?></td>
		<td><a href="ms_module_detail.php?master_id=<?php echo $row["module_id"]; ?>"><?php echo $row['module_desc']; ?></a></td>
		<td><?php echo $row['rvsby']; ?></td>
		<td><?php echo $row['rvsdate']; ?></td>
		<td align="center">
		<button type="button" name="edit" value="Edit" onclick="javascript:editItems('<?php echo $row["module_id"]; ?>');" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i> Edit</button>&nbsp;&nbsp;
		<button type="button" name="delete" value="Delete" onclick="javascript:deleteItems('<?php echo $row["module_id"]; ?>');" class="btn btn-danger btn-xs"><i class="fa fa-delete"></i> Delete</button>
		<input type="submit" name="edit_item" id="edit_btn" value="Edit" style="visibility: hidden;"/>
		<input type="submit" name="del_item" id="delete_btn" value="Delete" style="visibility: hidden;"/>
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

class ModuleMaster {

  var $conn;

    function ModuleMaster() {
        $this->conn = $_SESSION['conn'];
    }

	function insertModule($frm, $rvsby, $rvsdate){

		$res =  $this->conn->query("INSERT INTO ms_module (module_desc, act_flag, rvsby, rvsdate) VALUES('".addslashes(trim($frm['module_desc']))."', '1','$rvsby','$rvsdate')") or die($this->conn->error);

		if($res){
           return true;
		}else{
           return false;
		}

	}

	function updateModule($frm, $rvsby, $rvsdate){

		$res =  $this->conn->query("UPDATE ms_module SET module_desc = '".addslashes(trim($frm['module_desc']))."'
												, rvsby = '$rvsby'
												, rvsdate = '$rvsdate'
							WHERE module_id = '$frm[module_id]' AND module_id!=''") or die($this->conn->error);

		if($res){
           return true;
		}else{
           return false;
		}

	}

	function deleteModule($module_id)
    {
        $res =  $this->conn->query("DELETE FROM ms_module WHERE module_id = '$module_id' AND module_id!=''") or die($this->conn->error);

        return true;

    }

	function getModule($module_id)
    {
        $res =  $this->conn->query("SELECT * FROM ms_module WHERE module_id = '$module_id' LIMIT 1") or die($this->conn->error);

        if ($res->num_rows > 0) {
            return $res->fetch_array(MYSQLI_ASSOC);

        } else {
            return array();

        }
    }

	function getModuleList()
    {

		$res =  $this->conn->query("SELECT * FROM ms_module WHERE module_id != ''") or die($this->conn->error);

        return $res;

    }


}

?>
</body>
<script language="javascript">
function editItems(id){

	document.frm.module_code.value= id;
	document.getElementById("edit_btn").click();

}

function deleteItems(id){
	var c = confirm("Are you sure want to delete module id " + id + "?");
    if (c) {
        document.frm.module_code.value= id;
		document.getElementById("delete_btn").click();
    } else {
        return false;
    }

}


</script>
</html>
