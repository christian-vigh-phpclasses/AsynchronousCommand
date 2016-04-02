<?php
	// This script is a sample command that prompts the user to enter some text, then displays it
	// It is run by the input.php script.
	echo "Please enter something : " ;
	$line	=  trim ( fgets ( STDIN ) ) ;

	echo "You entered : [$line]\n" ;