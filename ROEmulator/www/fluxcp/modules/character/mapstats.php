<?php
if (!defined('FLUX_ROOT')) exit;

$title = 'Map Statistics';

$bind = array();
$sql  = "SELECT last_map AS map_name, COUNT(last_map) AS player_count FROM {$server->charMapDatabase}.`char` ";

if (($hideLevel=(int)Flux::config('HideFromMapStats')) > 0 && !$auth->allowedToSeeHiddenMapStats) {
	$sql .= "LEFT JOIN {$server->loginDatabase}.login ON `char`.account_id = login.account_id ";
}

$sql .= "WHERE online > 0 ";

if ($hideLevel > 0 && !$auth->allowedToSeeHiddenMapStats) {
	$sql   .= "AND login.level < ? ";
	$bind[] = $hideLevel;
}

$sql .= " GROUP BY map_name, online HAVING player_count > 0 ORDER BY map_name ASC";
$sth  = $server->connection->getStatement($sql);

$sth->execute($bind);
$maps = $sth->fetchAll();
?>