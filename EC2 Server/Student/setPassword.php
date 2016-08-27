<?php
$studNum = $_GET['sN'];
$accessCode = $_GET['rC'];
$type = $_GET['t'];

if (is_null($studNum) || ($studNum == "")) { //studNum not given
    $alertMessage = "Unauthorized Request";
	echo $alertMessage;
    exit;
} else {
    $formatedNumber = "(".substr($studNum,0,3).") ".substr($studNum,3,3)."-".substr($studNum,6,4);
    $title = "Set password for ".$formatedNumber;
    $studNumFormField = '<input type="hidden" name="sN" value="'.$studNum.'" class="form-control" required>';
}

if ($type == "reset") {
	$codeName = "Reset Code";
} else {
	$codeName = "Access Code";
}

if (is_null($accessCode) || ($accessCode == "")) { //accessCode not given
    $accessCodeFormField = '<input type="text" name="aC" class="form-control" placeholder="'.$codeName.'" required><br>';
} else {
    $accessCodeFormField = '<input type="hidden" name="aC" value="'.$accessCode.'" class="form-control" required>';
}


?>
<head>
<title>Set Password</title>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="form.css">
</head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<div class="container">
    <div class="row">
        <div class="col-sm-6 col-md-4 col-md-offset-4">          
            <h1 class="text-center login-title"><?php echo $title; ?></h1>
            <div class="account-wall">
                <img class="profile-img" src="logo.png" alt="">
                <form action="settingPassword.php" method="POST" class="form-signin">
                <?php echo $studNumFormField; echo $accessCodeFormField; ?>
                <input type="password" class="form-control" placeholder="Password" name="password" required><br>
                <button class="btn btn-lg btn-primary btn-block" type="submit">
                    Set Password & Login</button>
                </form>
            </div>
        </div>
    </div>
</div>