<?php
require_once("global.php");
error_reporting(E_ERROR | E_PARSE);
$conn = $_SESSION['conn'];
session_start();

$module_name = 'MASTER SETUP';
$script_name = 'ACCESS LEVEL';

doValidateUserAccess($script_name,$module_name);

$rvsby = getUserId();
$rvsdate = getCurrentTimeStamp();


$access = new AccessMaster();

if (empty($_POST)) {
    $_SESSION['show_update'] = false;
}

if(isset($_POST["save"])){
	$proceed = true;

	$is_exist = $access->checkAccess($_POST['access_level']);

	if($_POST['access_level'] == "") {
		$errMsg = "Access Level can not be empty";
		$proceed = false;
	}else if($is_exist) {
		$errMsg = "Access Level already Exist! ";
		$proceed = false;
	}elseif($_POST['access_desc'] == "") {
		$errMsg = "Access Description can not be empty";
		$proceed = false;
	}

	if ($proceed) {

		$res = $access->insertAccess($_POST, $rvsby, $rvsdate);
		if($res){
			$errMsg = "New Data Added Sucessfully..!";
		}else{
			$errMsg = "Data Fail to Add..!";
		}

		$url="ms_access.php";
		header('Location:'. $url);
	}
}elseif(isset($_POST["update"])){

	$proceed = true;

	if ($_POST['access_desc'] == "") {
		$errMsg = "Access Description can not be empty";
		$proceed = false;
	}

	if ($proceed) {
		$res = $access->updateAccess($_POST, $rvsby, $rvsdate);

		if($res){
			$errMsg = "Data Updated Sucessfully..!";
			$_POST = '';
			$_SESSION['show_update'] = false;
		}

		$url="ms_access.php";
		header('Location:'. $url);
	}
}elseif (isset($_POST['edit_item'])){

    if (!empty($_POST['access_code'])){

        $_POST = $access->getAccess($_POST['access_code']);

        //show update button
        $_SESSION['show_update'] = true;

		if (isset($_GET['Page'])) $Page = '';

    }

}elseif(isset($_POST['del_item']))  {

    if (!empty($_POST['access_code'])){
        $access->deleteAccess($_POST['access_code']);

    }

}

//show account list
if (!isset($_POST['search']) && !isset($_GET['search'])) {

    if (isset($_GET['Page'])); $Page = '';
    $query = $access->getAccessList();

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
			<a href="#" class="alert-link">Alert</a> : <?php echo $errMsg;?>
		</div><?php
}?>
<h2 align="center"><?php echo $module_name. " - ". $script_name ?></h2>
<form name="frm" id="frm" method="post" action="">
<input type="hidden" name="access_code" id="access_code" value="<?php echo $_POST['access_code']; ?>">
<div class="panel panel-default">
<div class="panel-body">

<table width="80%" class="frm">
<tr>
	<td>Access Level :</td>
	<td><input type="text" name="access_level" size="5" maxlength="5" value="<?php echo $_POST['access_level'];?>" <?php if($_SESSION['show_update']) echo "readonly"; ?> ></td>
	<td>Access Description :</td>
	<td><input type="text" name="access_desc" size="50" maxlength="40" value="<?php echo $_POST['access_desc'];?>"></td>
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
	<td width="10%">Access Level</td>
	<td width="15%">Access Description</td>
	<td width="45%">List User ID</td>
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
		<td><a href="ms_access_detail.php?access_level=<?php echo $row["access_level"]; ?>"><?php echo $row["access_level"];?></a></td>
		<td><?php echo $row['access_desc']; ?></td>
		<td><?php
			$user_res = $conn->query("SELECT user_id FROM ms_user WHERE access_level = '$row[access_level]' ORDER BY user_id") or die($conn->error);
			if ($user_res->num_rows > 0) {
				$no = 1;
				while ($user = $user_res->fetch_array(MYSQLI_ASSOC)) {
					if ($no != $user_res->num_rows) {
						//echo "<a href=\"user_maintain.php?uid=$user[user_id]\">" . $user['user_id'] . "</a>, ";
						echo $user['user_id'].",  ";
					} else {
						//echo "<a href=\"user_maintain.php?uid=$user[user_id]\">" . $user['user_id'] . "</a>";
						echo $user['user_id'];
					}
					$no++;
				}
			}
		?>
		</td>
		<td align="center">
		<button type="button" name="edit" value="Edit" onclick="javascript:editItems('<?php echo $row["access_level"]; ?>');" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i> Edit</button>&nbsp;&nbsp;
		<button type="button" name="delete" value="Delete" onclick="javascript:deleteItems('<?php echo $row["access_level"]; ?>');" class="btn btn-danger btn-xs"><i class="fa fa-delete"></i> Delete</button>
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

</div>
</div>
</div>
<?php include('footer.php'); ?>
</div>
<?php

class AccessMaster {

  var $conn;

    function AccessMaster() {
        $this->conn = $_SESSION['conn'];
    }

	function checkAccess($access_level)
    {
	   $res = $this->conn->query("SELECT * FROM ms_halevel WHERE access_level = '$access_level' AND access_level !=''") or die($this->conn->error);
       if(mysql_num_rows($res) > 0){
           return true;
       }else{
           return false;
       }
    }

	function insertAccess($frm, $rvsby, $rvsdate){

		$res = $this->conn->query("INSERT INTO ms_halevel VALUES('$frm[access_level]','".addslashes(trim($frm['access_desc']))."', '1','$rvsby','$rvsdate')") or die($this->conn->error);

		if($res){
           return true;
		}else{
           return false;
		}

	}

	function updateAccess($frm, $rvsby, $rvsdate){

		$res = $this->conn->query("UPDATE ms_halevel SET access_desc = '".addslashes(trim($frm['access_desc']))."'
										, rvsby = '$rvsby'
										, rvsdate = '$rvsdate'
							WHERE access_level = '$frm[access_level]' AND access_level!=''") or die($this->conn->error);

		if($res){
           return true;
		}else{
           return false;
		}

	}

	function deleteAccess($access_level)
    {
        $res = $this->conn->query("DELETE FROM ms_halevel WHERE access_level = '$access_level' AND access_level!=''") or die($this->conn->error);

        return true;

    }

	function getAccess($access_level)
    {
        $res = $this->conn->query("SELECT * FROM ms_halevel WHERE access_level = '$access_level' LIMIT 1") or die($this->conn->error);

        if ($res->num_rows > 0) {
            return $res->fetch_array(MYSQLI_ASSOC);

        } else {
            return array();

        }
    }

	function getAccessList()
    {
		$res = $this->conn->query("SELECT * FROM ms_halevel WHERE access_level != ''") or die($this->conn->error);
        return $res;

    }


}

?>
</body>
<script language="javascript">
function editItems(id){

	document.frm.access_code.value= id;
	document.getElementById("edit_btn").click();

}

function deleteItems(id){
	var c = confirm("Are you sure want to delete Access level " + id + "?");
    if (c) {
        document.frm.access_code.value= id;
		document.getElementById("delete_btn").click();
    } else {
        return false;
    }

}


</script>
</html>
