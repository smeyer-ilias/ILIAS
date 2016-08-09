<?php

if(isset($_GET['ref_id']))
{
	if(isset($_GET['cmd']))
	{
		//echo "1";
		switch($_GET['cmd'])
		{
			case 'sendfile':
				header("Location: http://www.ilias.fh-dortmund.de/ilias/ilias.php?ref_id=".$_GET['ref_id']."&cmd=sendfile&cmdClass=ilrepositorygui&baseClass=ilRepositoryGUI");
				break;
			default:
				notFoundError();
		}					
	}
	else
	{
		notFoundError();
	}

}
else
{
	notFoundError();
}

function notFoundError() {
	header("HTTP/1.1 404 Not Found");
	echo <<<EOF
<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
<html><head>
<title>404 Not Found</title>
</head><body>
<h1>Not Found</h1>
<p>The requested URL /ilias/repository.php was not found on this server.</p>
<hr>
</body></html>
EOF;

}

?>