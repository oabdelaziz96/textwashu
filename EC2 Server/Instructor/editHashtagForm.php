<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.0.0/jquery.min.js"></script>
<title>Edit Hashtag</title>
<h1>Edit <?php echo urldecode($_GET["hashtag"])?></h1>
<form action="editHashtag.php" method="POST" id="usrForm">
<input type="hidden" value="<?php echo urldecode($_GET["hashtag"])?>" name="hashtag" readonly="readonly">
Status<br>
<input type="radio" name="status" id="Active" value="Active"> Active<br>
<input type="radio" name="status" id="Archived" value="Archived"> Archived<br>
<br><br>Reply Message<br>
<textarea rows="4" cols="50" id="reply" name="reply" form="usrForm"><?php echo urldecode($_GET["response"])?></textarea><br>
Add Smart Fields: 
<input id="first_name" type="button" value="First Name" class="smart"/>
<input id="last_name" type="button" value="Last Name" class="smart"/>
<input id="email" type="button" value="Email" class="smart"/>
<input id="wustl_key" type="button" value="WUSTL Key" class="smart"/>
<input id="id_number" type="button" value="ID Number" class="smart"/>
<input id="profile_url" type="button" value="Profile URL" class="smart"/>
<br><br><br>
<button type="submit">Update</button><a href="manageHashtags.php"><input type="button" value="Cancel"/></a>
</form>
<?php echo '<script> $("#'.urldecode($_GET["status"]).'").prop("checked", true); </script>'; ?>

<script> //Smartfields script
    $(".smart").click(function(){
        var id = this.id;
        var old = $('#reply').val();
        var updated = old + "["+id+"]";
        $('#reply').focus();
        $('#reply').val(updated);
    });
</script>