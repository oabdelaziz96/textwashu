<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>
<title>Edit Hashtag</title>
<h1>Edit Hashtag</h1>
<form action="editHashtag.php" method="POST" id="usrForm">
Hashtag<br>
<input type="text" value="<?php echo urldecode($_GET["hashtag"])?>" name="hashtag" readonly="readonly">
<br><br>Status<br>
<input type="radio" name="status" id="Active" value="Active"> Active<br>
<input type="radio" name="status" id="Archived" value="Archived"> Archived<br>
<br><br>Reply Message<br>
<textarea rows="4" cols="50" name="reply" form="usrForm"><?php echo urldecode($_GET["response"])?></textarea>
<br><br>
<button type="submit">Edit</button>
</form>
<?php echo '<script> $("#'.urldecode($_GET["status"]).'").prop("checked", true); </script>'; ?>