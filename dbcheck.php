<?php
/* Copyright (C) 2015 fhcomplete.org
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
 * Authors: Stefan Puraner <puraner@technikum-wien.at>
 */
require_once('../../config/system.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');

// Datenbank Verbindung
$db = new basis_db();

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
    <link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
    <title>Addon Datenbank Check</title>
</head>
<body>
<h1>Addon Datenbank Check</h1>';

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('basis/addon'))
{
    exit('Sie haben keine Berechtigung für die Verwaltung von Addons');
}

echo '<h2>Aktualisierung der Datenbank</h2>';

// Code fuer die Datenbankanpassungen
if(!$result = @$db->db_query("SELECT 1 FROM addon.tbl_externeAusweise"))
{
//TODO DB anpassen
    $qry = 'CREATE TABLE addon.tbl_externeAusweise
	    (
		    id serial,
		    cardnumber varchar(32) UNIQUE,
		    cardtext text,
		    ablaufdatum timestamp
	    );
	    GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_externeAusweise TO vilesci;
	    GRANT SELECT, UPDATE ON addon.tbl_externeausweise_id_seq TO vilesci;
	    GRANT SELECT ON addon.tbl_externeausweise TO web;
	    GRANT USAGE ON SCHEMA addon TO web;
	    ';

    if(!$db->db_query($qry))
	echo '<strong>addon.tbl_externeAusweise: '.$db->db_last_error().'</strong><br>';
    else 
	echo ' addon.tbl_externeAusweise: Tabelle addon.tbl_externeAusweise hinzugefügt!<br>';
}

//Neue Berechtigung für das Addon hinzufügen
if($result = $db->db_query("SELECT * FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='addon/externeAusweise'"))
{
    if($db->db_num_rows($result)==0)
    {
	$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung) 
		    VALUES('addon/externeAusweise','AddOn Externe Ausweise');";

	if(!$db->db_query($qry))
	    echo '<strong>Berechtigung: '.$db->db_last_error().'</strong><br>';
	else 
	    echo 'Neue Berechtigung addon/externeAusweise hinzugefuegt!<br>';
	
	$qry = "INSERT INTO system.tbl_rolleberechtigung(berechtigung_kurzbz, rolle_kurzbz, art) 
		    VALUES('addon/externeAusweise','admin','suid');";
	
	if(!$db->db_query($qry))
	    echo '<strong>Berechtigung: '.$db->db_last_error().'</strong><br>';
	else 
	    echo 'Neue Berechtigung addon/externeAusweise zu Rolle admin hinzugefuegt!<br>';
	
    }
}

echo '<br>Aktualisierung abgeschlossen<br><br>';
echo '<h2>Gegenprüfung</h2>';


// Liste der verwendeten Tabellen / Spalten des Addons
$tabellen=array(
    "addon.tbl_externeausweise"  => array("id","cardnumber","cardtext")
);


$tabs=array_keys($tabellen);
$i=0;
foreach ($tabellen AS $attribute)
{
    $sql_attr='';
    foreach($attribute AS $attr)
	$sql_attr.=$attr.',';
    $sql_attr=substr($sql_attr, 0, -1);

    if (!@$db->db_query('SELECT '.$sql_attr.' FROM '.$tabs[$i].' LIMIT 1;'))
	echo '<BR><strong>'.$tabs[$i].': '.$db->db_last_error().' </strong><BR>';
    else
	echo $tabs[$i].': OK - ';
    flush();
    $i++;
}
?>
