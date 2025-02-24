<?php
require_once("global.php");
error_reporting(E_ERROR | E_PARSE);
$conn = $_SESSION['conn'];
session_start();

$module_name = 'MASTER SETUP';
$script_name = 'UNIT';

doValidateUserAccess($script_name,$module_name);

$rvsby = getUserId();
$rvsdate = getCurrentTimeStamp();

$audit = new AuditModel();

$dept_arr = $audit->getDepartment();

if (empty($_POST)) {
    $_SESSION['show_update'] = false;

}

if(isset($_POST['save'])){
	$proceed = true;

  if ($_POST['sect_name']=="") {
		$errMsg = "Section Name can not be empty";
		$proceed = false;
	}

	if ($_POST['dept_name']=="") {
		$errMsg = "Department Name can not be empty";
		$proceed = false;
	}

	if ($proceed) {

		$res = $audit->insertSection($_POST, $rvsby, $rvsdate);
		if($res){
			$errMsg = "New Data Added Sucessfully..!";
			$url="ms_department.php";
			header('Location:'. $url);
		}else{
			$errMsg = "Data Fail to Add..!";
		}

	}
}elseif(isset($_POST["update"])){

	$proceed = true;

	if ($_POST['dept_name']=="") {
		$errMsg = "Department Name can not be empty";
		$proceed = false;
	}
  if ($_POST['sect_name']=="") {
		$errMsg = "Section Name can not be empty";
		$proceed = false;
	}

	if ($proceed) {
		$update = $audit->updateSection($_POST, $rvsby, $rvsdate);

		if($update){

			$errMsg = "Data Updated Sucessfully..!";
			$_POST = '';
			$_SESSION['show_update'] = false;

		}
		$url="ms_section.php";
		header('Location:'. $url);
	}
}else if (isset($_POST["delete"])) {

	if (!empty($_POST['ck'])){
        foreach($_POST['ck'] AS $id) {

            //delete account
            $audit->deleteSection($id, $rvsby, $rvsdate);

            $_POST = '';
        }

        $errMsg = "Record deleted!";

    } else {
        $errMsg = "Please choose at least one record!";

    }

	$url="ms_section.php";
    header('Location:'. $url);

}elseif (isset($_POST['edit'])){

    if (empty($_POST['ck'])){
        $errMsg = "Please choose one record!";

    } else {
        $key = $_POST['ck'];
        $_POST = $audit->getSection($key[0]);

        //show update button
        $_SESSION['show_update'] = true;

		if (isset($_GET['Page'])) $Page = '';
    }

}


//show account list
if (!isset($_POST['search']) && !isset($_GET['search'])) {

    if (isset($_GET['Page'])); $Page = '';
    $query = $audit->getSectionList();

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
<input type="hidden" name="sect_id" value="<?php echo $_POST['sect_id'];?>">
<div class="panel panel-default">
<div class="panel-body">
<br>
<table width="100%" class="frm">
  <tr>
    <td>Unit Name</td>
    <td><input type="text" name="sect_name" size="30" maxlength="30" value="<?php echo $_POST['sect_name'];?>" onkeyup="javascript:cUpper(this);"></td>
  </tr>
	<tr>
		<td>Department/Branch Name</td>
    <td>
			<select name="dept_name">
			<option></option>
				<?php if (!empty($dept_arr)) {
					foreach ($dept_arr AS $v) {
				?>
					<option value="<?php echo $v['dept_name']; ?>" <?php echo ($_POST['dept_name'] == $v['dept_name']) ? "selected" : ""?>><?php echo $v['dept_name'];?></option>
				<?php }
				} ?>
			</select>
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
			<td>Unit ID</td>
      <td>Unit Name</td>
      <td>Department/Branch</td>
			<td>Created By</td>
			<td>Created Date</td>
		</tr>
		</thead>
		<tbody>
			<?php
				$cnt = 1;
				while($row = $query->fetch_array(MYSQLI_ASSOC)){
			?>
					<tr>
					<td><input type="checkbox" name="ck[]" value="<?php echo $row['sect_id']; ?>"></td>
					<td><?php echo $cnt; ?></td>
					<td><?php echo $row['sect_id'];?></td>
					<td><?php echo $row['sect_name']; ?></td>
					<td><?php echo $row['dept_name']; ?></td>
					<td><?php echo $row['crt_by']; ?></td>
					<td><?php echo $row['crt_dd']; ?></td>
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

	function getSectionList()
    {
		$res = $this->conn->query("SELECT * FROM ms_section WHERE sect_id != ''") or die($this->conn->error);
        return $res;
    }

    function insertSection($frm,$rvsby,$rvsdate)
    {
       $res = $this->conn->query("INSERT INTO ms_section (sect_name, dept_name, crt_by, crt_dd)
											   VALUES ('".addslashes($frm['sect_name'])."','".addslashes($frm['dept_name'])."', '$rvsby','$rvsdate')") or die($this->conn->error);
       if($res){
           return true;
       }else{
           return false;
        }
    }

	function getSection($id)
    {
        $res = $this->conn->query("SELECT * FROM ms_section WHERE sect_id = '$id' LIMIT 1") or die($this->conn->error);

        if ($res->num_rows > 0) {
            return $res->fetch_array(MYSQLI_ASSOC);

        } else {
            return array();

        }
    }


    function updateSection($frm,$rvsby,$rvsdate)
    {

		$res = $this->conn->query("UPDATE ms_section set sect_name='".addslashes($frm['sect_name'])."'
                       ,dept_name='".addslashes($frm['dept_name'])."'
											 ,mod_by = '$rvsby'
											 ,mod_dd = '$rvsdate'
								WHERE sect_id ='$frm[sect_id]'") or die($this->conn->error);


         return true;

    }

	function deleteSection($id, $rvsby, $rvsdate)
    {
        $res = $this->conn->query("DELETE FROM ms_section WHERE sect_id = '$id'") or die($this->conn->error);


        return true;

    }

    function getDepartment()
      {
  		$res = $this->conn->query("SELECT * FROM ms_dept") or die($this->conn->error);
          if ($res->num_rows > 0) {
              while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                  $arr[] = $row;
              }
              return $arr;
          } else {
              return array();
          }

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
