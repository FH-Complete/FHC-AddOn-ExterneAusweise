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

require_once ('../version.php');
require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once ('../include/idCard.class.php');
$uid = get_uid();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
    "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="stylesheet" href="../../../skin/fhcomplete.css" type="text/css">
    <link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
    <link rel="stylesheet" href="../include/css/bootstrap.min.css" type="text/css">
    <script src="../include/js/jquery1.9.min.js"></script>
    <script src="../include/js/bootstrap.min.js"></script>
    <script type="text/javascript">
	$(document).ready(function() {
	    loadAll();
	    $(document).ready(function() {
		var minLength = 0;
		$("#search").autocomplete({
		    source: "search_autocomplete.php?autocomplete=search",
		    minLength: minLength,
		    response: function(event, ui)
		    {
			//Wenn Suchfeld gelöscht wird, alle Eintraege anzeigen
			if(event.target.value.length === 0)
			{
			    loadAll();
			    $("#search").autocomplete("close");
			}
			//Value und Label fuer die Anzeige setzen
			var html= "";
			for(i in ui.content)
			{
			    html += writeTableRow(ui.content[i]);
			}
			$("#dataBody").html(html);
			$("#search").autocomplete("close");

		    },
		    select: function(event, ui)
		    {

		    }
		});
	    }); 
	});
	function deleteId(id)
	{
	    clearMessage();
	    if(confirm("Datensatz wirklich löschen?"))
	    {
		$.ajax({
		    dataType: 'json',
		    url: "../include/ajaxHelper.php",
		    type: "POST",
		    data: {
			method: "delete",
			id: id
		    }
		}).success(function(data){
		    loadAll();
		}).complete(function(event, xhr, settings){

		});
	    }
	}
	
	function loadAll()
	{
	    clearMessage();
	    $.ajax({
		dataType: 'json',
		url: "../include/ajaxHelper.php",
		type: "POST",
		data: {
		    method: "loadAll"
		}
	    }).success(function(data){
		var html = "";
		$.each(data.result, function(i, v)
		{
		    html += writeTableRow(v);
		});
		$("#dataBody").html(html);
	    }).complete(function(event, xhr, settings){
		
	    }).error(function(event, xhr, settings){
		if(xhr == "parsererror")
		{
		    $("#message").html("Ein Datenbankfehler ist aufgetreten.");
		}
	    });
	}
	
	function writeTableRow(rowData)
	{
	    var cardText = (rowData.cardtext!==null ? rowData.cardtext : "");
	    var row ='<tr id="row_'+rowData.id+'">';
		row += '<td class="col-md-1">'+rowData.id+'</td>';
		row += '<td class="cardnumber col-md-3">'+rowData.cardnumber+'</td>';
		row += '<td class="cardtext col-md-4">'+cardText+'</td>';
		row += '<td class="ablaufdatum col-md-2">'+rowData.ablaufdatum.substring(0,10)+'</td>';
		row += '<td class="col-md-1">';
		row += '<button type="button" class="btn btn-default btn-xs" onclick="edit(\''+rowData.id+'\');">';
		row += '<span class="glyphicon glyphicon-pencil" aria-hidden="true">';
		row += '</button>';
		row += '</td>';
		row += '<td class="col-md-1">';
		row += '<button type="button" class="btn btn-default btn-xs btn-danger" onclick="deleteId(\''+rowData.id+'\');">';
		row += '<span class="glyphicon glyphicon-remove" aria-hidden="true">';
		row += '</button>';
		row += '</td>';
		row += '</tr>';
	    return row;
	}
	
	function save()
	{
	    clearMessage();
	    var cardNumber = $("#cardnumber").val();
	    var cardText = $("#cardtext").val();
	    var ablaufdatum = $("#ablaufdatum").val();
	    var validation = true;
	    if(cardNumber === "")
	    {
		$("#cardnumber").parent().addClass("has-error");
		//$("#cardnumber").parent().addClass("has-feedback");
		validation = false;
	    }
	    
//	    if(cardText === "")
//	    {
//		$("#cardtext").parent().addClass("has-error");
//		//$("#cardtext").parent().addClass("has-feedback");
//		return;
//		validation = false;
//	    }
	    if(ablaufdatum === "")
	    {
		$("#ablaufdatum").parent().addClass("has-error");
		//$("#cardnumber").parent().addClass("has-feedback");
		validation = false;
	    }
	    
	    if(validation)
	    {
		$.ajax({
		    dataType: 'json',
		    url: "../include/ajaxHelper.php",
		    type: "POST",
		    data: {
			method: "save",
			cardnumber: cardNumber,
			cardtext: cardText,
			ablaufdatum: ablaufdatum
		    }
		}).success(function(data){
		    if(data.error != "true")
		    {
			var html = "";
			html += writeTableRow(data.result);
			$("#dataBody").append(html);
			clearForm();
		    }
		    else
		    {
			$("#message").html(data.errormsg);
		    }
		}).complete(function(event, xhr, settings){
		    
		});
	    }	
	}
	
	function update(id)
	{
	    clearMessage();
	    var cardNumber = $("#cardnumber").val();
	    var cardText = $("#cardtext").val();
	    var ablaufdatum = $("#ablaufdatum").val();
	    
	    var validation = true;
	    if(cardNumber === "")
	    {
		$("#cardnumber").parent().addClass("has-error");
		//$("#cardnumber").parent().addClass("has-feedback");
		validation = false;
	    }
	    
	    if(cardText === "")
	    {
		$("#cardtext").parent().addClass("has-error");
		//$("#cardtext").parent().addClass("has-feedback");
		return;
		validation = false;
	    }
	    
	    if(ablaufdatum === "")
	    {
		$("#ablaufdatum").parent().addClass("has-error");
		//$("#cardnumber").parent().addClass("has-feedback");
		validation = false;
	    }
	    
	    if(validation)
	    {
		$.ajax({
		    dataType: 'json',
		    url: "../include/ajaxHelper.php",
		    type: "POST",
		    data: {
			method: "update",
			cardnumber: cardNumber,
			cardtext: cardText,
			id: id,
			ablaufdatum: ablaufdatum
		    }
		}).success(function(data){
		    if(data.error != "true")
		    {
			var html = "";
			html += writeTableRow(data.result);
			$("#row_"+id).replaceWith(html);
			clearForm();
		    }
		    else
		    {
			$("#message").html(data.errormsg);
		    }
		});
	    }
	}
	
	function edit(rowId)
	{
	    clearMessage();
	    $("#cardnumber").val($("#row_"+rowId+" .cardnumber").text());
	    $("#cardtext").val($("#row_"+rowId+" .cardtext").text());
	    $("#ablaufdatum").val($("#row_"+rowId+" .ablaufdatum").text());
	    $("#saveButton").attr("onclick", "update(\""+rowId+"\")");
	    if($("#abortEditButton").length === 0)
		$("#cardForm").append('<button id="abortEditButton" type="button" class="btn btn-default btn-danger" onclick="clearForm();">Abbrechen</button>');
	}
	
	function clearForm()
	{
	    $("#cardnumber").val("");
	    $("#cardtext").val("");
	    $("#ablaufdatum").val("");
	    $("#cardnumber").parent().removeClass("has-error");
	    $("#cardtext").parent().removeClass("has-error");
	    $("#ablaufdatum").parent().removeClass("has-error");
	    $("#abortEditButton").remove();
	    $("#saveButton").attr("onclick", "save()");
	}
	
	function clearMessage()
	{
	    $("#message").html("");
	}
    </script>
    <style type="text/css">
	body
	{
	    padding: 1em;
	}

	#formWrapper
	{
	    width:500px;
	}
	
	#dataWrapper
	{
	    width: 600px;
	    margin-top: 1em;
	}
	
	table
	{
	    font-size: 1.2em;
	}
	
	#dataWrapper h3{
	}
	
	#dataWrapper .form-group{
	    margin-bottom: 0;
	}

    </style>
    <title><?php echo $addon_name?></title>
</head>
<body>
<h1><?php echo $addon_name?> - Dateneingabe</h1>
<?php

$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('addon/externeAusweise'))
{
    die('Sie haben keine Berechtigung fuer diese Seite');
}
?>
<div id="formWrapper">
    <form id="cardForm" method="POST">
	<div class="form-group">
	    <label class="control-label" for="cardnumber">Kartennummer</label>
	    <input type="text" class="form-control" id="cardnumber" name="cardnumber">
	</div>
	<div class="form-group">
	    <label class="control-label" for="cardtext">Kartentext</label>
	    <input type="text" class="form-control" id="cardtext" name="cardtext">
	</div>
	<div class="form-group">
	    <label class="control-label" for="ablaufdatum">Ablaufdatum</label>
	    <input type="text" class="form-control" id="ablaufdatum" name="ablaufdatum" placeholder="YYYY-MM-DD">
	</div>
	<button id="saveButton" type="button" class="btn btn-default btn-success" onclick="save();">Speichern</button>
    </form>
    <div id="message"></div>
</div>
<div id="dataWrapper">
    <h3>Kartendaten</h3>
    <div class="form-group">
	<input type="text" class="form-control" id="search" name="search" placeholder="Search...">
	<input type="hidden" id="carnumber_search" value=""/>
    </div>
    <table class="table">
	<thead>
	    <tr>
		<th>#</th>
		<th>Kartennummer</th>
		<th>Text</th>
		<th>Ablaufdatum</th>
		<th></th>
		<th></th>
	    </tr>
	</thead>
	<tbody id="dataBody">
	    <!--Data generated by Javascript loadAll() -->
	</tbody>
    </table>
</div>
    


