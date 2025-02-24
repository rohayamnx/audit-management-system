<?php
require_once("global.php");
error_reporting(E_ERROR | E_PARSE);
$conn = $_SESSION['conn'];
session_start();

$module_name = 'AUDIT';
$script_name = 'AUDIT FINDINGS';

// doValidateUserAccess($script_name,$module_name);

$rvsby = getUserId();
$rvsdate = getCurrentTimeStamp();

$audit = new AuditModel();
$query = $audit->getAuditList();

// if (isset($_POST["delete"])) {
//
// 	if (!empty($_POST['ck'])){
//         foreach($_POST['ck'] AS $id) {
//
//             //delete account
//             $product->deleteProduct($id, $rvsby, $rvsdate);
//             $_POST = '';
//         }
//
//         $errMsg = "STATUS: Record deleted!";
// 		$url="product_list.php";
// 		header('Location:'. $url);
//
//     } else {
//         $errMsg = "WARNING: Please choose at least one record!";
//
//     }
// }

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
<h2 align="center"><?php echo $module_name. " - ". $script_name ?></h2>

<div class="panel panel-default">
<div class="panel-body">
<p><i class="fa fa-plus-square "></i> <a href="audit_new.php">New Audit Findings</a></p>
</div>
</div>
<br>
<form name="frm" id="frm" method="post" action="">
<div class="panel panel-default">
<div class="panel-heading">Audit Findings List</div>
<div class="panel-body">
<div class="table-responsive">
	 <table class="table table-striped table-bordered table-hover" id="dataTables-example">
		<thead>
		<tr>
			<td>No.</td>
			<td>Audit ID</td>
			<td>Reference No</td>
			<td>Business Oblige Function</td>
			<td>Department/Branch</td>
			<td>Unit</td>
			<td>Status</td>
		</tr>
		</thead>
		<tbody>
			<?php
				$cnt = 1;
				while($row = $query->fetch_array(MYSQLI_ASSOC)){

					if($row['status'] == 0){
						$status = "Pending";
					}elseif($row['status'] == 1){
						$status = "Submitted";
					}

			?>
					<tr>
					<td><?php echo $cnt; ?></td>
					<td><a href="audit_new.php?audit_id=<?php echo $row['audit_id']; ?>"><?php echo $row['audit_id']; ?></a></td>
					<td><?php echo $row['reference_no']; ?></td>
					<td><?php echo $row['org_name'];?></td>
					<td><?php echo $row['dept_name']; ?></td>
					<td><?php echo $row['sect_name']; ?></td>
					<td><?php echo $status; ?></td>
				</tr>
			<?php $cnt++;

				} ?>
		</tbody>
	  </table>

</div>
<br>
<?php if (!empty($query)) { ?>
<!-- <button type="submit" name="delete" value="Delete" onclick="return confirmDelete();" class="btn btn-danger btn-xs"><i class="fa fa-trash-o"></i> Delete</button><?php } ?> -->

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

	function getAuditList()
    {

		$res = $this->conn->query("SELECT * FROM audit_header WHERE audit_id != ''") or die($this->conn->error);
        return $res;

    }


	function deleteAudit($audit_id, $rvsby, $rvsdate)
    {
        $res_1 = $this->conn->query("DELETE FROM audit_header WHERE audit_id = '$audit_id'") or die($this->conn->error);
				$res_2 = $this->conn->query("DELETE FROM audit_details WHERE audit_id = '$product_id'") or die($this->conn->error);

        return true;

    }

	// function getAuditList()
  //   {
	// 	$res = $this->conn->query("SELECT * FROM product WHERE product_id !=''") or die($this->conn->error);
  //       return $res;
	//
  //   }

}
?>
</body>
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
</html>
