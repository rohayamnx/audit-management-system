<?php
require_once("global.php");
error_reporting(E_ERROR | E_PARSE);
$conn = $_SESSION['conn'];
session_start();

$module_name = 'MASTER SETUP';
$script_name = 'TEAM';

doValidateUserAccess($script_name,$module_name);

$rvsby = getUserId();
$rvsdate = getCurrentTimeStamp();


$user = new UserModel();


if (empty($_POST)) {
    $_SESSION['show_update'] = false;

}

if(isset($_POST["save"])){
	$proceed = true;

	$isExist = $user->checkTeam($_POST['team_id']);

	if (empty($_POST['team_id'])) {
		$errMsg = "Team id can not be empty";
		$proceed = false;
	}elseif ($isExist) {
    $errMsg = "Team ID already exist!";
    $proceed = false;

	}
	if ($proceed) {

		$res = $user->insertTeam($_POST,$rvsby,$rvsdate);
		if($res){
			$status = "New Data Added Sucessfully..!";
		}else{
			$errMsg = "Data Fail to Add..!";
		}
		$url="ms_team.php";
		header('Location:'. $url);
	}
}elseif(isset($_POST["update"])){

	$proceed = true;

	if ($proceed) {
		$update = $user->updateTeam($_POST, $rvsby, $rvsdate);

		if($update){

			$status = "Data Updated Sucessfully..!";
			$_POST = '';
			$_SESSION['show_update'] = false;

		}

		$url="ms_team.php";
		header('Location:'. $url);
	}
}else if (isset($_POST["delete"])) {

	if (!empty($_POST['ck'])){
        foreach($_POST['ck'] AS $id) {

            //delete account
            $user->deleteTeam($id, $rvsby, $rvsdate);

            $_POST = '';
        }

        $errMsg = "Record deleted!";

    } else {
        $errMsg = "Please choose at least one record!";

    }

	$url="ms_team.php";
    header('Location:'. $url);

}elseif (isset($_POST['edit'])){

    if (empty($_POST['ck'])){
        $errMsg = "Please choose one record!";

    } else {
        $key = $_POST['ck'];
        $_POST = $user->getTeam($key[0]);
		    $_POST['user_pwd'] = '';

        //show update button
        $_SESSION['show_update'] = true;

		if (isset($_GET['Page'])) $Page = '';
    }

}


//show account list
if (!isset($_POST['search']) && !isset($_GET['search'])) {

    if (isset($_GET['Page'])); $Page = '';
    $query = $user->getTeamList();

}

i

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
}


if(!empty($status)){
  echo '<script>MsgStatus("'.$status.'");</script>';	?>
  <div class="alert alert-success alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
    <a href="#" class="alert-link">Status</a>:<?php echo $status;?>
  </div><?php
}?>

<h2 align="center"><?php echo $module_name. " - ". $script_name ?></h2>
<form name="frm" id="frm" method="post" action=""  >

<div class="panel panel-default">
<div class="panel-body">

  <div class="form-group">
		<div class="col-lg-10">
			<div class="row">
				<label for="inputEmail1" class="col-lg-2 control-label">Team ID</label>
				<div class="col-lg-4">
					<input type="text" class="form-control"name="team_id" size="10" maxlength="10" value="<?php echo $_POST['team_id'];?>">
				</div>
				<label for="inputEmail1" class="col-lg-2 control-label"></label>
        <div class="col-lg-4">
            <input type="hidden" class="form-control"name="id" size="10" maxlength="10" value="<?php echo $_POST['id'];?>">

				</div>
			</div>
		</div>
	</div><br><br>
<!--
  <div class="form-group">
    <div class="col-lg-10">
      <div class="row">
        <label for="inputEmail1" class="col-lg-2 control-label">Start Date</label>
        <div class="col-lg-4">
          <input type="date" class="form-control" name="start_dd" value="<?php //echo $_POST['start_dd'];?>">
        </div>
        <label for="inputEmail1" class="col-lg-2 control-label">End Date</label>
        <div class="col-lg-4">
          <input type="date" class="form-control" name="end_dd" value="<?php //echo $_POST['end_dd'];?>">
        </div>
      </div>
    </div>
  </div> -->

  <div class="form-group">
    <div class="col-lg-10">
      <div class="row">
        <label for="inputEmail1" class="col-lg-2 control-label">
          <button type="submit" name="<?php echo (!$_SESSION['show_update']) ? "save" : "update" ;?>" value="<?php echo (!$_SESSION['show_update']) ? "Save" : "Update" ;?>" class="btn btn-primary btn-xs"><i class="fa fa-edit "></i><?php echo (!$_SESSION['show_update']) ? "Save" : "Update" ;?></button>
        </label>
      </div>
    </div>
  </div>

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
			<td>Team ID</td>
			<td>Created By</td>
      <td>Created Date</td>
      <td>Modified By</td>
      <td>Modified Date</td>
		</tr>
		</thead>
		<tbody>
			<?php
				$cnt = 1;
				while($row = $query->fetch_array(MYSQLI_ASSOC)){?>
					<tr>
					<td><input type="checkbox" name="ck[]" value="<?php echo $row["id"]; ?>"></td>
					<td><?php echo $cnt; ?></td>
          <td><a href="ms_team_detail.php?id=<?php echo $row["id"]; ?>"><?php echo $row["team_id"];?></a></td>
					<td><?php echo $row["crt_by"];?></td>
          <td><?php echo $row["crt_dd"];?></td>
          <td><?php echo $row["mod_by"];?></td>
          <td><?php echo $row["mod_dd"];?></td>
				</tr><?php $cnt++;

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
class UserModel {

  var $conn;

    function UserModel() {
        $this->conn = $_SESSION['conn'];
    }

	function getTeamList()
  {
		$res = $this->conn->query("SELECT * FROM ms_team WHERE team_id != ''") or die($this->conn->error);
        return $res;
  }

	function checkTeam($team_id)
  {
	     $res = $this->conn->query("SELECT * FROM ms_team WHERE team_id = '".addslashes($team_id)."' AND team_id !=''") or die($this->conn->error);
       if($res->num_rows > 0){
           return true;
       }else{
           return false;
       }
  }

  function insertTeam($frm,$rvsby,$rvsdate)
  {
     $res = $this->conn->query("INSERT INTO ms_team (team_id, crt_by, crt_dd)
										   VALUES ('".addslashes($frm['team_id'])."','$rvsby','$rvsdate')") or die($this->conn->error);
     if($res){
         return true;
     }else{
         return false;
      }
  }

    function getTeam($id)
    {
        $res = $this->conn->query("SELECT * FROM ms_team WHERE id = '".addslashes($id)."' LIMIT 1") or die($this->conn->error);

        if ($res->num_rows > 0) {
            return $res->fetch_array(MYSQLI_ASSOC);

        } else {
            return array();

        }
    }

    function updateTeam($frm,$rvsby,$rvsdate)
    {

			$res=  $this->conn->query("UPDATE ms_team SET is_active='$frm[is_active]'
                         ,mod_by='$rvsby'
                         ,mod_dd='$rvsdate'
									 WHERE id ='$frm[id]'") or die($this->conn->error);

         return true;

    }

	function deleteTeam($id, $rvsby, $rvsdate)
    {
        $res = $this->conn->query("DELETE FROM ms_team WHERE id = '$id'") or die($this->conn->error);
        $res = $this->conn->query("DELETE FROM ms_team_detail WHERE team_ref_id = '$id'") or die($this->conn->error);


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
