<?php
	/***
		This example runs the "find" command on unix systems, and the "forfiles" command
		on windows systems, then displays filenames one by one.

		The user is then prompted ; she can either press the enter key to display the 
		next filename, or enter "q" to stop.

		Note that the AsynchronousCommand class allows us to retrieve command output
		as soon as it arrives ; we definitely do not have to wait for the end of the
		command execution to do that.

	 ***/ 
	require ( '../AsynchronousCommand.php') ;

	// Don't worry about the following distinction between windows and unix ; the AsynchronousCommand
	// class is platform-independent.
	// this just helps to determine which command is to be run (the unix 'find' command does not have 
	// the same purpose on windows systems, this is why we will run something approximate,
	// such as FORFILES.EXE).
	if  ( ! strncasecmp ( php_uname ( 's' ), 'windows', 7 ) )
 		$command	=  "forfiles /P \\ /S" ;
	else
 		$command	=  "find / -print" ;

	// Instantiate an AsynchronousCommand object and run the command ('find' on unix systems, 'FORFILES'
	// on windows)
	$cmd = new AsynchronousCommand ( $command ) ;
	$cmd -> Run ( ) ;
	$pid =  $cmd -> GetPid ( ) ;		// Retrieve the process id of the command

	echo ( "'find' command started (pid = $pid), retrieving filenames :\n") ;

	// While the command is still running...
	while ( $cmd -> IsRunning ( ) )
	   {
		// ... Collect all the data lines (in this case, filenames) that have been made available 
		// on command standard output
		// Note that we have to use a nested loop : this is to get current command output until
		// it gets exhausted. When such a condition arises, we fall to the outer loop and test
		// again if the command is running. If true, then we can try to read the next lines of
		// command output.
		// Such a construct (nested loops) is useful when you have to run commands that take
		// processing time before any output appears.
		while  ( ( $line = $cmd -> ReadLine ( ) )  !==  false )
		   {
			// Display next filename and stop if the user entered "q" :
			echo ( "found file: $line\n" ) ;
			echo ( "Press enter to display next filename, or 'q' to quit : " ) ;
			$input =  trim ( strtolower ( fgets ( STDIN ) ) ) ;

			if  ( $input  ==  'q' )
				break 2 ;
		    }
	    }

	// All done...
	$cmd -> Terminate ( ) ;
	$exit_code =  $cmd -> GetExitCode ( ) ;
	echo ( "Command terminated, exit code = $exit_code\n" ) ;


