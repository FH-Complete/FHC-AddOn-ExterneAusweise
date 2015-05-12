<?php
header( 'Expires:  -1' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Pragma: no-cache' );
header('Content-Type: text/html;charset=UTF-8');

require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once ('./idCard.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('addon/externeAusweise'))
{
    die('Sie haben keine Berechtigung fuer diese Seite');
}

$method = filter_input(INPUT_POST, "method");

switch($method)
{
    case 'save':
	$cardNumber = filter_input(INPUT_POST,"cardnumber");
	$cardText = filter_input(INPUT_POST,"cardtext");
	$ablaufdatum = filter_input(INPUT_POST,"ablaufdatum");
	
	if(checkAblaufdatum($ablaufdatum))
	{
	    if((!is_null($cardNumber)) && (!is_null($cardText)) && (!is_null($ablaufdatum)))
	    {
		$data = save($cardNumber, $cardText, $ablaufdatum);
	    }
	}
	else
	{
	    $data['result']="";
	    $data['error']='true';
	    $data['errormsg']='Ungültiges Datum.';
	}
	break;
    case 'update':
	$cardNumber = filter_input(INPUT_POST,"cardnumber");
	$cardText = filter_input(INPUT_POST,"cardtext");
	$ablaufdatum = filter_input(INPUT_POST,"ablaufdatum");
	$id = filter_input(INPUT_POST,"id");
	
	if(checkAblaufdatum($ablaufdatum))
	{
	    if((!is_null($cardNumber)) && (!is_null($cardText)) && (!is_null($id)) && (!is_null($ablaufdatum)))
	    {
		$data = update($id, $cardNumber, $cardText, $ablaufdatum);
	    }
	}
	else
	{
	    $data['result']="";
	    $data['error']='true';
	    $data['errormsg']='Ungültiges Datum.';
	}
	break;
    case 'loadAll':
	$data = loadAll();
	break;
    case 'delete':
	$id = filter_input(INPUT_POST, "id");
	if((!is_null($id)) && ($id!=false))
	{
	    $data = delete($id);
	}
	break;
    default:
	$data['result']="";
	$data['error']='true';
	$data['errormsg']='Method not supported.';
	break;
}

echo json_encode($data);

/**
 * Beginn Funktionen
 */

function loadAll()
{
    $idCard = new idCard();
    if($idCard->getAll())
    {
	$data["result"] = $idCard->result;
	$data['error']='false';
    }
    else
    {
	$data['result']="";
	$data['error']='true';
	$data['errormsg']='Daten konnten nicht geladen werden.';
    }
    return $data;
}

function delete($id)
{
    $idCard = new idCard();
    if($idCard->delete($id))
    {
	$data['result']="true";
	$data['error']='false';
	$data['errormsg']='Datensatz gelöscht. ID: '.$id;
    }
    else
    {
	$data['result']="";
	$data['error']='true';
	$data['errormsg']='Datensatz konnte nicht gelöscht werden. ID: '.$id;
    }
    return $data;
}

function save($cardNumber, $cardText, $ablaufdatum)
{   
    $idCard = new idCard();
    $idCard->loadByCardnumber($cardNumber);
    
    if(is_null($idCard->id))
    {
	$idCard->cardnumber = $cardNumber;
	$idCard->cardtext = $cardText;
	$idCard->ablaufdatum = $ablaufdatum;

	if($idCard->save())
	{
	    $temp = new idCard();
	    $temp->loadByCardnumber($cardNumber);
	    $data['result']=$temp;
	    $data['error']='false';
	    $data['errormsg']='Datensatz gespeichert. ID: '.$temp->id;
	}
	else
	{
	    $data['result']="";
	    $data['error']='true';
	    $data['errormsg']='Datensatz konnte nicht gespeichert werden.';
	}
    }
    else
    {
	$data['result']="";
	$data['error']='true';
	$data['errormsg']='Datensatz konnte nicht gespeichert werden. Kartennummer bereits vorhanden.';
    }
    return $data;
}

function update($id, $cardnumber, $cardtext, $ablaufdatum)
{
    $idCard = new idCard();
    $idCard->loadByCardnumber($cardnumber);
    if((is_null($idCard->id)) || ($id == $idCard->id))
    {
	if($idCard->load($id))
	{
	    $idCard->cardnumber = $cardnumber;
	    $idCard->cardtext = $cardtext;
	    $idCard->ablaufdatum = $ablaufdatum;
	    if($idCard->save())
	    {
		$data['result']=$idCard;
		$data['error']='false';
		$data['errormsg']='Datensatz erfolgreich gespeichert.';
	    }
	    else
	    {
		$data['result']="";
		$data['error']='true';
		$data['errormsg']='Fehler beim Speichern der Daten.';
	    }
	}
	else
	{
	    $data['result']="";
	    $data['error']='true';
	    $data['errormsg']='Datensatz konnte nicht gespeichert werden. ID ('.$id.') nicht vorhanden';
	}
    }
    else
    {
	$data['result']="";
	$data['error']='true';
	$data['errormsg']='Datensatz konnte nicht gespeichert werden. Kartennummer bereits vorhanden.';
    }
    return $data;
}

function checkAblaufdatum($ablaufdatum)
{
    $date_array = explode("-", $ablaufdatum);
    if(count($date_array) === 3)
	return checkdate($date_array[1], $date_array[2], $date_array[0]);
    else
	return false;
}