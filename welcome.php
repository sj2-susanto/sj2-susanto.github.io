<html>
<head> <title>Welcome</title> </head>
<body>

<p>Today's Date (according to this Web server) is
	<?php
	echo(date("l, F dS Y.") );
	?>
</p>

<p>
This is the result of the call to welcome.php
</p>
<?php
      echo("hello ".$_POST[“firstname”]." ".$_POST[“lastname”]);
?>
</body>
</html>
