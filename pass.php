<?php
include ('verify.php');
$encryptedpasswd= getpass() ;
$showlogin = true ;
// check cookie 

if (isset($_SESSION['logincheck'])) 
{

	if (md5($encryptedpasswd) == $_SESSION['logincheck'])
	{
	$showlogin = false ;
	}
}
?>
<?php
if (isset($_POST['pass']) )
{
	if (verifyPassword($_POST['pass'])!='right') 
	{ 
	echo ('<center><font color="red">Password incorrect. </font></center>');
	}
	else
	{
	$showlogin= false; // the pass is good
	$_SESSION['logincheck'] = md5($encryptedpasswd);
	}
}
else
{
 ;  // no login attempt
}
if ($showlogin) {
?>
<div class="row">
<div class="col-lg-6">
   <form name="sql-data" method="post" action="<?php $_SERVER['PHP_SELF'] ?>">
      <div class="input-group">
         <span class="input-group-btn">
	    <button class='btn btn-default' type="submit" value="Submit">Submit</button>
         </span>
	    <input class="form-control" type="password" name="pass" value ="" placeholder="Enter your password to continue" maxlength="60" size="30"> 
      </div><!-- /input-group -->
   </form>
</div><!-- /.col-lg-6 -->
</div>
</div>
<?php include ("footer.php");?>
<?php
}
if ($showlogin)  die() ;
?>
<!-- normal continuation of page here.... -->
