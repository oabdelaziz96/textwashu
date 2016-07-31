<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>
<title>Edit Preference</title>
<h1><?php echo urldecode($_GET["name"])?></h1>
<h3><?php echo urldecode($_GET["prefDesc"])?></h3>
<form action="editPreference.php" method="POST" id="usrForm">
<input type="hidden" value="<?php echo urldecode($_GET["number"])?>" name="prefNum">
Status<br>
<input type="radio" name="status" id="On" value="On"> On<br>
<input type="radio" name="status" id="Off" value="Off"> Off<br>
<br><br><?php echo urldecode($_GET["arg1Desc"])?><br>
<textarea rows="4" cols="50" name="arg1" form="usrForm"><?php echo urldecode($_GET["arg1"])?></textarea>
<br><br><?php echo urldecode($_GET["arg2Desc"])?><br>
<textarea rows="4" cols="50" name="arg2" form="usrForm"><?php echo urldecode($_GET["arg2"])?></textarea>
<br><br>
<button type="submit">Update</button><a href="managePreferences.php"><input type="button" value="Cancel"/></a>
</form>
<?php echo '<script> $("#'.urldecode($_GET["status"]).'").prop("checked", true); </script>'; ?>