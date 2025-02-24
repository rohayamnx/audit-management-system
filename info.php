<?php 
require_once("global.php");
error_reporting(E_ERROR | E_PARSE);

session_start();

$module_name = '';  
$script_name = 'MAIN MENU';

//doValidateUserAccess($script_name,$module_name);

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

<div class="panel panel-default">
<div class="panel-body">
1) Company<br><br>

<table class="table">
<tr><td>ACTION</td><td>PRINCIPLE</td><td>NON-PRINCIPLE</td></tr>
<tr>
<td>ADD NEW/ UPDATE</td>
<td>

1) Create new company with "tick" <i class="fa fa-check"></i> "is principle?"<br>
2) After you create this principle, you can add new product under this principle. Cannot create new product w/out principle<br>
3) In page company update:
	- User will see "Add New Product" instead of "Assign New Product"<br>
	- Can direct create new product under principle<br>
	- Can direct create new contact person<br>
	- Can direct assign product/contact person<br>
</td>
<td>
1) Create new company with "untick" <i class="fa fa-check"></i> "is principle?"<br>
2) After you create this company, you need to assign new product from principle first before assign contact person<br>	
3) In page company update:
	- User will see "Assign New Product" instead of "Add New Product" <br>
	- Can direct assign new product from principle company<br>
	- Can direct create new contact person<br>
	- Can direct assign product/contact person<br>
</td>
</tr>

<tr>
<td>DELETE</td>
<td>

1) Not affect to main product record under this principle. But, the product only display as without "principle id"<br>
2) User will see "unknown principle" on certain page or report because the principle id for that product already deleted.<br>
3) In page company update <br>
	- Under list of product, User will see "Delete" button.<br>
	  Means it will delete product from principle and all relation to product. etc: relation between product->company, product->contact person<br>
	- Under list of contact person, , User will see "Delete" button. <br>
	  Means can direct delete contact/staff under that company. All details about staff and relation with product also will be deleted	  
</td>
<td>
1) Not affect to main product because product is not under non-principle company. It's only delete relation between product->company, product->contact person<br>
2) User will see "unknown company" on contact person list or report because the company id for that contact person already deleted.<br>
3) In page company update <br>
	- Under list of product, User will see "Delete" button.<br>
	  Main product is not deleted. It only delete relation etc: relation between product->company, product->contact person<br>
	- Under list of contact person, , User will see "Delete" button. <br>
	  Means can direct delete contact/staff under that company. All details about staff and relation with product also will be deleted<br>	  

</td>
</tr>
</table>

</div>
</div>
</div>
<?php include('footer.php'); ?>
</div>
</body>

</html>