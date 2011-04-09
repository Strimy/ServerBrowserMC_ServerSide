<?php

error_reporting(E_ALL);

require('sql.php');
require('sources/class_sql.php');
$Sql = new SQL($SQL);

$serverList = $Sql->full_query('SELECT s.id AS server_id, s.server_ip, s.server_dns, s.last_update, s.servername, s.maxplayercount, s.premiumonly, s.serverport, p.playername FROM servers s LEFT OUTER JOIN players p ON s.id = p.server_id');

$serverArray = array();

while($serverData = mysql_fetch_assoc($serverList))
{
	$server_id = $serverData['server_id'];
	
	if(!array_key_exists($server_id, $serverArray))
	{
		$serverArray[$server_id] = array(
			'Name' 				=> $serverData['servername'],
			'MaxPlayerCount' 	=> $serverData['maxplayercount'],
			'PremiumOnly'		=> $serverData['premiumonly'],
			'ServerPort'		=> $serverData['serverport'],
			'ServerIP'			=> $serverData['server_ip'],
			'ServerDNS'			=> $serverData['server_dns'],
			'LastUpdate'		=> $serverData['last_update'],
			'Players'			=> array()
		);
	}
	
	if($serverData['playername'] != NULL)
	{
		array_push($serverArray[$server_id]['Players'], $serverData['playername']);
	}
}


echo '<table>
	<tr>
		<th>Server Name</th>
		<th>Server IP</th>
		<th>Server Port</th>
		<th>Server DNS</th>
		<th>Players</th>
		<th>Premium ?</th>
		<th>Last Update</th>
	</tr>';
	
foreach($serverArray as $server)
{
	echo '<tr>';
	echo '<td>'.$server['Name'].'</td>';
	echo '<td>'.$server['ServerIP'].'</td>';
	echo '<td>'.$server['ServerPort'].'</td>';
	echo '<td>'.$server['ServerDNS'].'</td>';
	echo '<td>'.count($server['Players']).'/'.$server['MaxPlayerCount'].'</td>';
	echo '<td>'.$server['PremiumOnly'].'</td>';
	echo '<td>'.$server['LastUpdate'].'</td>';
	echo '</tr>';
}

echo '</table>';
?>