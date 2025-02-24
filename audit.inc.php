<?php
require_once("audit.obj.php");

class AuditModel {

	var $conn;

    function AuditModel() {
        $this->conn = $_SESSION['conn'];
    }

		function approve($audit_id, $trxBy, $trxDate)
    {
				$this->conn->query("UPDATE audit_header SET status = 1, approved_by = '$trxBy', approved_dd = '$trxDate' WHERE audit_id = '$audit_id'") or die($this->conn->error);

				return true;
		}

	function getUniqueID($date)
    {
			$tmp_dd = $date;

			$yy = substr($tmp_dd, 2, 2);
			$mm = substr($tmp_dd, 5, 2);

			$dd = $yy.$mm;

			$get_id = $this->conn->query("SELECT DISTINCT SUBSTRING(audit_id,-3) AS audit_no FROM audit_header WHERE audit_id LIKE 'A".$dd."%' ORDER BY audit_id DESC LIMIT 1");
			// echo "SELECT DISTINCT SUBSTRING(audit_id,-3) AS audit_no FROM audit_header WHERE audit_id LIKE '".$dd."%' ORDER BY audit_id DESC LIMIT 1 <br>";
			$used_id = $get_id->fetch_object()->audit_no;

			$next_no = $used_id+1;
			$tmp_id=str_pad($next_no, 3, "0", STR_PAD_LEFT);

			// echo $next_no.' '.$tmp_id.'<br>';
			// exit();

			$id = "A".$dd."".$tmp_id;

      return $id;
    }

    function insertAudit($i, $frm, $audit_id, $trxBy, $trxDate)
    {

				$arr_trail = $i->getItemsAll();

				$this->_setAuditDetails($i, $arr_trail, $frm, $audit_id, $trxBy, $trxDate);

				return true;
		}

		function updateAudit($i, $frm, $audit_id, $trxBy, $trxDate)
    {
			  $this->deleteAuditDetails($i, $audit_id, $trxBy, $trxDate, false);

				$arr_trail = $i->getItemsAll();

				$this->_setAuditDetails($i, $arr_trail, $frm, $audit_id, $trxBy, $trxDate);

				return true;
		}

		function deleteAuditDetails($i, $audit_id, $trxBy, $trxDate, $is_cancel=false)
    {
				// if($is_cancel){
				// 		$arr = $i->getItemsAll();
				// }

				$this->conn->query("DELETE FROM audit_header WHERE audit_id = '$audit_id'") or die($this->conn->error);
				$this->conn->query("DELETE FROM audit_trail WHERE audit_id = '$audit_id'") or die($this->conn->error);
		}

		private function _setAuditDetails($i, $arr_trail, $frm, $audit_id, $trxBy, $trxDate)
		{

			if($frm[scope_name] == "OTHER" && $frm[other_scope] != "") {
				$frm[scope_name] = $frm[other_scope];
			} else {
				$frm[scope_name] = $frm[scope_name];
			}


			// part of insert digital signature
			if(!empty($frm['signature'])){
					$folderPath = "img_signature/";
		      $image_parts = explode(";base64,", $frm['signature']);


		      $image_type_aux = explode("image/", $image_parts[0]);


		      $image_type = $image_type_aux[1];

		      $image_base64 = base64_decode($image_parts[1]);

		      $file = $folderPath . $audit_id . '.'.$image_type;
		      file_put_contents($file, $image_base64);

		      $fp = fopen($file, 'rb');
		      //$content = fread($fp, $_FILES['file']['size']);
		      $content = fread($fp, filesize($file));
		      $content = addslashes($content);

		      $filename = addslashes($file);
		      $tmp_new_name=(explode(".", $file));
		      $new_name = $audit_id.'.'.$tmp_new_name[1];

		      fclose($fp);

		      // unlink($file);

		}

			$res = $this->conn->query("INSERT INTO audit_header (audit_id, org_name, dept_name, sect_name, scope_name, reference_no
																														,date_from, date_to, approver_id, crt_by, crt_dd, mod_by, mod_dd, signature, signature_name, audit_score, team_id)
																												VALUES ('$audit_id', '$frm[org_name]', '$frm[dept_name]', '$frm[sect_name]', '$frm[scope_name]', '$frm[reference_no]'
																													,'$frm[date_from]', '$frm[date_to]', '$frm[approver_id]', '$frm[crt_by]', '$frm[crt_dd]', '$trxBy', '$trxDate', '$content', '$new_name', '$frm[audit_score]', '$frm[team_id]')") or die($this->conn->error);

			if (!empty($arr_trail)) {

				foreach($arr_trail AS $issue_id=>$issue_arr) {
						foreach($issue_arr AS $raf_k=>$raf_v) {

								$this->conn->query("INSERT INTO audit_trail(audit_id, issue_id, review, raf_no, raf_score, raf_risk_rating)
														 				VALUES ('$audit_id', '$issue_id', '$raf_v->review', '$raf_k', '$raf_v->raf_score', '$raf_v->raf_risk_rating')") or die($this->conn->error);
						}
				}
			}


		}

	function getAudit($audit_id)
  {
      $res = $this->conn->query("SELECT * FROM audit_header WHERE audit_id = '$audit_id' AND audit_id !=''") or die($this->conn->error);
      if ($res->num_rows > 0) {
          return $res->fetch_array(MYSQLI_ASSOC);
      } else {
          return array();
      }
  }

	function prepareReview($review_list)
  {
      $audit_id = $_GET['audit_id'];
      $i = new ReviewObj();

      if (!empty($audit_id)){
          //add item into object
          $this->getReviewItem($i, $audit_id);
      }
      $_SESSION[$review_list] = $i;

      return $i;
  }

	function addReviewItem(&$audit, $frm) {

			if($frm['raf_no'] == "" || $frm['raf_no'] <=0){
				$number_o = sizeof($audit->getItemsAll());
				$tmp_frm['number_o'] = $number_o + 1;

				$count = 0;
				foreach ($audit->getItemsAll() AS $issue_id=>$issue_arr) {
					foreach($issue_arr AS $k){
				    $count++;
					}
				}

				$tmp_frm['number_o'] = $count + 1;

			}else{
				$tmp_frm['number_o'] = $frm['raf_no'];
			}

			if (!empty($frm)) {

				$tmp_frm['review'] = $frm['new_review'];
				//add to list
				$i = $this->_getReviewItem($tmp_frm);

				//add to object
				$audit->addItemReview($i, $frm['issue_id'], $tmp_frm['number_o']);

			}
					return true;
	}

	private function _getReviewItem($i)
	{
		$list_item = new ReviewObj();
		$list_item->number_o = ($i['number_o']=="") ? 0 : $i['number_o'];
		$list_item->review = (empty($i['review'])) ? "": $i['review'];
		$list_item->raf_score = ($i['raf_score'] == "") ? "": $i['raf_score'];
		$list_item->raf_risk_rating = ($i['raf_risk_rating'] == "") ? "": $i['raf_risk_rating'];

		return $list_item;

	}

	function getReviewItem(&$audit, $audit_id)
	{
		$res = $this->conn->query("SELECT * FROM audit_trail WHERE audit_id = '$audit_id' AND audit_id !=''") or die($this->conn->error);
		if ($res) {
				while ($row = $res->fetch_array(MYSQLI_ASSOC)) {

					$row['number_o'] = $row['raf_no'];
					$row['review'] = $row['review'];
					$row['raf_score'] = $row['raf_score'];
					$row['raf_risk_rating'] = $row['raf_risk_rating'];
					//add to list
					$i = $this->_getReviewItem($row);
					//add to object
					$audit->addItemReview($i, $row['issue_id'], $row['number_o']);

			}
		}
	}
/*
	function checkProduct($product_id)
    {
	   $res = $this->conn->query("SELECT * FROM product WHERE product_id = '$product_id' AND product_id !=''") or die($this->conn->error);
       if(mysql_num_rows($res) > 0){
           return true;
       }else{
           return false;
       }
    }
*/
	function getOrganization()
    {
		$res = $this->conn->query("SELECT DISTINCT org_name FROM ms_organization WHERE org_name != '' ORDER BY org_id") or die($this->conn->error);
        if ($res->num_rows > 0) {
            while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
            return $arr;
        } else {
            return array();
        }

    }

	function getDepartment($org_name)
    {
		$res = $this->conn->query("SELECT dept_name FROM ms_dept WHERE dept_id!='' AND org_name='$org_name' ORDER BY dept_id") or die($this->conn->error);
		if ($res->num_rows > 0) {
				while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                $arr[] = $row;
            }
            return $arr;
        } else {
            return array();
        }

    }

		function getSection($org_name)
	    {
			$res = $this->conn->query("SELECT s.sect_name
																	FROM ms_section s
																	join ms_dept d ON s.dept_name=d.dept_name
																	WHERE s.sect_id!='' AND d.org_name='$org_name' ORDER BY sect_id") or die($this->conn->error);
			if ($res->num_rows > 0) {
					while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
	                $arr[] = $row;
	            }
	            return $arr;
	        } else {
	            return array();
	        }
	    }

			function getScope()
		    {
				$res = $this->conn->query("SELECT scope_name FROM ms_scope ORDER BY scope_id") or die($this->conn->error);
				if ($res->num_rows > 0) {
						while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
		                $arr[] = $row;
		            }
		            return $arr;
		        } else {
		            return array();
		        }
		    }

			function getListIssue()
		  {
				$res = $this->conn->query("SELECT * FROM ms_issue ORDER BY issue_id") or die($this->conn->error);
				if ($res->num_rows > 0) {
						while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
		                $arr[$row['issue_id']] = $row;
		            }
		            return $arr;
		        } else {
		            return array();
		        }
		   }

		   function getListRating()
		  {
				$res = $this->conn->query("SELECT * FROM ms_rating ORDER BY rating_id") or die($this->conn->error);
				if ($res->num_rows > 0) {
						while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
		                $arr[$row['rating_id']] = $row;
		            }
		            return $arr;
		        } else {
		            return array();
		        }
		   }

			function getUser()
		    {
				$res = $this->conn->query("SELECT user_id from ms_user WHERE position = 5 ORDER BY position DESC") or die($this->conn->error);
				if ($res->num_rows > 0) {
						while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
		                $arr[] = $row;
		            }
		            return $arr;
		        } else {
		            return array();
		        }

		    }

				function getTeam($trxBy)
			    {
					$res = $this->conn->query("SELECT ms.team_id from ms_team ms JOIN ms_team_detail md ON ms.id = md.team_ref_id WHERE md.user_id = '$trxBy' ORDER BY ms.team_id") or die($this->conn->error);
					if ($res->num_rows > 0) {
							// while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
			            //     $arr[] = $row;
			            // }
			            // return $arr;
			        // } else {
			        //     return array();
			        // }

							return $res->fetch_object()->team_id;
						}

			    }

				function getPosition($trxBy)
			    {
					$res = $this->conn->query("SELECT position from ms_user WHERE user_id = '$trxBy'") or die($this->conn->error);
					if ($res->num_rows > 0) {
							    $row = $res->fetch_object()->position;
			            return $row;
			        } else {
			            return array();
			        }

			    }
}
?>
