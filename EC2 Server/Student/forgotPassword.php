<?php
$studNum = $_GET['sN'];

$studNumFormField = '<input type="text" name="sN" value="'.$studNum.'" class="form-control" placeholder="Phone Number" required><br>';

?>
<head>
<title>Reset Password</title>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="form.css">
</head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<div class="container">
    <div class="row">
        <div class="col-sm-6 col-md-4 col-md-offset-4">          
            <h1 class="text-center login-title">Reset Password</h1>
            <div class="account-wall">
                <img class="profile-img" src="logo.png" alt="">
                <form action="sendResetCode.php" method="POST" class="form-signin">
                <?php echo $studNumFormField; ?>
                <button class="btn btn-lg btn-primary btn-block" type="submit">
                    Request Reset Code</button>
                </form>
            </div>
        </div>
    </div>
</div>