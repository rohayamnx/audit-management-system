<?php

class ReviewObj {

	private $number_o = 0;
	private $review = '';
	private $raf_score = '';
	private $raf_risk_rating = '';


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

	public function addItemReview($review, $issue_id, $number_o)
    {

			$this->items_list[$issue_id][$number_o] = $review;
    }

	public function getItems($issue_id)
    {
    	return $this->items_list[$issue_id];

    }

	public function getItemsRaf($issue_id, $raf_no)
    {
    	return $this->items_list[$issue_id][$raf_no];

    }

		public function getItemsAll()
	    {
	    	return $this->items_list;

	    }

    public function getItemCount($issue_id)
    {
    	return count($this->items_list[$issue_id]);

    }

		public function getItemCountAll()
    {
    	return count($this->items_list);

    }

		public function updItemAll($frm)
    {
				$this->updItem($frm['raf_score'], 'raf_score');
				$this->updItem($frm['raf_risk_rating'], 'raf_risk_rating');


    }

	public function updItem($tmp_array, $field_type)
    {

        $bpv = $this->items_list;
        foreach ($tmp_array AS $k=>$v){

						$tmp_key_arr = explode("|", $k);

						$issue_id = $tmp_key_arr[0];
						$raf_no = $tmp_key_arr[1];

            $bpv[$issue_id][$raf_no]->$field_type = (empty($v)) ? "" : $v;

        }

    }

	public function deleteItem($issue_id, $raf_no)
    {



			$audit = $this->items_list;
    	$tmp = array();

    	foreach($audit AS $issue_k=>$issue_arr){
				foreach($issue_arr AS $k_raf=>$o){
						// echo $issue_k.' '.$issue_id.' '.$k_raf.' '.$raf_no;
	    		if (($issue_k!=$issue_id ) || ($k_raf != $raf_no)){

						// echo " = yes";

	    			$tmp[$issue_k][$k_raf] = $o;

	    		}
					// echo '<br>';
				}
    	}
// exit();
// 			echo '<pre>';
// 			print_r($tmp);
// 			echo '</pre>';

    	$this->items_list = $tmp;

    }

}

?>
