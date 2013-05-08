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
			$ext->splice('macro-dialout-trunk','s',1, new ext_agi('trunkbalance.php,${ARG1},${ARG2}'));			
			
		break;
	}
}

	
function trunkbalance_vercheck() {	
	// compare version numbers of local module.xml and remote module.xml 
	// returns true if a new version is available
	$newver = false;
	$module_local = trunkbalance_xml2array("modules/trunkbalance/module.xml");
	$module_remote = trunkbalance_xml2array("https://raw.github.com/POSSA/freepbx-trunk-balancing/master/module.xml");
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

	//Parse XML file into an array
function trunkbalance_xml2array($url, $get_attributes = 1, $priority = 'tag')  {
	$contents = "";
	if (!function_exists('xml_parser_create'))
	{
		return array ();
	}
	$parser = xml_parser_create('');
	if(!($fp = @ fopen($url, 'rb')))
	{
		return array ();
	}
	while(!feof($fp))
	{
		$contents .= fread($fp, 8192);
	}
	fclose($fp);
	xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, trim($contents), $xml_values);
	xml_parser_free($parser);
	if(!$xml_values)
	{
		return; //Hmm...
	}
	$xml_array = array ();
	$parents = array ();
	$opened_tags = array ();
	$arr = array ();
	$current = & $xml_array;
	$repeated_tag_index = array ();
	foreach ($xml_values as $data)
	{
		unset ($attributes, $value);
		extract($data);
		$result = array ();
		$attributes_data = array ();
		if (isset ($value))
		{
			if($priority == 'tag')
			{
				$result = $value;
			}
			else
			{
				$result['value'] = $value;
			}
		}
		if(isset($attributes) and $get_attributes)
		{
			foreach($attributes as $attr => $val)
			{
				if($priority == 'tag')
				{
					$attributes_data[$attr] = $val;
				}
				else
				{
					$result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
				}
			}
		}
		if ($type == "open")
		{
			$parent[$level -1] = & $current;
			if(!is_array($current) or (!in_array($tag, array_keys($current))))
			{
				$current[$tag] = $result;
				if($attributes_data)
				{
					$current[$tag . '_attr'] = $attributes_data;
				}
				$repeated_tag_index[$tag . '_' . $level] = 1;
				$current = & $current[$tag];
			}
			else
			{
				if (isset ($current[$tag][0]))
				{
					$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
					$repeated_tag_index[$tag . '_' . $level]++;
				}
				else
				{
					$current[$tag] = array($current[$tag],$result);
					$repeated_tag_index[$tag . '_' . $level] = 2;
					if(isset($current[$tag . '_attr']))
					{
						$current[$tag]['0_attr'] = $current[$tag . '_attr'];
						unset ($current[$tag . '_attr']);
					}
				}
				$last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
				$current = & $current[$tag][$last_item_index];
			}
		}
		else if($type == "complete")
		{
			if(!isset ($current[$tag]))
			{
				$current[$tag] = $result;
				$repeated_tag_index[$tag . '_' . $level] = 1;
				if($priority == 'tag' and $attributes_data)
				{
					$current[$tag . '_attr'] = $attributes_data;
				}
			}
			else
			{
				if (isset ($current[$tag][0]) and is_array($current[$tag]))
				{
					$current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
					if ($priority == 'tag' and $get_attributes and $attributes_data)
					{
						$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
					}
					$repeated_tag_index[$tag . '_' . $level]++;
				}
				else
				{
					$current[$tag] = array($current[$tag],$result);
					$repeated_tag_index[$tag . '_' . $level] = 1;
					if ($priority == 'tag' and $get_attributes)
					{
						if (isset ($current[$tag . '_attr']))
						{
							$current[$tag]['0_attr'] = $current[$tag . '_attr'];
							unset ($current[$tag . '_attr']);
						}
						if ($attributes_data)
						{
							$current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
						}
					}
					$repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
				}
			}
		}
		else if($type == 'close')
		{
			$current = & $parent[$level -1];
		}
	}
	return ($xml_array);
}
