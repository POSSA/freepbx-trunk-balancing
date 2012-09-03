<?php

function trunkbalance_list() {
	$allowed = array(array('trunkbalance_id' => 0, 'description' => _("None")));
	$results = sql("SELECT * FROM trunkbalance","getAll",DB_FETCHMODE_ASSOC);
	if(is_array($results)){
		foreach($results as $result){
				$allowed[] = $result;
		}
	}
	return isset($allowed)?$allowed:null;
}

function trunkbalance_listtrunk() {
	$allowed = array(array('trunkid' => 0, 'name' => _("None"), 'tech' => _("None")));
	$sqlr = "SELECT * FROM `trunks` WHERE (name NOT LIKE 'BAL_%') AND (tech!='enum' AND tech!='dundi') ORDER BY tech, name";
	$results = sql($sqlr,"getAll",DB_FETCHMODE_ASSOC);
	if(is_array($results)){
		foreach($results as $result){
				$allowed[] = $result;
		}
	}
	return isset($allowed)?$allowed:null;
}

function trunkbalance_listtimegroup() {
	$allowed = array(array('id' => 0, 'description' => _("None")));
	$sqlr = "SELECT * FROM `timegroups_groups`";
	$results = sql($sqlr,"getAll",DB_FETCHMODE_ASSOC);
	if(is_array($results)){
		foreach($results as $result){
				$allowed[] = $result;
		}
	}
	return isset($allowed)?$allowed:null;
}


function trunkbalance_get($id){
	$results = sql("SELECT * FROM trunkbalance WHERE trunkbalance_id = '$id'","getRow",DB_FETCHMODE_ASSOC);
	return isset($results)?$results:null;
}

function trunkbalance_trunkid($trunkname){
      	$results = sql("SELECT `trunkid` FROM `trunks` WHERE name='BAL_$trunkname'","getOne");
       return isset($results)?$results:null;
}


function trunkbalance_del($id){
	// Deleting source and its associations
	$trunkname = sql("SELECT `description`FROM `trunkbalance` WHERE trunkbalance_id='$id'","getOne");
	$trunknum = trunkbalance_trunkid($trunkname);
	$result = core_trunks_del($trunknum,''); 
	$results = sql("DELETE FROM `trunkbalance` WHERE trunkbalance_id = '$id'","query");
}

function trunkbalance_add($post){
	global $db;

	$description = $db->escapeSimple($post['description']);
       $desttrunk_id = $db->escapeSimple($post['desttrunk_id']);
       $timegroup_id = $db->escapeSimple($post['timegroup_id']);
	$dialpattern = $db->escapeSimple($post['dialpattern']);
	$notdialpattern = $db->escapeSimple($post['notdialpattern']);
	$billingday = $db->escapeSimple($post['billingday']);
	$billingperiod = $db->escapeSimple($post['billingperiod']);
	$endingdate = $db->escapeSimple($post['endingdate']);
	$loadratio = $db->escapeSimple($post['loadratio']);
	$maxtime = $db->escapeSimple($post['maxtime']);
	$maxnumber = $db->escapeSimple($post['maxnumber']);
	$maxidentical = $db->escapeSimple($post['maxidentical']);
	$results = sql("
		INSERT INTO trunkbalance
			(description, desttrunk_id, timegroup_id, dialpattern, notdialpattern, billingday, billingperiod, endingdate, loadratio, maxtime, maxnumber, maxidentical)
		VALUES 
			('$description', '$desttrunk_id', '$timegroup_id', '$dialpattern', '$notdialpattern', '$billingday','$billingperiod', '$endingdate', '$loadratio', '$maxtime', '$maxnumber', '$maxidentical')
		");
	$result=core_trunks_add('custom','Balancedtrunk/'.$description,'','','','','notneeded','','','off','','off','BAL_'.$description,'');

}

function trunkbalance_edit($id,$post){
	global $db;

	$description = $db->escapeSimple($post['description']);
	$desttrunk_id = $db->escapeSimple($post['desttrunk_id']);
       $timegroup_id = $db->escapeSimple($post['timegroup_id']);
	$dialpattern = $db->escapeSimple($post['dialpattern']);
	$notdialpattern = $db->escapeSimple($post['notdialpattern']);	
	$billingday = $db->escapeSimple($post['billingday']);
	$billingperiod = $db->escapeSimple($post['billingperiod']);
	$endingdate = $db->escapeSimple($post['endingdate']);
	$loadratio = $db->escapeSimple($post['loadratio']);
	$maxtime = $db->escapeSimple($post['maxtime']);
	$maxnumber = $db->escapeSimple($post['maxnumber']);
	$maxidentical = $db->escapeSimple($post['maxidentical']);



       $olddescription=sql("SELECT `description`FROM `trunkbalance` WHERE trunkbalance_id='$id'","getOne");

	$results = sql("
		UPDATE trunkbalance 
		SET 
			description = '$description',
			desttrunk_id = '$desttrunk_id',
                     timegroup_id = '$timegroup_id',
			dialpattern = '$dialpattern',
			notdialpattern = '$notdialpattern',
			billingday = '$billingday',
			billingperiod ='$billingperiod',
			endingdate = '$endingdate',
			loadratio = '$loadratio',
			maxtime = '$maxtime',
			maxnumber = '$maxnumber',
			maxidentical ='$maxidentical'
		WHERE trunkbalance_id = '$id'");

	if ($olddescription !== $description) {//need to update the trunk too
		$trunknum = trunkbalance_trunkid($olddescription);
  		$result=core_trunks_edit($trunknum,'Balancedtrunk/'.$description,'','','','','notneeded','','','off','','off','BAL_'.$description,'');

	}
}


function trunkbalance_hookGet_config($engine) {
	global $ext;
	switch($engine) {
		case "asterisk":
			$ext->splice('macro-dialout-trunk','s',1, new ext_agi('trunkbalance.php,${ARG1}'));			
			
		break;
	}
}

	
function trunkbalance_vercheck() {	
	// compare version numbers of local module.xml and remote module.xml 
	// returns true if a new version is available
	$newver = false;
	if ( function_exists(xml2array)){
		$module_local = xml2array("modules/trunkbalance/module.xml");
		$module_remote = xml2array("https://raw.github.com/POSSA/freepbx-trunk-balancing/master/module.xml");
		if ( $foo= empty($module_local) or $bar = empty($module_remote) )
			{
			//  if either array is empty skip version check
			}
		else if ( $module_remote[module][version] > $module_local[module][version])
			{
			$newver = true;
			}
		return ($newver);
		}
	}