<?php
require_once("audit.inc.php");
require_once("global.php");
$conn = $_SESSION['conn'];
ob_start();
// echo "SELECT count(*) FROM audit_trail WHERE audit_id = '$_GET[audit_id]'";
// exit();
require_once('fpdi/tfpdf.php');

$audit = new AuditModel();

$rating_arr = $audit->getListRating();

$res = $conn->query("SELECT count(*) FROM audit_trail WHERE audit_id = '$_GET[audit_id]'") or die($conn->error);
$total_row = $res->num_rows;

class FPDF extends tFPDF{

	protected $_tplIdx;

	function Code39($xpos, $ypos, $code, $baseline=0.5, $height=5){
			$wide = $baseline;
			$narrow = $baseline / 3 ;
			$gap = $narrow;

			$barChar['0'] = 'nnnwwnwnn';
			$barChar['1'] = 'wnnwnnnnw';
			$barChar['2'] = 'nnwwnnnnw';
			$barChar['3'] = 'wnwwnnnnn';
			$barChar['4'] = 'nnnwwnnnw';
			$barChar['5'] = 'wnnwwnnnn';
			$barChar['6'] = 'nnwwwnnnn';
			$barChar['7'] = 'nnnwnnwnw';
			$barChar['8'] = 'wnnwnnwnn';
			$barChar['9'] = 'nnwwnnwnn';
			$barChar['A'] = 'wnnnnwnnw';
			$barChar['B'] = 'nnwnnwnnw';
			$barChar['C'] = 'wnwnnwnnn';
			$barChar['D'] = 'nnnnwwnnw';
			$barChar['E'] = 'wnnnwwnnn';
			$barChar['F'] = 'nnwnwwnnn';
			$barChar['G'] = 'nnnnnwwnw';
			$barChar['H'] = 'wnnnnwwnn';
			$barChar['I'] = 'nnwnnwwnn';
			$barChar['J'] = 'nnnnwwwnn';
			$barChar['K'] = 'wnnnnnnww';
			$barChar['L'] = 'nnwnnnnww';
			$barChar['M'] = 'wnwnnnnwn';
			$barChar['N'] = 'nnnnwnnww';
			$barChar['O'] = 'wnnnwnnwn';
			$barChar['P'] = 'nnwnwnnwn';
			$barChar['Q'] = 'nnnnnnwww';
			$barChar['R'] = 'wnnnnnwwn';
			$barChar['S'] = 'nnwnnnwwn';
			$barChar['T'] = 'nnnnwnwwn';
			$barChar['U'] = 'wwnnnnnnw';
			$barChar['V'] = 'nwwnnnnnw';
			$barChar['W'] = 'wwwnnnnnn';
			$barChar['X'] = 'nwnnwnnnw';
			$barChar['Y'] = 'wwnnwnnnn';
			$barChar['Z'] = 'nwwnwnnnn';
			$barChar['-'] = 'nwnnnnwnw';
			$barChar['.'] = 'wwnnnnwnn';
			$barChar[' '] = 'nwwnnnwnn';
			$barChar['*'] = 'nwnnwnwnn';
			$barChar['$'] = 'nwnwnwnnn';
			$barChar['/'] = 'nwnwnnnwn';
			$barChar['+'] = 'nwnnnwnwn';
			$barChar['%'] = 'nnnwnwnwn';

			$this->SetFont('Arial','',10);
			//$this->Text($xpos, $ypos + $height + 4, $code);
			$this->SetFillColor(0);

			$code = '*'.strtoupper($code).'*';
			for($i=0; $i<strlen($code); $i++){
				$char = $code[$i];
				if(!isset($barChar[$char])){
					$this->Error('Invalid character in barcode: '.$char);
				}
				$seq = $barChar[$char];
				for($bar=0; $bar<9; $bar++){
					if($seq[$bar] == 'n'){
						$lineWidth = $narrow;
					}else{
						$lineWidth = $wide;
					}
					if($bar % 2 == 0){
						$this->Rect($xpos, $ypos, $lineWidth, $height, 'F');
					}
					$xpos += $lineWidth;
				}
				$xpos += $gap;
			}
   }
	 function MultiCell2($w, $h, $txt, $border=0, $align='J', $fill=false)
	 {
		 // Output text with automatic or explicit line breaks
		 $cw = &$this->CurrentFont['cw'];
		 if($w==0)
			 $w = $this->w-$this->rMargin-$this->x;
		 $wmax = ($w-2*$this->cMargin);

		 $txt = trim($txt);
		 $s = str_replace("\r",'',$txt);
		 if ($this->unifontSubset) {
			 $nb=mb_strlen($s, 'utf-8');
			 while($nb>0 && mb_substr($s,$nb-1,1,'utf-8')=="\n")	$nb--;
		 }
		 else {
			 $nb = strlen($s);
			 if($nb>0 && $s[$nb-1]=="\n")
				 $nb--;
		 }
		 $b = 0;
		 if($border)
		 {
			 if($border==1)
			 {
				 $border = 'LTRB';
				 $b = 'LRT';
				 $b2 = 'LR';
			 }
			 else
			 {
				 $b2 = '';
				 if(strpos($border,'L')!==false)
					 $b2 .= 'L';
				 if(strpos($border,'R')!==false)
					 $b2 .= 'R';
				 $b = (strpos($border,'T')!==false) ? $b2.'T' : $b2;
			 }
		 }
		 $sep = -1;
		 $i = 0;
		 $j = 0;
		 $l = 0;
		 $ns = 0;
		 $nl = 1;


		 while($i<$nb)
		 {
			 // Get next character
			 if ($this->unifontSubset) {
				 $c = mb_substr($s,$i,1,'UTF-8');
			 }
			 else {
				 $c=$s[$i];
			 }
			 if($c=="\n")
			 {
				 // Explicit line break
				 if($this->ws>0)
				 {
					 $this->ws = 0;
					 $this->_out('0 Tw');
				 }
				 if ($this->unifontSubset) {
					 $this->Cell($w,$h,mb_substr($s,$j,$i-$j,'UTF-8'),$b,2,$align,$fill);
				 }
				 else {
					 $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
				 }
				 $i++;
				 $sep = -1;
				 $j = $i;
				 $l = 0;
				 $ns = 0;
				 $nl++;
				 if($border && $nl==2)
					 $b = $b2;
				 continue;
			 }
			 if($c==' ')
			 {
				 $sep = $i;
				 $ls = $l;
				 $ns++;
			 }

			 if ($this->unifontSubset) { $l += $this->GetStringWidth($c); }
			 else { $l += $cw[$c]*$this->FontSize/1000; }

			 if($l>$wmax)
			 {
				 // Automatic line break
				 if($sep==-1)
				 {
					 if($i==$j)
						 $i++;
					 if($this->ws>0)
					 {
						 $this->ws = 0;
						 $this->_out('0 Tw');
					 }
					 if ($this->unifontSubset) {
						 $this->Cell($w,$h,mb_substr($s,$j,$i-$j,'UTF-8'),$b,2,$align,$fill);
					 }
					 else {
						 $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
					 }
				 }
				 else
				 {
					 if($align=='J')
					 {
						 $this->ws = ($ns>1) ? ($wmax-$ls)/($ns-1) : 0;
						 $this->_out(sprintf('%.3F Tw',$this->ws*$this->k));
					 }
					 if ($this->unifontSubset) {
						 $this->Cell($w,$h,mb_substr($s,$j,$sep-$j,'UTF-8'),$b,2,$align,$fill);
					 }
					 else {
						 $this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
					 }
					 $i = $sep+1;
				 }
				 $sep = -1;
				 $j = $i;
				 $l = 0;
				 $ns = 0;
				 $nl++;
				 if($border && $nl==2)
					 $b = $b2;
			 }
			 else
				 $i++;
		 }

		 // echo "nb = ".$nb.' no = '.$nl.'<br>';
		 // Last chunk
		 if($this->ws>0)
		 {
			 $this->ws = 0;
			 $this->_out('0 Tw');
		 }


		 if($border && strpos($border,'B')!==false)
			 $b .= 'B';
		 if ($this->unifontSubset) {
			 $this->Cell($w,$h,mb_substr($s,$j,$i-$j,'UTF-8'),$b,2,$align,$fill);
		 }
		 else {
			 $this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
		 }
		 $this->x = $this->lMargin;
		 return $nl;
	 }
	 public function multipleImagebk($rating_arr, $avg_score_issue){

			 $image1 = "images/star_yellow.png";
			 // $pdf->Cell(28,$mult_h_issue,$pdf->Image($image1,$pdf->GetX(),$pdf->GetY(),3),1,0,'C',0);

			 foreach($rating_arr AS $rating_id=>$rating_v){
				 if(($avg_score_issue >= $rating_v['score_min']) && ($avg_score_issue <=$rating_v['score_max'])){
					 $var_num2 = 0;

					 // echo $rating_v['total_star'].'<br>';

					 if($rating_v['total_star'] == 1){
							 $var_cell = 28;
							 $zz = 28/2;
					 }elseif($rating_v['total_star'] == 2){
							 $var_cell = 14;
							 $starting = 8;
							 $zz = 2;
					 }elseif($rating_v['total_star'] == 3){
							 $var_cell = 14;
							 $starting = 6;
							 $zz = 2;
					 }else{
							 $var_cell = 5;
							 $zz = 2;
					 }
					 for($rc=1;$rc<=$rating_v['total_star'];$rc++){
							 $image1 = "images/star_".$rating_v['star_name'].".png";

							 // $pdf->Image($image1, $pdf->GetX()+$var_num, $pdf->GetY(),3).' ';

							 if($rc != 1){
								 $starting = 0;
							 }

							 // echo $pdf->GetX().'<br>';
							 return $this->Cell($var_cell,$mult_h_issue,$this->Image($image1, $this->GetX()+$starting+$zz, $this->GetY(),3),0,0,'C',0);
							 // $pdf->Ln(20);
							 // $var_num1 += 5;
							 $var_num2 += 3;
					 }



				 }
			 }

			 // $this->Cell(28,190,$this->Image($image1, $this->GetX()+$starting+$zz, $this->GetY(),3),0,0,'C',0);
	 }

	 public function multipleImage($rating_arr, $avg_score_issue){

			 $image1 = "images/star_yellow.png";
			 // $pdf->Cell(28,$mult_h_issue,$pdf->Image($image1,$pdf->GetX(),$pdf->GetY(),3),1,0,'C',0);

			 foreach($rating_arr AS $rating_id=>$rating_v){
				 if(($avg_score_issue >= $rating_v['score_min']) && ($avg_score_issue <=$rating_v['score_max'])){
					 $var_num2 = 0;

					 // echo $rating_v['total_star'].'<br>';

					 if($rating_v['total_star'] == 1){
							 $starting = 12;
					 }elseif($rating_v['total_star'] == 2){
							 // $var_cell = 14;
							 $starting = 10;
							 $zz = 2;
					 }elseif($rating_v['total_star'] == 3){
							 // $var_cell = 14;
							 $starting = 8;
							 $zz = 2;
					 }elseif($rating_v['total_star'] == 4){
							 // $var_cell = 14;
							 $starting = 4;
							 $zz = 2;
					 }else{
							 // $var_cell = 5;
							 $starting = 2;
							 $zz = 2;
					 }

					 // $starting = 2;

					 // echo $rating_v['total_star'].'<br>';
					 $var_num2 = 5;
					 $zz = 2;
					 for($rc=1;$rc<=$rating_v['total_star'];$rc++){
							 $image1 = "images/star_".$rating_v['star_name'].".png";

							 // $pdf->Image($image1, $pdf->GetX()+$var_num, $pdf->GetY(),3).' ';

							 if($rc == 1){
								 // $starting = 0;
								 $var_num2 = 0;
							 }

							 // echo $pdf->GetX().'<br>';
							 $this->Image($image1, $this->GetX()+$starting+$var_num2, $this->GetY()+1,3);
							 // $pdf->Ln(20);
							 // $var_num1 += 5;
							 $var_num2 += 5;
					 }



				 }
			 }

			 // $this->Cell(28,190,$this->Image($image1, $this->GetX()+$starting+$zz, $this->GetY(),3),0,0,'C',0);
	 }


	public function Header(){
		$conn = $_SESSION['conn'];
		// $pono = $_GET['pono'];

		if (is_null($this->_tplIdx)) {
			$this->setSourceFile("fpdi/PDF/bg.pdf");
			$this->_tplIdx = $this->importPage(1);
		}

		$size = $this->useTemplate($this->_tplIdx, 0, 0, 210);
		$this->SetTextColor(0);


		$res = $conn->query("SELECT * FROM audit_header WHERE audit_id = '$_GET[audit_id]'");

		if($res->num_rows>0){
			$arr = $res->fetch_array(MYSQLI_ASSOC);
			$organization = $arr['org_name'];
			$dept = $arr['dept_name'];
			$section = $arr['sect_name'];

		}

		// Landscape 300, Potrait 203

		$this->SetFont('Arial','B',14);
		$this->setFillColor(230,230,230);
		$this->SetXY(3,3); //Y = set for margin top/bottom  X = right/left
		$this->Cell(290, 5,'AUDIT FINDINGS REPORT',0,0,'C',1);
		//$this->Cell(80, 5,$principle_name,1,0,'L',0);

		$this->SetFont('Arial', 'B', 10);
		$this->SetXY(3,10);
		$this->Cell(18, 10,'BUSINESS OBLIGE FUNCTION :',0,1,'L',0);
		$this->SetXY(60,10);
		$this->Cell(18, 10,$arr['org_name'],0,1,'L',0);

		$this->SetXY(3,16);
		$this->Cell(18, 10,'DEPARTMENT : ',0,1,'L',0);
		$this->SetXY(60,16);
		$this->Cell(18, 10,$arr['dept_name'],0,1,'L',0);

		$this->SetXY(3,22);
		$this->Cell(18, 10,'UNIT : ',0,1,'L',0);
		$this->SetXY(60,22);
		$this->Cell(18, 10,$arr['sect_name'],0,1,'L',0);


		$this->SetXY(3,32);
		$this->setFillColor(230,230,230);
		$this->Cell(290, 5,'AUDIT FINDINGS DETAILS',1,1,'C',1);

		$this->SetXY(3,37);
		$this->Cell(60, 5,'Audit Conducted On :',1,1,'L',1);

		$this->SetXY(63,37);
		$this->Cell(100, 5,$arr['date_from'].' to '.$arr['date_to'],1,1,'L',0);

		$this->SetXY(163,37);
		$this->Cell(30, 5,'Conducted By :',1,1,'L',1);

		$this->SetXY(193,37);
		$this->Cell(100, 5,$arr['crt_by'],1,1,'L',0);

		$this->SetXY(3,42);
		$this->Cell(60, 5,'Report Issued On :',1,1,'L',1);

		$this->SetXY(63,42);
		$this->Cell(100, 5,$arr['crt_dd'],1,1,'L',0);

		$this->SetXY(163,42);
		$this->Cell(30, 5,'Audit Scope :',1,1,'L',1);

		$this->SetXY(193,42);
		$this->Cell(100, 5,$arr['scope_name'],1,1,'L',0);


	}

}

$total_page=$total_row/20;
$total_page=ceil($total_page);

require_once('fpdi/fpdi.php');
$pdf = new FPDI('L','mm','A4');

$pdf->SetAutoPageBreak(false);

//$pdf = new FPDF();
$pdf->AddPage();
$page = $page + 1;


// $pdf->Cell(68, 10,'Sheet:		'.$page.'		of		'.$total_page,1,1,'R',0);

$pdf->SetXY(225,22);
$pdf->Cell(68, 10,'Page:		'.$page,0,1,'R',0);


// $pdf->SetFont('Arial', 'B', 8);
// $pdf->SetXY(3,20);
// $pdf->Cell($a, 10,'No',1,0,'C',0);
// $pdf->Cell($b, 10,'Product',1,0,'C',0);
// $pdf->Cell($c, 10,'Category',1,0,'C',0);
// $pdf->Cell($d, 10,'Person In-Charge',1,0,'C',0);

$y_coordinate = 40;
//max size follow add_size


$res_issue = $conn->query("SELECT issue_id, issue_name FROM ms_issue ORDER BY issue_id LIMIT 30");
if($res_issue->num_rows > 0){
	while($row_issue = $res_issue->fetch_array(MYSQLI_ASSOC)){
			$issue_arr[$row_issue['issue_id']] = $row_issue['issue_name'];
	}
}

$pdf->SetXY(154,3);

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetXY(3,50);
$pdf->setFillColor(230,230,230);
$pdf->Cell(290, 5,'Audit Trail - ',1,1,'C',1);

// $pdf->SetFont('Arial', '', 10);
// $pdf->SetXY(3,55);
// $pdf->Cell(200, 5,'The following documentation was reviewed -',0,1,'L',0);

$pdf->SetFont('Arial', '', 10);
$pdf->SetXY(3,55);
$pdf->Cell(7, 10,'No',1,1,'L',1);

$pdf->SetXY(10,55);
$pdf->Cell(30, 10,'Isu',1,1,'L',1);

$pdf->SetXY(40,55);
$pdf->Cell(130, 10,'RAF',1,1,'L',1);

$pdf->SetXY(170,55);
$pdf->Cell(40, 10,'Penarafan Risiko',1,1,'L',1);

$pdf->SetXY(210,55);
$pdf->Cell(15, 5,'Skor/',1,1,'L',1);
$pdf->SetXY(210,60);
$pdf->Cell(15, 5,'100',1,1,'L',1);

$pdf->SetXY(225,55);
$pdf->Cell(15, 5,'Jumlah',1,1,'L',1);
$pdf->SetXY(225,60);
$pdf->Cell(15, 5,'Skor',1,1,'L',1);

$pdf->SetXY(240,55);
$pdf->Cell(25, 5,'Jumlah Skor/',1,1,'L',1);
$pdf->SetXY(240,60);
$pdf->Cell(25, 5,'Jumlah Raf',1,1,'L',1);

$pdf->SetXY(265,55);
$pdf->Cell(28, 10,'Star Rating',1,1,'L',1);


$y_start = 65+$plus2+2;

$total_p_max = 170;
$total_row = 0;

$cnt = 1;
$tot_score_audit = 0;
foreach($issue_arr AS $issue=>$v_issue){

$score_per_issue = 0;
$mult_h_issue = 0;
$tot_issue_id = 0;
$avg_score_issue = 0;
$res_detail = $conn->query("SELECT a.*, at.* FROM audit_header a JOIN audit_trail at ON a.audit_id = at.audit_id WHERE a.audit_id = '$_GET[audit_id]' AND issue_id = '$issue' ORDER BY at.issue_id, at.raf_no") or die($conn->error);
// echo "SELECT a.*, at.* FROM audit_header a JOIN audit_trail at ON a.audit_id = at.audit_id WHERE a.audit_id = '$_GET[audit_id]' AND issue_id = '$issue' ORDER BY at.issue_id, at.raf_no <br>";

if($res_detail->num_rows > 0){

	// if ($total_row >= $total_p_max) {
	//
	// }

	while($row = $res_detail->fetch_array(MYSQLI_ASSOC)){

		/********** END TO GET TOTAL ROWS ****************/

		if ($total_row >= $total_p_max) {

			// echo $total_row.' '.$total_p_max;

			$add_size = 0;
			$mult_h = 0;
			$y_start = 65+$plus2+2;

			$mult_h_issue = 0;

			$pdf->AddPage();
			$page = $page + 1;

			$pdf->SetFont('Arial','B',10);

			$pdf->SetXY(225,22);
			$pdf->Cell(68, 10,'Page:		'.$page,0,1,'R',0);

			$pdf->SetXY(154,3);

			$pdf->SetFont('Arial', 'B', 10);

			$pdf->SetXY(3,50);
			$pdf->setFillColor(230,230,230);
			$pdf->Cell(290, 5,'Audit Trail - ',1,1,'C',1);

			$pdf->SetFont('Arial', '', 10);
			$pdf->SetXY(3,55);
			$pdf->Cell(7, 10,'No',1,1,'L',1);

			$pdf->SetXY(10,55);
			$pdf->Cell(30, 10,'Isu',1,1,'L',1);

			$pdf->SetXY(40,55);
			$pdf->Cell(130, 10,'RAF',1,1,'L',1);

			$pdf->SetXY(170,55);
			$pdf->Cell(40, 10,'Penarafan Risiko',1,1,'L',1);

			$pdf->SetXY(210,55);
			$pdf->Cell(15, 5,'Skor/',1,1,'L',1);
			$pdf->SetXY(210,60);
			$pdf->Cell(15, 5,'100',1,1,'L',1);

			$pdf->SetXY(225,55);
			$pdf->Cell(15, 5,'Jumlah',1,1,'L',1);
			$pdf->SetXY(225,60);
			$pdf->Cell(15, 5,'Skor',1,1,'L',1);

			$pdf->SetXY(240,55);
			$pdf->Cell(25, 5,'Jumlah Skor/',1,1,'L',1);
			$pdf->SetXY(240,60);
			$pdf->Cell(25, 5,'Jumlah Raf',1,1,'L',1);

			$pdf->SetXY(265,55);
			$pdf->Cell(28, 10,'Star Rating',1,1,'L',1);

		}

		 $pdf->SetFont('Arial', '', 9);


			$row['review'] = trim($row['review']);

			$cnt_length = strlen($row['review']);

			// echo $row['raf_no'].' '.$cnt_length.'<br>';

			$mult_h = 0;
			do{
				$row_height = 5;
				$cnt_length = $cnt_length-100;
				// $mult_h += 5;
			}while($cnt_length > 100);

// echo 'issue = '.$v_issue.' start = '.$y_start;

// echo $cnt.' mult h = '.$mult_h.' next row = '.$next_row_mult.' + 5 ='.$y_start.'<br>';

// echo $row['raf_no'].' start = '.$y_start.' '.$mult_h.'<br>';

			$pdf->SetXY(40,$y_start);

			$mult_h = $pdf->MultiCell2(130, $row_height,$row['review'],1,'L',0,100);
			$mult_h = $mult_h * 5;

			$res_rating = $conn->query("SELECT * FROM ms_rating WHERE rating_id = '$row[raf_risk_rating]'") or die($conn->error);
			$row_rating = $res_rating->fetch_array(MYSQLI_ASSOC);

			$pdf->SetXY(170,$y_start);
			$pdf->Cell(40, ($mult_h),$row_rating['risk_rating'],1,1,'C',0);

			$pdf->SetXY(210,$y_start);
			$pdf->Cell(15, ($mult_h),$row['raf_score'],1,1,'C',0);

			$score_per_issue += $row['raf_score'];

			$y_start = $y_start + $mult_h;
			$total_row = $y_start;

			$mult_h_issue += $mult_h;

			if ($total_row >= $total_p_max) {
				$pdf->SetXY(3,$y_start-$mult_h_issue);
				$pdf->Cell(7, $mult_h_issue,$cnt,1,0,'L',0);

				$pdf->SetXY(10,$y_start-$mult_h_issue);
				$pdf->Cell(30,$mult_h_issue,$v_issue,1,0,'L',0);

				$pdf->SetXY(225,$y_start-$mult_h_issue);
				$pdf->Cell(15, $mult_h_issue,'',1,1,'L',0);

				$pdf->SetXY(240,$y_start-$mult_h_issue);
				$pdf->Cell(25,$mult_h_issue,'',1,1,'L',0);

				$pdf->SetXY(265,$y_start-$mult_h_issue);
				$pdf->Cell(28, $mult_h_issue,'',1,1,'L',0);
			}

			$tot_issue_id++;
	}


}else{

	$pdf->SetXY(40,$y_start);
	$pdf->Cell(130, 5,'N/A',1,1,'C',0);

	$pdf->SetXY(170,$y_start);
	$pdf->Cell(40, 5,'N/A',1,1,'C',0);

	$pdf->SetXY(210,$y_start);
	$pdf->Cell(15,5,'N/A',1,1,'C',0);

	$score_per_issue = 0;
	$tot_issue_id = 0;


	$y_start = $y_start + 5;
	$total_row = $y_start;

	$mult_h_issue += 5;
}

	$pdf->SetXY(3,$y_start-$mult_h_issue);
	$pdf->Cell(7, $mult_h_issue,$cnt,1,1,'L',0);

	$pdf->SetXY(10,$y_start-$mult_h_issue);
	$pdf->Cell(30,$mult_h_issue,$v_issue,1,0,'L',0);

	// echo $issue_id.' '.$score_per_issue.' '.$tot_issue_id.'<br>';

	$avg_score_issue = number_format($score_per_issue/$tot_issue_id,2, '.', '');

	if($score_per_issue == 0){
		$score_per_issue = "N/A";
	}

	if(!is_numeric($score_per_issue)){
		$avg_score_issue = "100.00";
	}

	$pdf->SetXY(225,$y_start-$mult_h_issue);
	$pdf->Cell(15,$mult_h_issue,$score_per_issue,1,1,'C',0);

	$pdf->SetXY(240,$y_start-$mult_h_issue);
	$pdf->Cell(25,$mult_h_issue,$avg_score_issue,1,1,'C',0);

  $pdf->SetXY(265,$y_start-$mult_h_issue);
	$pdf->Cell(28,$mult_h_issue,$pdf->multipleImage($rating_arr, $avg_score_issue),1,0,'C',0);

	$tot_score_audit += $avg_score_issue;


	$cnt++;
}
$tot_all = 0;

// echo $tot_score_audit.' '.($cnt-1);
$tot_all = number_format($tot_score_audit / ($cnt-1),2, '.', '');

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetXY(3,$y_start);
$pdf->Cell(237,5,'Jumlah Keseluruhan',1,1,'L',0);

$pdf->SetXY(240,$y_start);
$pdf->Cell(25,5,$tot_all,1,1,'C',0);

$tot_all = intval($tot_all);
$pdf->SetXY(265,$y_start);
$pdf->Cell(28,5,$pdf->multipleImage($rating_arr, $tot_all),1,0,'C',0);

$res = $conn->query("SELECT approved_by, signature, signature_name FROM audit_header WHERE audit_id = '$_GET[audit_id]'");
if($res->num_rows>0){
	$row_sig = $res->fetch_array(MYSQLI_ASSOC);
	$sig_app = $row_sig['approved_by'];
	$sig_img = $row_sig['signature'];
	$sig_name = $row_sig['signature_name'];

}

$pdf->SetXY(240,$y_start+15);
$pdf->Cell(20,5,'Signature by',0,1,'L',0);


$pdf->SetFont('Arial', 'I', 9);
$pdf->SetXY(263,$y_start+15);
$pdf->Cell(10,5,'('.$sig_app.')',0,1,'L',0);

$image2 = "img_signature/$sig_name";
$pdf->Image($image2, $pdf->GetX()+230, $pdf->GetY(),50);
// $this->Image($image1, $this->GetX()+$starting+$var_num2, $this->GetY()+1,3);


$pdf->Output();
// unlink($tmp_loc);

ob_end_flush();
?>
