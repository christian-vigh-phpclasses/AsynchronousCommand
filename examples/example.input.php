<?php
	/***
		This example runs the askforinput.php script, which prompts the user for some input
		and displays the results.
	 ***/
	require ( '../AsynchronousCommand.php') ;

	// Execute the askforinput.php script. Note that the second parameter of the AsynchronousCommand
	// constructor is set to true, meaning that we want to write to the process' standard input.
	$cmd	=  new AsynchronousCommand ( "php askforinput.php", true ) ;
	$cmd -> Run ( ) ;

	// A difficulty I could not overcome is that stream_select() on windows platforms is blocking if
	// some input is requested meanwhile. 
	// For that reason, we won't be able to catch the "Please enter something : " prompt until we 
	// satisfy the external command input request
	if  ( $cmd -> IsStdinRequested ( ) )
		$cmd -> WriteLine ( "This is some text piped into askforinput.php standard input" ) ;

	// Write command output
	while  ( ( $line = $cmd -> ReadLine ( ) )  !==  false )
		echo ( $line ) ;
