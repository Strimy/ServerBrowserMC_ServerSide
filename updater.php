<?php

error_reporting(E_ALL);

require('sql.php');
require('sources/class_sql.php');
$Sql = new SQL($SQL);

$serverIp = $_SERVER['REMOTE_ADDR'];

if(isset($_POST['data']))
{
	$data = $_POST['data'];
}
else
{
	header('location: index.php');
	exit();
}

$domDoc = new DOMDocument();
$domDoc->loadXML($data);

$rootNode = $domDoc->getElementsByTagName("ServerInfos")->item(0);

$serverName = mysql_real_escape_string($domDoc->getElementsByTagName("ServerName")->item(0)->nodeValue);
echo $serverName.'<br />';

$serverPort = mysql_real_escape_string($domDoc->getElementsByTagName("ServerPort")->item(0)->nodeValue);
echo $serverPort.'<br />';

$maxPlayers = intval($domDoc->getElementsByTagName("MaxPlayers")->item(0)->nodeValue);
echo $maxPlayers.'<br />';

$token = mysql_real_escape_string($domDoc->getElementsByTagName("Token")->item(0)->nodeValue);

$password = mysql_real_escape_string($domDoc->getElementsByTagName("Password")->item(0)->nodeValue);

$playersElem = $domDoc->getElementsByTagName("Players")->item(0);
$players = array();

foreach($playersElem->getElementsByTagName("Player") as $playerNode)
{
	$name = $playerNode->getAttribute('Name');
	$timeOnline = $playerNode->getAttribute('TimeOnline');
	
	$playerArray = array(	'Name' 			=> 	mysql_real_escape_string($name),
							'TimeOnline'	=>	intval($timeOnline));
							
	array_push($players, $playerArray);
}

print_r($players);

$checkServerRes = $Sql->full_query('SELECT * FROM servers WHERE token = "'.$token.'"');

if(mysql_num_rows($checkServerRes) != 0)
{
	$serverData = mysql_fetch_array($checkServerRes);
	if($serverData['token_password'] == $password)
	{
		$id = $serverData['id'];
		$Sql->full_query('UPDATE servers SET 	servername = "'.$serverName.'",
												maxplayercount = '.$maxPlayers.',
												serverport = '.$serverPort.',
												last_update = NOW()
										WHERE id = '.$id.'');
										echo 'Update';
										
		$currentPlayersRes = $Sql->full_query('SELECT * FROM players WHERE server_id = '.$id.'');
		$currentPlayers = array();
		while($currentPlayer = mysql_fetch_array($currentPlayersRes))
		{
			array_push($currentPlayers, $currentPlayer);
		}
		
		
		// Suppression des anciennes entrées
		foreach($currentPlayers as $currentPlayer)
		{
			$playerFound = false;
			foreach($players as $newPlayer)
			{
				if($newPlayer['playername'] == $newPlayer['Name'])
				{
					$playerFound = true;
				}
			}
			if(!$playerFound)
			{
				$Sql->full_query('DELETE FROM players WHERE id = '.$currentPlayer['id'].'');
			}
		}
		
		foreach($players as $newPlayer)
		{
			$playerFound = false;
			foreach($currentPlayers as $currentPlayer)
			{
				if($currentPlayer['playername'] == $newPlayer['Name'])
				{
					$playerFound = true;
				}
			}
			if(!$playerFound)
			{
				$Sql->full_query('INSERT INTO players (playername, server_id) VALUES ("'.$newPlayer['Name'].'", '.$id.')');
			}
		}
	}
	else
	{
		// Return something to inform the Minecraft server that the token is already used or the password is incorrect
	}
}
else
{
	$id = $Sql->full_query('INSERT INTO servers (server_ip, servername, maxplayercount, premiumonly, serverport, token, token_password) 
						VALUES ("'.$serverIp.'", "'.$serverName.'", '.$maxPlayers.', 0, '.intval($serverPort).', "'.$token.'", "'.$password.'")');
						echo 'Add';
						
	foreach($players as $newPlayer)
	{
		$Sql->full_query('INSERT INTO players (playername, server_id) VALUES ("'.$newPlayer['Name'].'", '.$id.')');
	}					
						
	echo $Sql->debug_sql();
}
?>