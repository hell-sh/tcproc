<?php
if(empty($argv[2]))
{
	die("Syntax: tcproc <port> <executable> [executable arguments ...]\n");
}
if(defined("PHP_WINDOWS_VERSION_MAJOR"))
{
	echo "Oh God, you're on Windows... There's a good chance this won't work.\r\n";
}
$cmd = $argv[2];
if(!empty($argv[3]))
{
	for($i = 3; $i < count($argv); $i++)
	{
		$cmd .= " ".$argv[$i];
	}
}
$server = stream_socket_server("tcp://0.0.0.0:".$argv[1], $errno, $errstr) or die($errstr."\n");
stream_set_blocking($server, false);
$clients = [];
$null = null;
do
{
	$start = microtime(true);
	while(($client = @stream_socket_accept($server, 0)) !== false)
	{
		stream_set_blocking($client, false);
		$proc = proc_open($cmd, [
			["pipe", "r"],
			["pipe", "w"],
			["pipe", "w"]
		], $pipes);
		if(is_resource($proc))
		{
			for($i = 0; $i <= 2; $i++)
			{
				stream_set_blocking($pipes[$i], false);
			}
			array_push($clients, [
				"sock" => $client,
				"proc" => $proc,
				"pipes" => $pipes
			]);
		}
		else
		{
			fwrite($client, "tcproc: failed to start process\n");
			fclose($client);
		}
	}
	foreach($clients as $i => $client)
	{
		if(proc_get_status($client["proc"])["running"] !== true)
		{
			fclose($client["sock"]);
			unset($clients[$i]);
		}
		else if(@feof($client["sock"]) !== false)
		{
			proc_close($client["proc"]);
			unset($clients[$i]);
		}
		else
		{
			$read = [$client["sock"], $client["pipes"][1], $client["pipes"][2]];
			if(stream_select($read, $null, $null, 0) > 0)
			{
				if(in_array($client["sock"], $read))
				{
					fwrite($client["pipes"][0], fread($client["sock"], 1024));
				}
				else if(in_array($client["pipes"][1], $read))
				{
					fwrite($client["sock"], fread($client["pipes"][1], 1024));
				}
				else if(in_array($client["pipes"][2], $read))
				{
					fwrite($client["sock"], fread($client["pipes"][2], 1024));
				}
			}
		}
	}
	if(($remaining = (0.050 - (microtime(true) - $start))) > 0)
	{
		time_nanosleep(0, $remaining * 1000000000);
	}
}
while(true);
