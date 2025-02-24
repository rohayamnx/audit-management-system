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


$access_d = new AccessMasterD();

if (empty($_POST)) {
    $_SESSION['show_update'] = false;
}

if (!empty($_GET['access_level'])) {
	$access_desc = $access_d->getAccDesc($_GET['access_level']);
    $arr_module = $access_d->getModuleList();
	$acclev_child = $access_d->getAccLevChild($_GET['access_level']);
	$acclev_master = $access_d->getAccLevMaster($acclev_child);
	//print_r($acclev_child);
	//exit();
}

if(isset($_POST["save"])){
	$proceed = true;


	if ($proceed) {

		$res = $access_d->insertAccLevModule($_POST, $_GET['access_level'], $rvsby, $rvsdate);
		if($res){
			$errMsg = "New Data Added Sucessfully..!";
		}else{
			$errMsg = "Data Fail to Add..!";
		}
		$url="ms_access_detail.php?access_level=$_GET[access_level]";
		header('Location:'. $url);
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
<table  width = "100%" class="frm">
	<tr>
		<th>Access Level</td>
		<td><?php echo $_GET['access_level'];?></td>
	    <th>Description</th>
		<td><?php echo $access_desc;?></td>
    </tr>
</table>
</div>
</div>

<br>

<div class="panel panel-default">
<div class="panel-body">
<table class="table table-striped" >
<thead>
<tr>
	<th>Main Operation Menu</th>
	<th>First Level Child Menu</th>
</tr>
</thead>
<?php
	 foreach($arr_module AS $master_module=>$master_module_arr){?>
		 <tr>
			<td><input type="checkbox" value="<?php echo $master_module;?>" onclick="checkAll(this,'ck[<?php echo $master_module;?>][]');"<?php if (array_key_exists($master_module,$acclev_master)){echo "checked";} ?>><?php echo $master_module;?></td>
			<td><?php
			   foreach($master_module_arr AS $child_module){ ?>
					<input type="checkbox" name="ck[<?php echo $master_module;?>][]" value="<?php echo $child_module;?>" <?php
					if (array_key_exists($child_module,$acclev_child))
					  {echo "checked";} ?>><?php echo $child_module;?><br><?php
			   }?>

		   </td>
		  </tr>
<?php $cnt++;

	} ?>
	<tr>
		<td>
		<button type="submit" name="save" value="Save" class="btn btn-primary btn-xs"><i class="fa fa-edit "></i> Save</button>&nbsp;
		<button type="button" name="btnback" value="Back" onclick="javascript:window.location='ms_access.php'" class="btn btn-primary btn-xs"><i class="fa fa-backward"></i> Back</button>
		</td>
		<td></td>
</table>
</div>
</div>

</div>
</div>
</div>
<?php include('footer.php'); ?>
</div>
<?php

class AccessMasterD {

  var $conn;

    function AccessMasterD() {
        $this->conn = $_SESSION['conn'];
    }

	function getAccDesc($access_level)
    {
	   $res = $this->conn->query("SELECT access_desc FROM ms_halevel WHERE access_level = '$access_level' AND access_level !=''") or die($this->conn->error);
       if ($res->num_rows > 0) {
            return $res->fetch_object()->access_desc;

        } else {
            return array();

        }
    }

	function insertAccLevModule($frm, $access_level, $rvsby, $rvsdate){

		$res_del = $this->conn->query("DELETE FROM ms_dalevel WHERE access_level = '$access_level' AND access_level!=''") or die($this->conn->error);

		if($res_del){
			foreach($frm['ck'] as $v){
				foreach($v as $k=>$childmodule){
					$res = $this->conn->query("INSERT INTO ms_dalevel (access_level,script_name, act_flag, rvsby, rvsdate)
																 VALUE('$access_level','".addslashes($childmodule)."', 1, '$rvsby', '$rvsdate')") or die($this->conn->error);

				}
			}
		}

	}

	function getModuleList()
    {
		$res = $this->conn->query("SELECT * FROM ms_module a LEFT JOIN ms_dmodule b ON a.module_id = b.mdetail_mstid ORDER by a.module_desc, b.mdetail_desc") or die($this->conn->error);

		if ($res->num_rows > 0) {
			while($row = $res->fetch_array(MYSQLI_ASSOC)){
				$arr[$row['module_desc']][] = $row['mdetail_desc'];
			}

			return $arr;
		}else {
            return array();
        }
    }

	function getAccLevChild($access_level)
    {
        $res = $this->conn->query("SELECT * FROM ms_dalevel WHERE access_level = '$access_level' AND access_level !=''") or die($this->conn->error);

        if ($res->num_rows > 0) {
          while($row = $res->fetch_array(MYSQLI_ASSOC)){
				$arr[$row['script_name']] = $row;
			}

			return $arr;

        }else {
            return array();
        }
    }

	function getAccLevMaster($child)
    {
        foreach($child as $v=>$k){
            $res = $this->conn->query("SELECT module_desc FROM ms_module a LEFT JOIN ms_dmodule b ON a.module_id = b.mdetail_mstid WHERE b.mdetail_desc = '$v'") or die($this->conn->error);

              while($row = $res->fetch_array(MYSQLI_ASSOC)){
                $arr[$row["module_desc"]] = true;
            }
        }
        return $arr;
    }

}

?>
</body>
<script language="javascript">
function checkAll(source,name){

checkboxes = document.getElementsByName(name);
  for(var i=0, n=checkboxes.length;i<n;i++) {
    checkboxes[i].checked = source.checked;
  }


}
</script>
</html>
