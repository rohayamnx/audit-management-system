<?php
require_once("global.php");
error_reporting(E_ERROR | E_PARSE);
$conn = $_SESSION['conn'];
session_start();

$module_name = 'MASTER SETUP';
$script_name = 'USER';

doValidateUserAccess($script_name,$module_name);

$rvsby = getUserId();
$rvsdate = getCurrentTimeStamp();


$user = new UserModel();
$position_arr = $user->getPosition();
$alev_arr = $user->getAlev();
$dept_arr = $user->getDept();

if (empty($_POST)) {
    $_SESSION['show_update'] = false;

}

if(isset($_POST["save"])){
	$proceed = true;

	$isExist = $user->checkUser($_POST['user_id']);

	if (empty($_POST['user_id'])) {
		$errMsg = "User id can not be empty";
		$proceed = false;
	}elseif ($isExist) {
        $errMsg = "User ID already exist!";
        $proceed = false;

	}else if ($_POST['access_level']=="") {
		$errMsg = "Access Level can not be empty";
		$proceed = false;
	}else if ($_POST['position']=="") {
		$errMsg = "Position can not be empty";
		$proceed = false;
	}else if ($_POST['department']=="") {
		$errMsg = "Department can not be empty";
		$proceed = false;
	} else if ($_POST['user_pwd']!=$_POST['pwd_confirm']) {
		$errMsg = "Password not matched";
		$proceed = false;
	}

	if ($proceed) {

		$res = $user->insertUser($_POST,$rvsby,$rvsdate);
		if($res){
			$errMsg = "New Data Added Sucessfully..!";
		}else{
			$errMsg = "Data Fail to Add..!";
		}
		$url="ms_user.php";
		header('Location:'. $url);
	}
}elseif(isset($_POST["update"])){

	$proceed = true;

	if ($_POST['access_level']=="") {
		$errMsg = "Access Level can not be empty";
		$proceed = false;
	}else if ($_POST['position']=="") {
		$errMsg = "Position can not be empty";
		$proceed = false;
	}else if ($_POST['department']=="") {
		$errMsg = "Department can not be empty";
		$proceed = false;
	}else if ($_POST['user_pwd']!=$_POST['pwd_confirm']) {
		$errMsg = "Password not matched";
		$proceed = false;
	}

	if ($proceed) {
		$update = $user->updateUser($_POST, $rvsby, $rvsdate);

		if($update){

			$errMsg = "Data Updated Sucessfully..!";
			$_POST = '';
			$_SESSION['show_update'] = false;

		}
		$url="ms_user.php";
		header('Location:'. $url);
	}
}else if (isset($_POST["delete"])) {

	if (!empty($_POST['ck'])){
        foreach($_POST['ck'] AS $id) {

            //delete account
            $user->deleteUser($id, $rvsby, $rvsdate);

            $_POST = '';
        }

        $errMsg = "Record deleted!";

    } else {
        $errMsg = "Please choose at least one record!";

    }

	$url="ms_user.php";
    header('Location:'. $url);

}elseif (isset($_POST['edit'])){

    if (empty($_POST['ck'])){
        $errMsg = "Please choose one record!";

    } else {
        $key = $_POST['ck'];
        $_POST = $user->getUser($key[0]);
		$_POST['user_pwd'] = '';

        //show update button
        $_SESSION['show_update'] = true;

		if (isset($_GET['Page'])) $Page = '';
    }

}


//show account list
if (!isset($_POST['search']) && !isset($_GET['search'])) {

    if (isset($_GET['Page'])); $Page = '';
    $query = $user->getUserList();

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

<div class="panel panel-default">
<div class="panel-body">
<table width="100%" class="frm">
    <tr>
		<td width="100px">Staff ID</td>
		<td><input type="text" name="user_id"  value="<?php echo $_POST['user_id']; ?>" <?php if($_SESSION['show_update']) echo "readonly"; ?>></td>
		<td width="100px">Staff Position</td>
		<td>
			<select name="position">
			<option></option>
				<?php if (!empty($position_arr)) {
					foreach ($position_arr AS $v) {
				?>
					<option value="<?php echo $v['position_id']; ?>" <?php echo ($_POST['position'] == $v['position_id']) ? "selected" : ""?>><?php echo $v['position_id'] . " | " . $v['position_name'];?></option>
				<?php }
				} ?>
			</select>
		</td>
	</tr>
	<tr>
		<td>Staff Name</td>
		<td><input type="text" name="user_name" value="<?php echo $_POST['user_name'];?>"></td>
		<td>Access Level</td>
		<td>
			<select name="access_level">
			<option></option>
		   <?php if (!empty($alev_arr)) {
				foreach ($alev_arr AS $v) {
			?>
				<option value="<?php echo $v['access_level']; ?>" <?php echo ($_POST['access_level'] == $v['access_level']) ? "selected" : ""?>><?php echo $v['access_level']. ' - ' . $v['access_desc'];?></option>
			<?php }
				} ?>
			</select>
		</td>
	</tr>
	<tr>
		<td>Password</td>
		<td><input type="password" name="user_pwd" size="20" value="<?php echo $_POST['user_pwd'];?>"></td>
		<td>Department/Branch</td>
		<td>

			<select name="department">
			<option></option>
			  <?php if (!empty($dept_arr)) {
					foreach ($dept_arr AS $v) {
				?>
					<option value="<?php echo $v['dept_id']; ?>" <?php echo ($_POST['department'] == $v['dept_id']) ? "selected" : ""?>><?php echo $v['dept_name'];?></option>
				<?php }
					} ?>
			</select>

		</td>
	</tr>
	<tr>
		<td>Password Confirmation</td>
		<td><input type="password" name="pwd_confirm" size="20" value="<?php echo $_POST['pwd_confirm'];?>"></td>
		<td>Email Address</td>
		<td><input type="text" name="email" value="<?php echo $_POST['email'];?>"></td>
	</tr>
	<tr>
		<td colspan="4" >
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
			<td>Staff ID</td>
			<td>Staff Name</td>
			<td>Position</td>
			<td>Access Level</td>
		</tr>
		</thead>
		<tbody>
			<?php
				$cnt = 1;
				while($row = $query->fetch_array(MYSQLI_ASSOC)){
			?>
					<tr>
					<td><input type="checkbox" name="ck[]" value="<?php echo $row["user_id"]; ?>"></td>
					<td><?php echo $cnt; ?></td>
					<td><?php echo $row["user_id"];?></td>
					<td><?php echo $row['user_name']; ?></td>
					<td><?php echo $row['position_name']; ?></td>
					<td><?php echo $row['access_level']; ?></td>
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
class UserModel {

  var $conn;

    function UserModel() {
        $this->conn = $_SESSION['conn'];
    }

	function getUserList()
    {
		$res = $this->conn->query("SELECT mu.*, mp.position_name FROM ms_user mu LEFT JOIN ms_position mp ON mu.position = mp.position_id WHERE mu.user_id != ''") or die($this->conn->error);
        return $res;
    }

	function checkUser($user)
    {
	   $res = $this->conn->query("SELECT * FROM ms_user WHERE user_id = '".addslashes($user)."' AND user_id !=''") or die($this->conn->error);
       if($res->num_rows > 0){
           return true;
       }else{
           return false;
        }
    }


    function insertUser($frm,$rvsby,$rvsdate)
    {
       $res = $this->conn->query("INSERT INTO ms_user (user_id, user_pwd, user_name, department, position, access_level, email, crd_by, crd_dd)
											   VALUES ('".addslashes($frm['user_id'])."','".md5($frm['user_pwd'])."','$frm[user_name]','$frm[department]',
													  '$frm[position]','$frm[access_level]','$frm[email]','$rvsby','$rvsdate')") or die($this->conn->error);
       if($res){
           return true;
       }else{
           return false;
        }
    }

    function getUser($user_id)
    {
        $res = $this->conn->query("SELECT * FROM ms_user WHERE user_id = '".addslashes($user_id)."' LIMIT 1") or die($this->conn->error);

        if ($res->num_rows > 0) {
            return $res->fetch_array(MYSQLI_ASSOC);

        } else {
            return array();

        }
    }

    function updateUser($frm,$rvsby,$rvsdate)
    {

		if ($frm['user_pwd']!='') {

			$res=  $this->conn->query("UPDATE ms_user SET user_pwd='".md5($frm['user_pwd'])."'
												 ,user_name='".addslashes($frm['user_name'])."'
												 ,position='$frm[position]'
												 ,access_level='$frm[access_level]'
												 ,department='$frm[department]'
												 ,email='".addslashes($frm['email'])."'
												 ,upd_by = '$rvsby'
												 ,upd_dd = '$rvsdate'
									 WHERE user_id='$frm[user_id]'") or die($this->conn->error);
		} else {
			$res = $this->conn->query("UPDATE ms_user set user_name='".addslashes($frm['user_name'])."'
												 ,position='$frm[position]'
												 ,access_level='$frm[access_level]'
												 ,department='$frm[department]'
												 ,email='".addslashes($frm['email'])."'
												 ,upd_by = '$rvsby'
												 ,upd_dd = '$rvsdate'
									WHERE user_id='$frm[user_id]'") or die($this->conn->error);
		}

         return true;

    }

	function deleteUser($id, $rvsby, $rvsdate)
    {
        $res = $this->conn->query("DELETE FROM ms_user WHERE user_id = '$id'") or die($this->conn->error);


        return true;

    }

	function getPosition()
    {
        $res = $this->conn->query("SELECT * FROM ms_position ORDER BY position_id") or die($this->conn->error);
        if ($res->num_rows > 0) {
            while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
            return $arr;
        } else {
            return array();
        }
    }

	function getAlev()
    {
        $res = $this->conn->query("SELECT * FROM ms_halevel ORDER BY access_level") or die($this->conn->error);
        if ($res->num_rows > 0) {
            while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
            return $arr;
        } else {
            return array();
        }
    }

	function getDept()
    {
        $res = $this->conn->query("SELECT * FROM ms_dept ORDER BY dept_name") or die($this->conn->error);
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
