<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Lettre</title>
	<link rel="stylesheet" type="text/css" href="reset.css" />
	<link rel="stylesheet" type="text/css" href="login.css" />
</head>

<body>

<form method="post" enctype="multipart/form-data" >
	<div class="textInputs">
		<input class="valider" autofocus="autofocus"  placeholder=" Mot de passe" class="textInput"  id="password" type="passWord" name="passWord" />
	</div>
	<input	class="valider custom-button" type="submit" name="submit" value="Valider"/>
</form>

<?php

if(isset($_POST['passWord']) && isset($_POST['submit'])){

	$passWord = $_POST['passWord'];
	
	if($passWord == 'totoro28'){
		session_start();
		$_SESSION['passWord'] = $passWord;
		header("LOCATION: text.php");
	}
	else {
		echo "Mot de passe incorrect.";
	}
}



?>
</body>
</html>