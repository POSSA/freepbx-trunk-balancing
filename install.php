<?php
print 'Installing Trunk Balance<br>';

global $db;
global $amp_conf;


$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") || ($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";

$cols['desttrunk_id'] = "INTEGER default '0'";
$cols['description'] = "varchar(50) default NULL";
$cols['dialpattern'] = "varchar(255) default NULL";
$cols['notdialpattern'] = "varchar(255) default NULL";
$cols['billingday'] = "SMALLINT default '0'";
$cols['billingperiod'] = "SMALLINT default '0'";
$cols['endingdate'] = "datetime default NULL";
$cols['loadratio'] = "INTEGER default '1'";
$cols['maxtime'] = "INTEGER default '-1'";
$cols['maxnumber'] = "INTEGER default '-1'";
$cols['maxidentical'] = "INTEGER default '-1'";
$cols['timegroup_id'] = "INTEGER default '-1'";



// create the tables
$sql = "CREATE TABLE IF NOT EXISTS `trunkbalance` (
	trunkbalance_id INTEGER NOT NULL PRIMARY KEY $autoincrement,
	desttrunk_id INTEGER default '0',
	description varchar(50) default NULL,
	dialpattern varchar(255) default NULL,
	notdialpattern varchar(255) default NULL,
	billingday SMALLINT default '0',
	billingperiod SMALLINT default '0',
	endingdate datetime default NULL,
	loadratio INTEGER default '1',
	maxtime INTEGER default '-1',
	maxnumber INTEGER default '-1',
	maxidentical INTEGER default '-1',
       timegroup_id INTEGER default '-1'
);";
$check = $db->query($sql);
if (DB::IsError($check)) {
        die_freepbx( "Can not create `trunkbalance` table: " . $check->getMessage() .  "\n");
}




//check to see that the proper columns are in the table.
$curret_cols = array();
$sql = "DESC trunkbalance";
$res = $db->query($sql);
while($row = $res->fetchRow())
{
	if(array_key_exists($row[0],$cols))
	{
		$curret_cols[] = $row[0];
		//make sure it has the latest definition
		$sql = "ALTER TABLE trunkbalance MODIFY ".$row[0]." ".$cols[$row[0]];
		$check = $db->query($sql);
		if (DB::IsError($check))
		{
			die_freepbx( "Can not update column ".$row[0].": " . $check->getMessage() .  "<br>");
		}
	}
/*	else
	{
		//remove the column
		$sql = "ALTER TABLE trunkbalance DROP COLUMN ".$row[0];
		$check = $db->query($sql);
		if(DB::IsError($check))
		{
			die_freepbx( "Can not remove column ".$row[0].": " . $check->getMessage() .  "<br>");
		}
		else
		{
			print 'Removed no longer needed column '.$row[0].' from trunkbalance table.<br>';
		}
	}*/
}

//add columns that are not already in the table
foreach($cols as $key=>$val)
{
	if(!in_array($key,$curret_cols))
	{
		$sql = "ALTER TABLE trunkbalance ADD ".$key." ".$val;
		$check = $db->query($sql);
		if (DB::IsError($check))
		{
			die_freepbx( "Can not add column ".$key.": " . $check->getMessage() .  "<br>");
		}
		else
		{
			print 'Added column '.$key.' to trunkbalance table.<br>';
		}
	}
}


?>
