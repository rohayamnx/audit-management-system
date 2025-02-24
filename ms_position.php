<?php
require_once("global.php");
error_reporting(E_ERROR | E_PARSE);
$conn = $_SESSION['conn'];
session_start();

$module_name = 'MASTER SETUP';
$script_name = 'USER POSITION';

doValidateUserAccess($script_name,$module_name);

$rvsby = getUserId();
$rvsdate = getCurrentTimeStamp();

$audit = new AuditModel();

if (empty($_POST)) {
    $_SESSION['show_update'] = false;

}

if(isset($_POST['save'])){
	$proceed = true;

  $is_exist = $audit->checkExist($_POST);

	if ($_POST['position_name']=="") {
		$errMsg = "Position Name can not be empty";
		$proceed = false;
	}elseif ($_POST['position_id']=="") {
		$errMsg = "Position ID can not be empty";
		$proceed = false;
	}elseif ($is_exist) {
		$errMsg = "Position ID already exist";
		$proceed = false;
	}

	if ($proceed) {

		$res = $audit->insertProductType($_POST, $rvsby, $rvsdate);
		if($res){
			$errMsg = "New Data Added Sucessfully..!";
			$url="ms_position.php";
			header('Location:'. $url);
		}else{
			$errMsg = "Data Fail to Add..!";
		}

	}
}elseif(isset($_POST["update"])){

	$proceed = true;

  $is_exist = $audit->checkExist($_POST);

	if ($_POST['position_name']=="") {
		$errMsg = "Position Name can not be empty";
		$proceed = false;
	}elseif ($_POST['position_id']=="") {
		$errMsg = "Position ID can not be empty";
		$proceed = false;
	}elseif ($is_exist) {
		$errMsg = "Position ID already exist";
		$proceed = false;
	}

	if ($proceed) {
		$update = $audit->updateProductType($_POST, $rvsby, $rvsdate);

		if($update){

			$errMsg = "Data Updated Sucessfully..!";
			$_POST = '';
			$_SESSION['show_update'] = false;

		}
		$url="ms_position.php";
		header('Location:'. $url);
	}
}else if (isset($_POST["delete"])) {

	if (!empty($_POST['ck'])){
        foreach($_POST['ck'] AS $id) {

            //delete account
            $audit->deleteProductType($id, $rvsby, $rvsdate);

            $_POST = '';
        }

        $errMsg = "Record deleted!";

    } else {
        $errMsg = "Please choose at least one record!";

    }

	$url="ms_position.php";
    header('Location:'. $url);

}elseif (isset($_POST['edit'])){

    if (empty($_POST['ck'])){
        $errMsg = "Please choose one record!";

    } else {
        $key = $_POST['ck'];
        $_POST = $audit->getProductType($key[0]);

        //show update button
        $_SESSION['show_update'] = true;

		if (isset($_GET['Page'])) $Page = '';
    }

}


//show account list
if (!isset($_POST['search']) && !isset($_GET['search'])) {

    if (isset($_GET['Page'])); $Page = '';
    $query = $audit->getProductTypeList();

}


?>

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
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
<form name="frm" id="frm" method="post" action=""  >
<input type="hidden" name="id" size="10" maxlength="10" value="<?php echo $_POST['id'];?>">
<div class="panel panel-default">
<div class="panel-body">
<br>
<table width="100%" class="frm">

	<tr>
    <td>Position ID</td>
		<td>
      <select name="position_id"><?php
              $arr_number = array(0,1,2,3,4,5,6,7,8,9,10);
              if (!empty($arr_number)) {
                foreach ($arr_number AS $v_no) {?>
                  <option value="<?php echo $v_no; ?>" <?php echo ($_POST['position_id'] == $v_no) ? "selected" : ""?>><?php echo $v_no;?></option><?php
                }
              }?>
      </select>
    </td>
		<td>Position Name</td>
		<td><input type="text" name="position_name" size="50" maxlength="50" value="<?php echo $_POST['position_name'];?>" onkeyup="javascript:cUpper(this);"></td>
		<td><center><i class="fa fa-info-circle "></i>
			Position Name use at new User
		</td>
	</tr>
	<tr>
		<td>
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
<div class="table-responsive">
	 <table class="table table-striped table-bordered table-hover" id="dataTables-example">
		<thead>
		<tr>
			<td></td>
			<td>No.</td>
      <td>Position ID</td>
			<td>Position Name</td>
			<td>Created By</td>
			<td>Created Date</td>
			<td>Updated By</td>
			<td>Updated Date</td>
		</tr>
		</thead>
		<tbody>
			<?php
				$cnt = 1;
				while($row = $query->fetch_array(MYSQLI_ASSOC)){
			?>
					<tr>
					<td><input type="checkbox" name="ck[]" value="<?php echo $row['id']; ?>"></td>
					<td><?php echo $cnt; ?></td>
          <td><?php echo $row['position_id'];?></td>
					<td><?php echo $row['position_name'];?></td>
					<td><?php echo $row['crd_by']; ?></td>
					<td><?php echo $row['crd_dd']; ?></td>
					<td><?php echo $row['upd_by']; ?></td>
					<td><?php echo $row['upd_dd']; ?></td>
				</tr>
			<?php $cnt++;

				} ?>
		</tbody>
	  </table>

</div>
<br>
<?php if (!empty($query)) { ?>
<button type="submit" name="edit" value="Edit" class="btn btn-primary btn-xs"><i class="fa fa-edit "></i> Edit</button>
<button type="submit" name="delete" value="Delete" onclick="return confirmDelete();" class="btn btn-danger btn-xs"><i class="fa fa-pencil"></i> Delete</button><?php } ?>

</div>

</div>


</form>
</div>
</div>
</div>
<?php include('footer.php'); ?>
</div>
<?php
class AuditModel {

  var $conn;

    function AuditModel() {
        $this->conn = $_SESSION['conn'];
    }

    function checkExist($frm)
    {
  		  $res = $this->conn->query("SELECT * FROM ms_position WHERE position_id = '$frm[position_id]' AND position_id != '' AND id !='$frm[id]'") or die($this->conn->error);
        if($res->num_rows > 0){
            return true;
        }else{
          return false;
        }
    }

	function getProductTypeList()
    {
		    $res = $this->conn->query("SELECT * FROM ms_position ORDER BY position_id") or die($this->conn->error);
        return $res;
    }

    function insertProductType($frm,$rvsby,$rvsdate)
    {
       $res = $this->conn->query("INSERT INTO ms_position (position_id, position_name, crd_by, crd_dd)
											   VALUES ('$frm[position_id]', '".addslashes($frm['position_name'])."', '$rvsby','$rvsdate')") or die($this->conn->error);
       if($res){
           return true;
       }else{
           return false;
        }
    }

	function getProductType($id)
    {
        $res = $this->conn->query("SELECT * FROM ms_position WHERE id = '$id' LIMIT 1") or die($this->conn->error);

        if ($res->num_rows > 0) {
            return $res->fetch_array(MYSQLI_ASSOC);

        } else {
            return array();

        }
    }


    function updateProductType($frm,$rvsby,$rvsdate)
    {

		$res = $this->conn->query("UPDATE ms_position set
                              position_id ='".addslashes($frm['position_id'])."'
                              ,position_name='".addslashes($frm['position_name'])."'
      											 ,upd_by = '$rvsby'
      											 ,upd_dd = '$rvsdate'
      								WHERE id ='$frm[id]'") or die($this->conn->error);


         return true;

    }

	function deleteProductType($id, $rvsby, $rvsdate)
    {
        $res = $this->conn->query("DELETE FROM ms_position WHERE position_id = '$id'") or die($this->conn->error);
        return true;

    }


}


?>
<script language="javascript">

function confirmCancel(){

    var c = confirm("Confirm Cancel?");
    if (c) {

        return true;
    } else {

        return false;
    }


}

</script>
</body>

</html>
