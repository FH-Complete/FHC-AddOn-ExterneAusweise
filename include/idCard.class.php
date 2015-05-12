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
 * Authors: Stefan Puraner <puraner@technikum-wien.at>
 */
require_once(dirname(__FILE__).'/../../../include/basis_db.class.php');

class idCard extends basis_db
{
    public $new;
    public $id;
    public $cardnumber;
    public $cardtext;
    public $ablaufdatum;
    public $result = array();
    /**
    * Konstruktor
    */
   public function __construct($cardnumber=NULL)
   {
	
	parent::__construct();
	
	if(!is_null($cardnumber))
	    $this->load($cardnumber);
	else
	    $this->new = true;
   }
   
   public function __get($property)
   {
       if(property_exists($this, $property))
       {
	   return $this->$property;
       }
       else
       {
	   return NULL;
       }
   }
   
   public function __set($property, $value)
   {
       if(property_exists($this, $property))
       {
	    if($property !== "id")
		$this->$property = $value;
       }
   }
   
   public function load($id)
   {
	$qry = "SELECT * FROM addon.tbl_externeausweise"
		. " WHERE id=".$this->db_add_param($id).";";

	if(!$this->db_query($qry))
	{
	    $this->errormsg = "Fehler beim Laden der Daten";
	    return FALSE;
	}
	if($result = $this->db_fetch_object())
	{
	    $this->id = $result->id;
	    $this->cardnumber = $result->cardnumber;
	    $this->cardtext = $result->cardtext;
	    $this->ablaufdatum = $result->ablaufdatum;
	    $this->new = false;
	    return true;
	}
   }
   
   public function save()
   {
       if($this->new)
       {
	   $qry = 'INSERT INTO addon.tbl_externeausweise (cardnumber, cardtext, ablaufdatum)'
	       . ' VALUES ('
		   .$this->db_add_param($this->cardnumber).', '
		   .$this->db_add_param($this->cardtext).', '
		   .$this->db_add_param($this->ablaufdatum).');';
       }
       else
       {
	   $qry = 'UPDATE addon.tbl_externeausweise SET '
		   . 'cardnumber='.$this->db_add_param($this->cardnumber).', '
		   . 'cardtext='.$this->db_add_param($this->cardtext).', '
		   . 'ablaufdatum='.$this->db_add_param($this->ablaufdatum).''
		   . ' WHERE id='.$this->db_add_param($this->id).';';
       }
       
       
       if(!$this->db_query($qry))
       {
	   $this->errormsg = "Fehler beim Speichern der Daten";
	   return false;
       }
       return true;
   }
   
   public function getAll()
   {
       $qry = "SELECT * FROM addon.tbl_externeausweise ORDER BY id;";
       if($this->db_query($qry))
       {
	   while($row = $this->db_fetch_object())
	   {
	       $stdobj = new stdClass();
	       $stdobj->id = $row->id;
	       $stdobj->cardnumber = $row->cardnumber;
	       $stdobj->cardtext = $row->cardtext;
	       $stdobj->ablaufdatum = $row->ablaufdatum;
	       array_push($this->result, $stdobj);
	   }
	   return true;
       }
       else
       {
	   $this->errormsg = "Fehler beim Laden der Daten";
	   return false;
       }
   }
   
   public function delete($id)
   {
       $qry = 'DELETE FROM addon.tbl_externeAusweise WHERE id='.$this->db_add_param($id).';';
       
       if(!$this->db_query($qry))
       {
	   $this->errormsg = "Fehler beim Löschen der Daten";
	   return false;
       }
       return true;
   }
   
   public function loadByCardnumber($cardnumber)
   {
	$qry = "SELECT * FROM addon.tbl_externeausweise"
		. " WHERE cardnumber=".$this->db_add_param($cardnumber).";";

	if(!$this->db_query($qry))
	{
	    $this->errormsg = "Fehler beim Laden der Daten";
	    return FALSE;
	}
	if($result = $this->db_fetch_object())
	{
	    $this->id = $result->id;
	    $this->cardnumber = $result->cardnumber;
	    $this->cardtext = $result->cardtext;
	    $this->ablaufdatum = $result->ablaufdatum;
	    $this->new = false;
	    return true;
	}
   }
   
   public function search($filter)
   {
	$qry = "SELECT *
	    FROM addon.tbl_externeausweise
	    WHERE 
		    lower(cardnumber) like lower('%".$this->db_escape($filter)."%')
	    ORDER BY cardnumber";
       
	if($this->db_query($qry))
	{
	    while($row = $this->db_fetch_object())
	   {
	       $stdobj = new stdClass();
	       $stdobj->id = $row->id;
	       $stdobj->cardnumber = $row->cardnumber;
	       $stdobj->cardtext = $row->cardtext;
	       $stdobj->ablaufdatum = $row->ablaufdatum;
	       array_push($this->result, $stdobj);
	   }
	   return true;
	}
	else
	{
	    $this->errormsg = 'Fehler beim Laden der Daten';
	    return false;
	}
   }
}