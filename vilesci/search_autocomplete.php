<?php
/* Copyright (C) 2014 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Stefan Puraner  <puraner@technikum-wien.at>
 */

require_once('../../../config/cis.config.inc.php');
require_once('../../../include/basis_db.class.php');
require_once('../include/idCard.class.php'); 
	
if (!$db = new basis_db())
    die('Es konnte keine Verbindung zum Server aufgebaut werden.');

$method = filter_input(INPUT_GET, "autocomplete");
$term = filter_input(INPUT_GET, "term");

if(!is_null($method) && !is_null($term))
{
	$search=trim($term);
	if (is_null($search) ||$search=='')
		exit();	
	$idCard = new idCard(); 
	$searchItems = explode(' ',$search);
	if($idCard->search($search))
	{
		$result_obj = array();
		foreach($idCard->result as $row)
		{
			$item['cardnumber']=html_entity_decode($row->cardnumber);
			$item['cardtext']=html_entity_decode($row->cardtext);
			$item['ablaufdatum']=html_entity_decode($row->ablaufdatum);
			$item['id']=html_entity_decode($row->id);
			$result_obj[]=$item;
		}
		echo json_encode($result_obj);
	}
	exit;
}
?>