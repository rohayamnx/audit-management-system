<?php
require_once('global.php');
session_start();

$module_name = 'REPORT';
$script_name = 'AUDIT RATING REPORT';

doValidateUserAccess($script_name,$module_name);

$rvsby = getUserId();
$rvsdate = getCurrentTimeStamp();

$arr_a = array();
$arr_b = array();
$arr_c = array();
$arr_d = array();
$arr_e = array();

$res_dept = $conn->query("SELECT dept_name FROM ms_dept WHERE dept_id!='' ORDER BY dept_id") or die($this->conn->error);
if ($res_dept->num_rows > 0) {
		while ($row = $res_dept->fetch_array(MYSQLI_ASSOC)) {
				$dept_arr[] = $row;
		}

}


$get_rating = $conn->query("SELECT * FROM ms_rating");
if ($get_rating->num_rows > 0) {
	while ($row=$get_rating->fetch_array(MYSQLI_ASSOC)) {
		$rating_arr []= $row;
	}
}

$get_org = $conn->query("SELECT * FROM ms_organization");
if ($get_org->num_rows > 0) {
	while ($row=$get_org->fetch_array(MYSQLI_ASSOC)) {
		$org_arr []= $row;
	}
}

if (isset($_POST['search'])) {

		if($_POST['date_from']!='' && $_POST['date_to']!=''){
			$filter .= " AND (date_from>='".$_POST['date_from']."' AND date_to<='".$_POST['date_to']."')";
		}

		if($_POST['dept_name']!=''){
			$filter .= " AND dept_name LIKE '%".$_POST['dept_name']."%'";
		}
		$res = $conn->query("SELECT dept_name, audit_score FROM audit_header WHERE  audit_id !='' AND status > 0 $filter ORDER BY audit_id, date_from") or die($conn->error);

		$dtl_res = $conn->query("SELECT dept_name, audit_score FROM audit_header WHERE audit_id !='' AND status > 0 $filter ORDER BY audit_id, date_from") or die($conn->error);
		if ($dtl_res->num_rows > 0) {
			while($row = $dtl_res->fetch_array(MYSQLI_ASSOC)) {
				if ($row[audit_score]>=80 && $row[audit_score]<=100) {
					$arr_a [] = $row[dept_name];
				} else if ($row[audit_score]>=66 && $row[audit_score]<=79) {
					$arr_b [] = $row[dept_name];
				}  else if ($row[audit_score]>=50 && $row[audit_score]<=65) {
					$arr_c [] = $row[dept_name];
				} else if ($row[audit_score]>=30 && $row[audit_score]<=49) {
					$arr_d [] = $row[dept_name];
				} else if ($row[audit_score]>=0 && $row[audit_score]<=29) {
					$arr_e [] = $row[dept_name];
				}
			}
		}
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
<div class="panel panel-default">
<div class="panel-body">
<table width="100%" class="frm">
	<tr>
		<td>Date From:</td>
		<td><input type=date name=date_from value=<?php echo $_POST['date_from']; ?>></td>
		<td>Date To:</td>
		<td><input type=date name=date_to value=<?php echo $_POST['date_to'];?>></td>
	</tr>
	<tr>
		<td>Department/Branch : </td>
		<td>
      <select name="dept_name">
			<option value=""></option> <?php
              if (!empty($dept_arr)) {
                    foreach ($dept_arr AS $v) {?>
						<option value="<?php echo $v['dept_name']; ?>" <?php echo ($_POST['dept_name'] == $v['dept_name']) ? "selected" : ""?>><?php echo $v['dept_name'];?></option><?php
					}
			} ?>
            </select>
        </td>

		<td colspan=2 align=right><button type="submit" name="search" value="Search" class="btn btn-primary btn-xs"><i class="fa fa-search "></i>Search</button></td>
	</tr>
</table>
</div>
</div>
<br>

<div class="panel panel-default">
<br>
<?php
if($res->num_rows > 0) { ?>
	<br>
	<table class="table table-striped table-bordered table-hover" >
		<thead>
			<tr>
		<?php
			foreach ($rating_arr as $v) {
				echo '<td>'.$v[audit_rating].'</td>';
			}
		?>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td>
				<?php
				foreach($arr_a as $aud_a) {
					echo $aud_a.'<br>';
				} ?>
			</td>
			<td>
				<?php
				foreach($arr_b as $aud_b) {
					echo $aud_b.'<br>';
				} ?>
			</td>
			<td>
				<?php
				foreach($arr_c as $aud_c) {
					echo $aud_c.'<br>';
				} ?>
			</td>
			<td>
				<?php
				foreach($arr_d as $aud_d) {
					echo $aud_d.'<br>';
				} ?>
			</td>
			<td>
				<?php
				foreach($arr_e as $aud_e) {
					echo $aud_e.'<br>';
				} ?>
			</td>
	 </tr>
		</tbody>
	  </table>
<?php } else { ?>
<div class="alert alert-info">
	There has no result.
</div><?php
} ?>
</div>
</form>
</div>
</div>
</div>
<?php include('footer.php'); ?>
</div>
<!--script language="javascript">
function print(supp_id, type, product_name){
        url = "report_product_pdf.php?supp_id=" + supp_id + "&type=" + type + "&product_name=" + product_name;
	window.open(url);
}
</script-->
</body>
</html>
