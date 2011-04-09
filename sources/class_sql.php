<?php

/*
+--------------------------------------------------------------------------
|   Experimental SQL Class
|   ========================================
|   par Olivencia Adrien
|   ========================================
|   
+---------------------------------------------------------------------------
|   > $Date: 31/08/2007 14:06 GMT+1 (Vendredi, 31 Aout 2007) $
|   > $Révision: 0.90 $
+---------------------------------------------------------------------------
|
|   > Classe de gestion SQL
|   > Ecrit par Olivencia Adrien
|   > Date de commencement: 31 Aout 2007
|	> Dernière mise à jour: Revolution 3 Experimental: Samedi 29 Décembre 2007
|
+--------------------------------------------------------------------------
*/

class SQL
{
	var $nbr_query = 0;
	var $query_log = array();
	var $connection_log = array();
	var $sql_prefix = '';
	var $is_connected;
	private $db_id;
	
	function __construct($sql_config = false, $force_utf = false)
	{
		if($sql_config)
		{		
			$this->parse_sql_var($sql_config);
			$this->connect();
			if($force_utf)
			{
				mysql_query("SET NAMES 'utf8'");	
			}
		}
	}
	
	function connect()
	{
			$this->db_id = mysql_connect($this->sql_host, $this->sql_user, $this->sql_pass) or sql_error('MySQL connection', mysql_error());
			mysql_select_db($this->sql_db, $this->db_id) or $this->sql_error('Select DB', mysql_error());
			
	}
	
	public function get_last_id()
	{
		return mysql_insert_id($this->db_id);
	}
	
	function full_query($query)
	{
		$this->nbr_query++;
		
		$sql_query_res = @mysql_query($query);

		$this->sql_log($query);
		
		return $sql_query_res;
	}
	
	function query($type, $table, $data_array = NULL, $where = '', $order = '', $lim = '')
	{
		if($type == 'select')
		{
			$table = addslashes($table);
			
			$query = 'SELECT ';
			
			if(!is_array($data_array))
			{
				$query .= '*';
			}
			else
			{

				while(list($key, $data) = each($data_array))
				{
					if($key == 0)
					{
						$query .= $data;
					}
					else
					{
						$query .=  ','.$data;
					}	
								
				}	
				
			}
			
			$query .= ' FROM '.$this->sql_prefix.$table;
			
			if(is_array($where))
			{
				$query .= ' WHERE `'.$where[0].'` = \''.$where[1].'\'';
			}
			
			if($order)
			{
				$query .= ' ORDER BY '.$order[0].' '.$order[1];
			}
			
			if($lim)
			{
				$query .= ' LIMIT 0,'.$lim;
			}
			
			return $this->full_query($query);
		}								
	}
	
	protected function sql_log($query)
	{
		$this->query_log[] = array($query, mysql_errno(), mysql_error());
	}
	
	function parse_sql_var($sql_conf_array)
	{
		foreach($sql_conf_array as $k => $v)
		{
			$this->$k = $v;
		}
	}
	
	function parse_errno($errno = 0)
	{
		if($errno == 0) $errno = mysql_errno();
		switch($errno) 
		{
			case 0:
				$str_error = 'Aucune erreur';
			break;
			
			case 1064:
				$str_error = 'Erreur fatale: La syntaxe de la requête est incorrecte';
			break;
			
			case 1146:
				$str_error = 'Erreur fatale: la table demandée n\'existe pas';
			break;
			
			default:
				$str_error = 'Erreur inconnue';
		}
		
		if($this->is_module)
		{
			if(is_object($this->kernel))
			{
				@$this->kernel->admin_info('Erreur SQL', 'Numéro d\'erreur SQL:'.mysql_errno().'<br />Erreur détectée: '.$str_error.'<br />Erreur MySQL: '.mysql_error());
			}
		}
		
		return $str_error;
	}
	
	function sql_error($call, $message)
	{
		$this->connection_log[$call] = $message;
	}
	
	function debug_sql()
	{
		$temp = '
		<h2>Debug SQL</h2>';
		
		foreach($this->connection_log as $call => $error)
		{
			$temp .= 'Error ('.$call.') : '.$error.'<br />';	
		}
		
		$temp .= '<table width="98%" cellspacing="0" style="margin-top: 2px; border: 1px solid black" summary="Debug SQL">
		<tr>
			<th scope="col">ID</th>
			<th scope="col">Requête</th>
			<th scope="col">Code SQL</th>
			<th scope="col">Statut</th>
		  </tr>';
		foreach($this->query_log as $id => $array)
		{
			if($array[2] == NULL)
			{
				$error = '<em>Réussi</em>';
			}
			else
			{
				$error = '<span style="color: red">';
				$error .= $array[2];
				$error .= '</span>';
			}
			
			$temp .= '<tr>
				<td>'.$id.'</td>
				<td>'.$array[0].'</td>
				<td>'.$array[1].'</td>
				<td>'.$error.'</td>
			</tr>';
		}	
		
		return $temp;
	}

	function __destruct()
	{
		if($this->is_connected)
		{
			mysql_close($this->db_id);
		}
	}

}

?>
