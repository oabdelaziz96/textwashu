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
<textarea rows="4" cols="50" id="arg1" name="arg1" form="usrForm"><?php echo urldecode($_GET["arg1"])?></textarea><br>
Add Smart Fields: 
<input id="first_name" type="button" value="First Name" class="smart1"/>
<input id="last_name" type="button" value="Last Name" class="smart1"/>
<input id="email" type="button" value="Email" class="smart1"/>
<input id="wustl_key" type="button" value="WUSTL Key" class="smart1"/>
<input id="id_number" type="button" value="ID Number" class="smart1"/>
<input id="profile_url" type="button" value="Profile URL" class="smart1"/>
<br><br><?php echo urldecode($_GET["arg2Desc"])?><br>
<textarea rows="4" cols="50" id="arg2" name="arg2" form="usrForm"><?php echo urldecode($_GET["arg2"])?></textarea><br>
Add Smart Fields: 
<input id="first_name" type="button" value="First Name" class="smart2"/>
<input id="last_name" type="button" value="Last Name" class="smart2"/>
<input id="email" type="button" value="Email" class="smart2"/>
<input id="wustl_key" type="button" value="WUSTL Key" class="smart2"/>
<input id="id_number" type="button" value="ID Number" class="smart2"/>
<input id="profile_url" type="button" value="Profile URL" class="smart2"/>
<br><br><br>
<button type="submit">Update</button><a href="managePreferences.php"><input type="button" value="Cancel"/></a>
</form>
<?php echo '<script> $("#'.urldecode($_GET["status"]).'").prop("checked", true); </script>'; ?>

<script> //Smartfields script
    $(".smart1").click(function(){
        var id = this.id;
        var old = $('#arg1').val();
        var updated = old + "["+id+"]";
        $('#arg1').focus();
        $('#arg1').val(updated);
    });
    
    $(".smart2").click(function(){
        var id = this.id;
        var old = $('#arg2').val();
        var updated = old + "["+id+"]";
        $('#arg2').focus();
        $('#arg2').val(updated);
    });
</script>