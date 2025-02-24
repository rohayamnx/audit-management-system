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

$team = new TeamDetailModel();

if (empty($_POST)) {
    //clear session
    unset($_SESSION['team_detail_list']);
    unset($_SESSION['edit_flag']);

    $i = new TeamDetailObj();
    $_SESSION['edit_flag'] = false;
	  $_SESSION['show_update'] = false;

}
$list_user = $team->getUserList();

if (empty($_POST) && !empty($_GET['id'])) {

	unset($_SESSION['team_detail_list']);
	$i = $team->prepareItems($_GET['id']);
	$_SESSION['edit_flag'] = true;
}

if (isset($_POST['add_item'])) {

	  $i = $_SESSION['team_detail_list'];
    if (empty($i)) $i = new TeamDetailObj();

    $other_team = $team->checkOtherTeam($_POST['new_user_id'], $_GET['id']);
    // echo '<pre>';
    // print_r($other_team);
    // echo '</pre>';

    $proceed = true;

    if (empty($_POST['new_user_id'])) {
        $errMsg = "WARNING: Please add User ID!";
        $proceed = false;
    }elseif ($other_team['team_id'] !='') {
  		  $errMsg = "WARNING: User ID already added in Team $other_team[team_id]!";
        $proceed = false;

  	}

		if($proceed){
			//echo $id;
			$i->deleteItem($_POST['new_user_id']);

			$res = $team->addTeamDetail($i, $_POST);
			$status = "STATUS: New item added!";

			//clear new item detail in form
			$_POST['new_user_id'] = "";
			$_SESSION['show_update'] = false;
		}

		$_SESSION[$_POST['team_detail_list']] = $i;


//Delete added item
}elseif (isset($_POST['save'])) {

    $i = $_SESSION['team_detail_list'];
    if (empty($i)) $i = new TeamDetailObj();

    $proceed = true;

	//if ($i->getItemCount() == 0) {
    //    $proceed = false;
    //    $errMsg = "WARNING: Please add item before save.";

    //}

    if ($proceed) {
      //save DO
      $res = $team->saveTeamDetail($i, $_GET['id']);

      if ($res) {
          //clear session
          unset($_SESSION['team_detail_list']);
          unset($_SESSION['edit_flag']);
          $errMsg = "STATUS: Saved successfully.";
          $url = "ms_team_detail.php?id=$_GET[id]";
  				echo'<meta http-equiv="refresh" content="0;URL='.$url.'">';
         // header("Location: $url");
      }

    }

}elseif (isset($_POST['delete'])) {

   $i = $_SESSION['team_detail_list'];
   if (empty($i)) $i = new TeamDetailObj();

   $arr = $_POST['ck'];

   if (!empty($arr)){

       foreach ($arr AS $id){
          $i->deleteItem($id);
       }

   }else{
        $errMsg = "WARNING: Please choose at least one record!";
   }


    $_SESSION['team_detail_list'] = $i;

//save or update DO
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
}

if(!empty($status)){
  echo '<script>MsgStatus("'.$status.'");</script>';	?>
  <div class="alert alert-success alert-dismissable">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
    <a href="#" class="alert-link">Status</a>:<?php echo $status;?>
  </div><?php
}

$res_id = $conn->query("SELECT * FROM ms_team WHERE id = '$_GET[id]' ") or die($conn->error);
if($res_id->num_rows > 0){
	$row_t = $res_id->fetch_array(MYSQLI_ASSOC);
  $team_id = $row_t['team_id'];
  $start_dd = $row_t['start_dd'];
  $end_dd = $row_t['end_dd'];
}

?>

<h2 align="center"><?php echo $module_name. " - ". $script_name ?></h2>
<form name="frm" id="frm" method="post" action="">
<div class="panel panel-default">
<div class="panel-body">
<table  width = "100%" class="frm">
	<tr>
		<th>Access Level</th>
		<td><?php echo $_GET['id'];?></td>
	    <th>Description</th>
		<td><?php echo $team_id;?></td>
    </tr>
</table>
</div>
</div>
<br>

<div class="panel panel-default">
<div class="panel-body">
<table width = "100%" class="frm" >
  <tr>
    <th>User:</th>
    <td>
      <select name="new_user_id">
          <option value="">-Select User-</option>
          <?php
          if(!empty($list_user)){
              while ($orow = $list_user->fetch_array(MYSQLI_ASSOC)){
                  echo '<option value="'.$orow['user_id'].'"  '.($_POST['new_user_id']==$orow['user_id']?"selected":"").'>'.$orow['user_id'].'</option>';
              }
          }?>

      </select>&nbsp;&nbsp;<button type="submit" name="add_item" value="Add" class="btn btn-primary btn-xs"><i class="fa fa-edit "></i> Add User</button> &nbsp;
      <button type="button" name="btnback" value="Back" onclick="javascript:window.location='ms_team.php'" class="btn btn-primary btn-xs"><i class="fa fa-backward"></i> Back</button>
    </td>
  </tr>

</table>
</div>
</div>
<br><?php
if(!empty($i)){

if ($i->getItemCount() > 0) { ?>
<div class="panel panel-default">
<div class="panel-heading">Search Result</div>
<div class="panel-body">
<div class="table-responsive">
	 <table class="table table-striped table-bordered table-hover" id="dataTables-example">
		<thead>
		<tr>
			<td></td>
			<td>No.</td>
			<td>User ID</td>
			<td>User Name</td>
		</tr>
		</thead>
		<tbody><?php

    if (!empty($i) || $i != '') {
        $cnt = 1;

  			foreach($i->getItems() AS $k=>$v) {
            $user_name = "";
            $res_name = $conn->query("SELECT * FROM ms_user WHERE user_id = '$v->user_id' ") or die($conn->error);
            if($res_name->num_rows > 0){
            	$user_name = $res_name->fetch_object()->user_name;
            }?>

  					<tr>
    					<td><input type="checkbox" name="ck[]" value="<?php echo $v->user_id; ?>"></td>
    					<td><?php echo $cnt; ?></td>
              <td><?php echo $v->user_id;?></a></td>
    					<td><?php echo $user_name;?></td>
  				  </tr><?php $cnt++;
				}
    } ?>
		</tbody>
	  </table>

</div>
<br>
<?php if ($i->getItemCount() > 0) { ?>
<button type="submit" name="save" value="Save" class="btn btn-primary btn-xs"><i class="fa fa-edit "></i> Save</button>
<button type="submit" name="delete" value="Delete" onclick="return confirmDelete();" class="btn btn-danger btn-xs"><i class="fa fa-pencil"></i> Delete</button>&nbsp;<?php } ?>

</div>

</div><?php
}
}
?>

</div>
</div>
</div>
<?php include('footer.php'); ?>
</div>
<?php

require("../inc/footer.php");

//object

class TeamDetailObj {

  private $id = '';
  private $team_id = '';
  private $user_id = '';


    private $items_list = array();

    public function __get($nm)
    {
        if (isset($this->$nm)) {
                return $this->$nm;

        } else {
            return("Property $nm doesn't exist!");

        }
    }

    public function __set($nm, $val)
    {
        if (isset($this->$nm)) {
            $this->$nm = $val;

        } else {
                return("Property $nm doesn't exist!");

        }

    }

    public function addItem($do)
    {
    	$this->items_list[$do->user_id] = $do;

    }

    public function getItems()
    {
    	return $this->items_list;

    }

	public function getItemsEdit($user_id)
    {

    	return $this->items_list[$user_id];

    }

    public function getItemCount()
    {
    	return count($this->items_list);

    }

    public function deleteItem($id)
    {
    	$do = $this->items_list;
    	$tmp = array();

    	foreach($do AS $k=>$o){
    		if ($k != $id){
    			$tmp[$k] = $o;
    		}
    	}

    	$this->items_list = $tmp;

    }


    public function updItem($tmp_array, $field_type)
    {
        $do = $this->items_list;
        foreach ($tmp_array AS $k=>$v){
            $do[$k]->$field_type = (empty($v)) ? 0 : $v;

        }
    }

    public function isExistItem($user_id)
    {
        $exist = false;
        $do = $this->items_list;
        foreach ($do AS $oid=>$oid_arr){
    				if ($oid == $user_id ) {
    					$exist = true;
    				}

        }
        return $exist;
    }


}

//classs
class TeamDetailModel {

	var $conn;

	  function TeamDetailModel() {
        $this->conn = $_SESSION['conn'];
    }

    function getUserList()
    {
  		$res = $this->conn->query("SELECT * FROM ms_user WHERE user_id != ''") or die($this->conn->error);
      return $res;
    }

    function checkOtherTeam($user_id, $id)
    {
          $res = $this->conn->query("SELECT td.*, t.* FROM ms_team_detail td JOIN ms_team t ON t.id=td.team_ref_id
                                     WHERE td.user_id = '$user_id' AND t.is_active = 1 AND t.id != '$id'") or die($this->conn->error);

  		    if($res->num_rows > 0){
              return $res->fetch_array(MYSQLI_ASSOC);

          } else {
              return false;
          }

      }

    function getTeamDetail(&$doi, $id)
    {
          $res = $this->conn->query("SELECT t.*, td.* FROM ms_team t JOIN ms_team_detail td ON t.id = td.team_ref_id
                                WHERE t.id = '$id'") or die($this->conn->error);


          if ($res->num_rows > 0) {
              while ($row = $res->fetch_array(MYSQLI_ASSOC)) {

                  //add to list
                  $i = $this->_getTeamDetail($row);
                  //add to object
                  $doi->addItem($i);

              }

          }

    }


    function addTeamDetail(&$doi, $frm)
    {
        if (!empty($frm)) {

            //convert
            $tmp_frm['id'] = trim($frm['new_id']);
            $tmp_frm['team_id'] = $frm['new_team_id'];
						$tmp_frm['user_id'] = trim($frm['new_user_id']);

            //add to list
            $i = $this->_getTeamDetail($tmp_frm);
            //add to object
            $doi->addItem($i);

        }

        return true;

    }



    function saveTeamDetail($i, $id)
    {
        //reset details
        $this->deleteTeamDetail($id);

        //save new details
        $arr = $i->items_list;
        $this->_setTeamDetail($arr, $id);

        return true;

    }

    function deleteTeamDetail($id)
    {
        //delete details
        $this->conn->query("DELETE FROM ms_team_detail WHERE team_ref_id = '$id'") or die($this->conn->error);

        return true;

    }



    function prepareItems($id)
    {
        $i = new TeamDetailObj();

        if (!empty($id)){
            //add item into object
            $this->getTeamDetail($i, $id);
        }
        $_SESSION['team_detail_list'] = $i;

        return $i;

    }

    /****************************** Private function *********************************/

    private function _getTeamDetail($i)
    {
        $team_detail = new TeamDetailObj();
        $team_detail->id = $i['id'];
        $team_detail->team_id = $i['team_id'];
    		$team_detail->user_id = $i['user_id'];

        return $team_detail;
    }

    private function _setTeamDetail($arr, $id)
    {

        if (!empty($arr)) {
            $no = 1;
            foreach($arr AS $oid=>$i) {

        					$i->acc_id = str_replace("'","",$i->acc_id);
        					$i->acc_id = preg_replace('/[.-]/', '', $i->acc_id);

                  $this->conn->query("INSERT INTO ms_team_detail (team_ref_id, user_id) VALUES ('$id', '$i->user_id')") or die($this->conn->error);
                  $no++;


           }
        }

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
