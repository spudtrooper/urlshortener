<?php include 'head.php'; ?>

<div id="long_url_label">Enter your link here</div>
<form method="post" action="<?php echo $phpSelf; ?>" >
	<input type="text" name="long_url" id="long_url" class="main_input round" size="57" />	

	 <script type="text/javascript">
	 function clearCustomCategory() {
	 var id = document.getElementById('category').value;
	 if (id != 'other') {
		 document.getElementById('custom_category').value = '';
	 }
 }
	 function changeToOther() {
		 var val = document.getElementById('custom_category').value;
		 if (val) {
			 document.getElementById('category').value = 'other';
		 }
	 }
</script>

<div id="inputs-wrapper">
<div id="inputs">
		 <div id="choices">
		 <table>
		 <tr>
		 <td>
		 <input type="submit" name="getlink" id="getlink" value="Get link" class="round" />
		 </td>
		 <?php
		 n('<td>');

n('<span class="label-wrapper"  id="urltype-wrapper">');
n('<select id="urltype" name="urltype" class="choice round">');
foreach ($TYPES as $id) opt($id);
n('</select>');
n('</span>');

n('</td>');
n('<td>');

n('<span class="label-wrapper" id="custom-category-wrapper">');
n('<select id="category" name="category" ' .
	'onchange="clearCustomCategory()" class="choice round">');
function opt($s) {
	n('<option id="' . $s . '">' . $s . '</option>');
}
opt('--');
foreach ($CATEGORIES as $id) opt($id);
opt('other');
n('</select>');
n('<input onkeyup="changeToOther()" type="text" name="custom_category" ' .
	'id="custom_category" class="main_input round" size="15" />');
n('</span>');

?>
</td></tr>
<tr>
<td></td>
<td>
<label for="urltype" class="input-label">
		 Type
</label>
</td>
<td>
<label for="custom_category" class="input-label">
		 Category
</label>
</td>
</tr>

</table>
</div>
</div>
</div>


</form>

<?php

$msgClass = '';
$msg = '';
$result   = '';
$success = FALSE;
if (isset($_POST['long_url']) || isset($_REQUEST['u'])) {
	if (isset($_POST['long_url'])) {
		$longUrl = post('long_url');
	} else {
		$longUrl = request('u');
	}
	$category = post('category');
	$customCategory = post('custom_category');
	$urlType = post('urltype');
	$newUrlResult = addNewUrl($longUrl,$category,$customCategory,$urlType);
	if ($newUrlResult->isOK()) {
		$result   = fullUrl($newUrlResult->getResult());
		$msg      = $newUrlResult->getMessage();
		$msgClass = 'success';
		$success = TRUE;

		// If we don't have a title for this one, try to find one
		$title = $lastFoundTitle;
		if (!$title) {
			$title = findTitle($longUrl,$lastFoundId);
		}

		// Send links
		$mailto  = 'RECIPIENT';
		$body    = $result;
		$subject = $title ? $title : $result;
		//
		// Get rid of any newlines, trim the strings, etc...
		//
		function gooderString($s) {
			$s = preg_replace('/\n/',' ',$s);
			$s = preg_replace('/^\s+/','',$s);
			$s = preg_replace('/\s+$/','',$s);
			return $s;
		}
		$subject = gooderString($subject);
		$body = gooderString($body);
		//
		// Make the links
		//
		$mail    = '<a href="#" onclick="sendMail(\'' . $mailto . '\',\'' . $subject . '\',\'' . $body . '\')">mail</a>';
		$aim     = '<a href="#" onclick="sendIM(\'' . $body . '\')">aim</a>';
		$yahoo   = '<a href="#" onclick="sendYahoo(\'' . $body . '\')">yahoo</a>';
		$skype   = '<a href="#" onclick="sendSkype(\'' . $body . '\')">skype</a>';
		$twitter = '<a href="#" onclick="sendTwitter(\'' . $body . '\')">twitter</a>';
    //
    // Make the stats string
    //
    $statsMsg = 'View the link\'s stats ' . ahref(statsLink($result),'here') . '.';
	} else {
		$msg = $newUrlResult->getResult();
		$msgClass = 'error';
	}
}
?>

<div class="url-title"><?php echo $title; ?></div>
<div id="message" class="<?php echo $msgClass; ?>"><?php echo $msg; ?></div>
<div id="results" class="url">
	<a target="_" href="<?php echo $result; ?>"><?php echo $result; ?></a>
	</div>
	<div id="results-more">
	<?php
	if ($success) {
		echo '[' . $mail    . '] ';
		echo '[' . $aim     . '] ';
		echo '[' . $yahoo   . '] ';
		echo '[' . $skype   . '] ';
		echo '[' . $twitter . ']';
	}
?>
	</div>

  <div id="statsDiv">
  <?php echo $statsMsg; ?>
</div>

<?php include 'foot.php'; ?>
