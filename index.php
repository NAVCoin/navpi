<?php
include ("header.php");
include ("pass.php");

$updateFile = file_get_contents ('./update.json');
$updateData = json_decode($updateFile, TRUE);
if ($updateData) {
	switch($updateData["code"]) {
		case "UPDATE_INSTALLED":
			echo "<br><p class='bg-success'><b>NOTICE: A new version of NavCoin has been installed. Click here to Apply the update and restart your device <a href='/applyupdate.php?updated=true' class='btn btn-default' style='margin:0;'>Apply Update</a></b></p>";
			break;
		default:
			echo "<br><p class='bg-danger'><b>NOTICE: Something went wrong trying to update the NavCoin daemon. Click here to dismiss this message and restart your device <a href='/applyupdate.php?updated=false' class='btn btn-default' style='margin:0;'>Dismiss Notice</a>.</b></p>";
			echo ('<pre>');
			print_r ($updateData);
			echo ('</pre>');
	}
}

$info = $coin->getinfo();
$y = array_reverse($info);
$bal1 = $coin->getbalance();
$bal2 = $coin->getbalance("*", 0);
$bal3 = abs($bal1 - $bal2);
$bal4 = abs("{$y['stake']}");
if($primary!=""){
	$address = $primary;
}
else{
        $address = $coin->getaddressesbyaccount("")[0];
}

if ($currentWallet == NavCoin){
	$stakinginfo = $coin->getstakinginfo();
	$stakereport = $coin->getstakereport();
	$x = array_reverse($stakinginfo);
	$time = $x['expectedtime'];
}

	$days = floor($time / 86400);
	$hours = floor(($time / 3600) % 24);
	$minutes = floor(($time / 60) % 60);
	$fiatValue = ($bal1 * $priceUsd);
	$fiatValue = sprintf("%01.2f", $fiatValue);
	$btcValue = ($bal1 * $priceBtc);
	$btcValue = sprintf("%01.8f", $btcValue);
	$img = shell_exec("qrencode --output=- -l H -d 144 -s 50 -m 1 $address"); $imgData = "data:image/png;base64," . base64_encode($img);
	$fiatValue = number_format($fiatValue);
?>
<div class="row">
	<div class="col-lg-6">
		<h3>Available Balance: <font color='green'><?php echo number_format($bal1); ?></font> <?php echo $currentWallet; ?></h3>
		<h4>Unavailable Due To Staking: <font color='red'><?php echo $bal4; ?></font> <?php echo $currentWallet; ?></h4>
		<h4>BTC Value: <font color='green'><?php echo "{$btcValue}"; ?></font></h4>
		<h4><?php echo $longCurrency; ?> Value: <font color='green'><?php echo "{$symbol}{$fiatValue}"; ?></font></h4><br>
		<div class="col-lg-8">
    	<form action="lockcontrol">
       		<button class='btn btn-default btn-block'>Your Wallet Is <?php print($lockState)?> Click To Change</button>
    	</form>
		</div>
	</div>
	<div class="col-lg-3">
		<?php if (isset($_POST['show'])){
			$privKey = $coin->dumpprivkey($address);
			$privKeyImg = shell_exec("qrencode --output=- -l H -d 144 -s 50 -m 1 $privKey");
			$privKeyImgData = "data:image/png;base64," . base64_encode($privKeyImg);
		?>
		<center><img class="emrQRCode" src="<?=$privKeyImgData?>"height="30%" />
		<h4>Private Key</h4></center>
		<?php
		}
		else {
		?>
		<center><h4>In order to</h4>
			<form name="sql-data" method="post" action="<?php $_SERVER['PHP_SELF']?>">
				<button class='btn btn-default' type="submit" name="show" value="show">Show private key</button>
			</form>
		<h4>Wallet must be unlocked for sending, or not encrypted.</h4></center>
		<?php
		}
		?>
	</div>
	<div class="col-lg-3">
		<center><img class="emrQRCode" src="<?=$imgData?>"height="30%" />
		<h4>Public Key</h4></center>
	</div>
</div>
</div>
<div class="well">
	<div class="row">
		<div class="col-lg-5">
			<?php
			$configPath = "/home/stakebox/.navcoin4/navcoin.conf";
			$configFile = file_get_contents($configPath);

			if (strpos($configFile, "stakervote=1")) {
				$vote = "YES";
			} else if (strpos($configFile, "stakervote=0")) {
				$vote = "NO";
			} else {
				$vote = "NOT_VOTED";
			}

			?>
			<h4>Network Vote</h4>
			<p>
				The Nav Coin Network is currently voting on introducing changes on the consensus protocol. As a participant in our network, we value your input and the decision ultimately is yours. Please cast your vote. For more information on the proposal, please visit <a href="https://navcoin.org/community-fund" style="color:black;text-decoration:underline;">this link</a>
			</p>
			<p>
				<?php if($vote === 'NOT_VOTED') { ?>
					<b>You have not cast your vote yet.</b>
				<?php } else { ?>
					Currently your vote is set to <b><?php echo ucfirst(strtolower($vote)); ?></b>. If you want to change it, use the form below.
				<?php } ?>
			</p>
			<p>
				Would you like the Nav Coin Network to update the staking rewards to fund a decentralised community fund to help grow the network?
			</p>
			<form name="sql-data" method="post" action="/updatevote">
				<div>
					<label for="voteYes">Yes</label>
					<input type="radio" name="vote" value="YES" id="voteYes" <?php if($vote == "YES") echo "checked"; ?> />
				</div>
				<div>
					<label for="voteNo">No</label>
					<input type="radio" name="vote" value="NO" id="voteNo" <?php if($vote == "NO") echo "checked"; ?> />
				</div>
				<button class='btn btn-default' type="submit" name="submitVote" value="vote">Set Vote</button>
			</form>
		</div>
	</div>
</div>
<div class="well">
<div class="row">
	<div class="col-lg-7">
	<p> Your main wallet address is: <input type="text" name="main_wallet_address" value="<?php print_r($address); ?>" onClick="this.setSelectionRange(0, this.value.length);" size="48" readonly />
	<p>The network is currently on block <?php print_r($coin->getblockcount()); ?>.
	<?php if ($currentWallet == NavCoin): ?>
		<?php echo "<p><b>Stake report</b></p><p>Last 24h: {$stakereport['Last 24H']} NAV</p><p>Last 7d: {$stakereport['Last 7 Days']} NAV</p><p>Last 30d: {$stakereport['Last 30 Days']} NAV</p><p>Last 365d: {$stakereport['Last 365 Days']} NAV</p>" ?>
	<?php endif; ?>
	<?php if ($currentWallet == Philosopherstone): ?>
		<?php echo "<p>Your stake weight is {$x['stakeweight']}.</p>"?>
	<?php endif; ?>
        <?php if ($currentWallet == NavCoin): ?>
		<?php
		echo "<p>Your estimated time to earn rewards is ";
		if ($days) echo $days . " day" . (($days > 1)?"s ":" ");
		if ($days || $hours) echo $hours . " hour" . (($hours)?"s ":" ");
		echo $minutes . " minutes";
		?>
	<?php endif; ?>
		</p></p>
	<?php if($currentVersion != $newestVersion):?>
		<!--<a href='update' class='btn btn-default' role='button'>A new update to the WebUI is available, click to get it.</a>-->
	<?php endif; ?>
	</div>
     <?php if(file_exists("/home/stakebox/UI/".$currentWallet."notes.php")){
       include('/home/stakebox/UI/'.$currentWallet.'notes.php');
	echo "<div class='col-lg-5'>
	  <div class='form-group'>
	  <form action='notes' method='POST'><input type='hidden'>
	    <label for='notes'>Notes:</label>
	      <textarea class='form-control' name='notes' id='notes' cols='60' rows='10'>$notes</textarea>
		<button class='btn btn-default' type='submit' value='setprimary' style='margin-top:10px;'>Save Notes</button>
	  </form>
	  </div>
	</div>";}
     else{
	echo "<div class='col-lg-5'>
	  <div class='form-group'>
	  <form action='notes' method='POST'><input type='hidden'>
	    <label for='notes'>Notes:</label>
	      <textarea class='form-control' name='notes' id='notes' cols='60' rows='10'></textarea>
		<button class='btn btn-default' type='submit' value='setprimary' style='margin-top:10px;'>Save Notes</button>
	  </form>
	  </div>
	</div>";}
?>
</div>
</div>
<?php include ("footer.php"); ?>
