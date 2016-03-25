# AsynchronousCommand class #

This class addresses the case where an external command is to be run, and its output needs to be captured and interpreted as soon as the external command emits it.

It uses streams to pipe the standard output of the child process and be able to read it.

Unfortunately for Windows users, there are several limitations that this class tries to overcome :

- The *stream\_set_blocking()* PHP function does not work, due to a Windows limitation. Thus, a call to *fread()* on whichever pipe that has been established will be a blocking call.
- There was an alternative of using *stream_select()*, which returns the pipes/sockets on which new events are available. However, the *select()* Windows API used on streams that are not yet available returns the stream descriptors themselves as if new data was available on them. 
  The consequence is that a call to *fread()* on one of these streams which is supposed to be in a "non-available" state will be blocking. 
  
In fact, on Windows it is currently impossible to separately get into PHP the output generated by a child process on both *stdout* and *stderr* without eventually doing a blocking call.
  
For those reasons, this class makes a compromise : you can spawn a child process, send data to it through its *stdin* file descriptor, read from its standard output, but you will not be able to differentiate between *stdout* and *stderr*. Both will be mixed because *stderr* will systematically be redirected to *stdout* by the **AsynchronousCommand** class.

Listed below is an example of how to run a command, capture in real-time its standard output, and do some special processing if a given string is encountered in the child output :

	$cmd = new AsynchronousCommand ( "somecommand" ) ;
	$cmd -> Run ( ) ;

	output ( "Execute pid = " . $cmd -> GetPid ( ) ) ;
	while  ( $cmd -> IsRunning ( ) )
	   {
		while  ( ( $data = $cmd -> ReadLine ( ) )  !==  false )
		   {
			echo ( "$data\n" ) ;
			
			if  ( strpos ( $data, "some string value that makes me stop" )  !==  false )
			   {
				$cmd -> Terminate ( ) ;		// Terminate the child process
				break ;
			    }
		    }
	    }
	 
	output ( "Finished, exit code = " . $cmd -> GetExitCode ( ) ) ;
	$cmd -> Terminate ( ) ;	
	
It is also possible to write to the child process standard input ; for that, just instanciate the command object using "true" as the second parameter ($pipe_stdin) :

	$cmd = new AsynchronousCommand ( "somecommand", true ) ;

Then, in the while loop, add the following code to write something if an input request has been made :
	
	if  ( $cmd -> IsStdinRequested ( ) )
		$cmd -> WriteLine ( "my output for my somecommand command" ) ;

# METHODS #

The following paragraph the methods that are available from the **AynchronousCommand** class.

## Constructor ##

Creates a child process using the specified command.

### Prototype ###

	$cmd 	= new AsynchronousCommand ( $command, $pipe_stdin = false, $run = false ) ;

### Parameters ###

**$command (string) -**

Command to be executed.
	
**$pipe_stdin (boolean) -**

When true, the parent process (ie, the one that instanciated an AsynchronousCommand object) will be able to write to its child stdin file descriptor using the Write or WriteLine methods.

When false (the default), the file descriptor used for the child's standard input is the one 
of the parent process.
	
**$run (boolean) -**

When true, the command is run immediately. Otherwise you will have to use the **Run()** method to start command execution.

### Notes ###

Due to the current limitations of Windows and PHP on Windows, only the child process standard output can be read from a pipe as a non-blocking call. The child standard error is always redirected to standard output.


## Execute method ##

A shortcut using a static method for executing a command and displaying its output onto standard output.

A callback can be specified to handle each chunk of output.

### Prototype ####

	$status		=  AsynchronousCommand::Execute ( $command, $cwd = null, $callback = null, 
													$line_buffering = true ) ;

### Parameters ###

**$command (string) -**

Command to execute.

**$cwd (string) -**

Current directory for command execution.

**$callback (callback) -**

Function that will be called for processing each chunk of data read from the command output 
stream. The callback must have the following signature :

		boolean  callback ( $data ) ;

The data will be written to standard output if the callback returns false.

**$line_buffering (boolean) -**

When true (the default), the command output stream data will be read line by line.
	
### Return value ###

Returns the command exit status.

## Various getters and setters ##

	// Get the command to run
	public function  GetCommand ( ) ;
	
	// Get the command current directory (specified before running it...)
	public function  GetCwd ( ) ;
	   
	// Get the command environment variables (specified before running it...)
	public function  GetEnvironment ( ) ;
	   
	// Get the command exit code (only available when the child process has terminated, otherwise returns -1)
	public function  GetExitCode ( ) ;

	// Get the command pid
	public function  GetPid ( ) ;
		
	// Get the timeout in microseconds when checking if data is available on the stdout pipe
	public function  GetPollTimeout ( ) ;
	   
	// Gets the child process descriptor for standard input.
	public function  GetStdin ( ) ;

	// Returns the standard output file descriptor of a child process.
	public function  GetStdout ( ) ;

	// For stopped process, get the signal number that stopped them
	public function  GetStopSignal ( ) ;

	// For terminated processes, get the signal number that was sent (0 = not killed)
	public function  GetTerminationSignal ( ) ;
	   
	// Checks if the command is running.
	public function  IsRunning ( ) ;

	// Returns a flag indicating if the child process received a signal.
	public function  IsSignaled ( ) ;

	// Returns a flag indicating if the child process standard input was piped to the parent process standard output
	public function  IsStdinPiped ( ) ;

	// Checks if the child process is requesting for input.
	public function  IsStdinRequested ( ) ;

	// Checks if data is available on child process standard output.
	public function  IsStdoutAvailable ( ) ; 

	// Returns a flag indicating if the child process was stopped.
	public function  IsStopped ( ) ;

	// Sets the child process working directory
	public function  SetCwd ( $cwd ) ;

	// Sets the poll timeout value (in microseconds). The effect of this function is immediate, it can be safely
	// called from within a while ( IsRunning() ) loop. The value is expressed in milliseconds.
	public function  SetPollTimeout ( $interval ) ;

	// Set the child process environment variables
	public function  SetEnvironment ( $env ) ;

## Read, ReadLine ##

Reads data from the child standard output.

Read() reads data from the child process standard output, up to 8Kb.
ReadLine() reads a line, until an end of line is met.

### Prototype ####

		$data 		=  $cmd -> Read ( ) ;
		$data 		=  $cmd -> ReadLine ( ) ;

### Return value ###

The data read from the child process standard output.

For *ReadLine()*, the trailing newline is stripped from the returned value.
	   

## Run ##

Runs the command specified to the AsynchronousCommand constructor.


## Terminate ##

### Prototype ###

	$cmd -> Terminate ( $signal = 15 ) ;

### Parameters ### 

**$signal (integer) -**

*(Unix systems only)* Signal to be sent to the running process.
