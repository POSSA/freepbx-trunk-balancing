<?php /* $Id */
// TrunkBalance
// (c) Patrick
//
//$module_info = xml2array("modules/trunkbalance/module.xml");


isset($_REQUEST['action'])?$action = $_REQUEST['action']:$action='';

//the item we are currently displaying
isset($_REQUEST['itemid'])?$itemid=$_REQUEST['itemid']:$itemid='';

$dispnum = "trunkbalance"; //used for switch on config.php

$tabindex = 0;

//if submitting form, update database
if(isset($_POST['action'])) {
	switch ($action) {
		case "add":
			trunkbalance_add($_POST);
			needreload();
			redirect_standard();
		break;
		case "delete":
			trunkbalance_del($itemid);
			needreload();
			redirect_standard();
		break;
		case "edit":
			trunkbalance_edit($itemid,$_POST);
			needreload();
			redirect_standard('itemid');
		break;
	}
}

//get list of trunks
$trunkbalances = trunkbalance_list();
?>

</div> <!-- end content div so we can display rnav properly-->

<!-- right side menu -->
<div class="rnav"><ul>
    <li><a id="<?php echo ($itemid=='' ? 'current':'') ?>" href="config.php?display=<?php echo urlencode($dispnum)?>"><?php echo _("Add Load balanced Trunk")?></a></li>
<?php
if (isset($trunkbalances)) {
	foreach ($trunkbalances as $trunkbalance) {
		if ($trunkbalance['trunkbalance_id'] != 0)
			echo "<li><a id=\"".($itemid==$trunkbalance['trunkbalance_id'] ? 'current':'')."\" href=\"config.php?display=".urlencode($dispnum)."&itemid=".urlencode($trunkbalance['trunkbalance_id'])."\">{$trunkbalance['description']}</a></li>";
	}
}
?>
</ul></div>

<div class="content">
<?php
if ($action == 'delete') {
	echo '<br><h3>'._("Balanced Trunk").' '.$itemid.' '._("deleted").'!</h3>';
} else {
	if ($itemid){ 
		//get details for this source
		$thisItem = trunkbalance_get($itemid);
	} else {
		$thisItem = Array( 'description' => null, 'desttrunk_id' => null);
	}

	$delURL = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'].'&action=delete';
	$delButton = "
			<form name=delete action=\"{$_SERVER['PHP_SELF']}\" method=POST>
				<input type=\"hidden\" name=\"display\" value=\"{$dispnum}\">
				<input type=\"hidden\" name=\"itemid\" value=\"{$itemid}\">
				<input type=\"hidden\" name=\"action\" value=\"delete\">
				<input type=submit value=\""._("Delete Balanced Trunk")."\">
			</form>";
	
?>

	<h2><?php echo ($itemid ? _("Balanced Trunk:")." ". $itemid : _("Add Balanced Trunk")); ?></h2>

	<p style="width: 80%"><?php echo ($itemid ? '' : _("Each Balanced Trunk is an outbound trunk associated with a set of parameters to define the maximum use you want to do with it. For instance you have a provider that gives you 100 minutes long distance calls per month. You can define here that after 100 minutes of local call during the month this trunk will become unavailable and your route will switch to the next trunk in line.")); ?></p>

<?php		if ($itemid){  echo $delButton; 	} ?>

<form autocomplete="off" name="edit" action="<?php $_SERVER['PHP_SELF'] ?>" method="post" onsubmit="return edit_onsubmit();">
	<input type="hidden" name="display" value="<?php echo $dispnum?>">
	<input type="hidden" name="action" value="<?php echo ($itemid ? 'edit' : 'add') ?>">
	<table>
	<tr><td colspan="2"><h5><?php echo ($itemid ? _("Edit Trunk") : _("Add Trunk")) ?><hr></h5></td></tr>

<?php		if ($itemid){ ?>
		<input type="hidden" name="itemid" value="<?php echo $itemid; ?>">
<?php		}?>

	<tr>
		<td><a href="#" class="info"><?php echo _("Trunk Description:")?><span><?php echo _("Enter a description for this balanced trunk.")?></span></a></td>
		<td><input type="text" name="description" value="<?php echo (isset($thisItem['description']) ? $thisItem['description'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
       <tr>
	<td><a href="#" class="info"><?php echo _("Trunk Destination:")?><span><?php echo _("Select the destination trunk")?></span></a></td>
	<td><SELECT id="desttrunk_id" name="desttrunk_id" tabindex="<?php echo ++$tabindex;?>"><OPTION VALUE="0">Select...</option>
<?php     
	    $trunklist = trunkbalance_listtrunk();
           foreach ($trunklist as $trunk){
		if ($trunk['trunkid']!=0){
		   echo _("<OPTION VALUE=\"");
	    	   echo ($trunk['trunkid']);
	          echo _("\"");
		   if ($thisItem['desttrunk_id']==$trunk['trunkid']) echo _("selected=\"selected\"");
		   echo _(">");
	          echo($trunk['name'].' ('.$trunk['tech'].')');
	          echo _("</OPTION>");
		} 
	    }?>
</SELECT></td>
       </tr>
       <tr>
	<td><a href="#" class="info"><?php echo _("Time Group:")?><span><?php echo _("Select the time group condition")?></span></a></td>
	<td><SELECT id="timegroup_id" name="timegroup_id" tabindex="<?php echo ++$tabindex;?>"><OPTION VALUE="-1">none selected</option>
           <?php     
	    $timegrouplist = trunkbalance_listtimegroup();
           foreach ($timegrouplist as $timegroup){
		if ($timegroup['id']!=0){
		   echo _("<OPTION VALUE=\"");
	    	   echo ($timegroup['id']);
	          echo _("\"");
		   if ($thisItem['timegroup_id']==$timegroup['id']) echo _("selected=\"selected\"");
		   echo _(">");
	          echo($timegroup['description']);
	          echo _("</OPTION>");
		} 
	    }?>
</SELECT></td>
       </tr>

       <tr>
		<td><a href="#" class="info"><?php echo _("Matching Rule:")?><span><?php echo _("Enter the SQL matching pattern that will be applied to the CDR to calculate your rules on this trunk. It will be inserted as WHERE dst LIKE 'your pattern'. For instance if you want to match all numbers starting by 0033 you will enter 0033%. At this time only one pattern will work. ")?></span></a></td>
		<td><input type="textarea" rows="5" cols="25" name="dialpattern" value="<?php echo (isset($thisItem['dialpattern']) ? $thisItem['dialpattern'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>

	<tr>
		<td><a href="#" class="info"><?php echo _("Not Matching Rule:")?><span><?php echo _("Enter the matching pattern that will be excluded from the CDR matching to calculate your rules on this trunk.It will be inserted as WHERE dst NOT LIKE 'your pattern'. ")?></span></a></td>
		<td><input type="textarea" rows="5" cols="25" name="notdialpattern" value="<?php echo (isset($thisItem['notdialpattern']) ? $thisItem['notdialpattern'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>


       <tr>
		<td><a href="#" class="info"><?php echo _("Billing Day:")?><span><?php echo _("Enter the day of the month when to reset the counter. 0 for never reseting the counter. If this field is used, you should set Billing Period to 0")?></span></a></td>
		<td><input type="text" name="billingday" value="<?php echo (isset($thisItem['billingday']) ? $thisItem['billingday'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	<tr>
		<td><a href="#" class="info"><?php echo _("Billing Period :")?><span><?php echo _("Enter the number of floating days that should be included in the count. 0 to include all. If this field is used, you should set Billing Days to 0.")?></span></a></td>
		<td><input type="text" name="billingperiod" value="<?php echo (isset($thisItem['billingperiod']) ? $thisItem['billingperiod'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>
	

       <tr>
		<td><a href="#" class="info"><?php echo _("Ending Date:")?><span><?php echo _("Enter the date when this balanced trunk should expire. YYYY-MM-DD HH:mm - Keep empty to disable")?></span></a></td>
		<td><input type="text" name="endingdate" value="<?php echo (isset($thisItem['endingdate']) ? $thisItem['endingdate'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>

       <tr>
		<td><a href="#" class="info"><?php echo _("Load Ratio:")?><span><?php echo _("Enter the ratio this trunk should accept call. For instance if you want to balance equaly between this balanced trunk and two following ones, you should enter 3 to let this trunk accept 1 out of 3 calls.")?></span></a></td>
		<td><input type="text" name="loadratio" value="<?php echo (isset($thisItem['loadratio']) ? $thisItem['loadratio'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>

       <tr>
		<td><a href="#" class="info"><?php echo _("Maximum Time:")?><span><?php echo _("Enter the maximum number of minutes per billing period. Be aware that the test is performed before the begining of the call and it will not break an active call even if it lasts two hours")?></span></a></td>
		<td><input type="text" name="maxtime" value="<?php echo (isset($thisItem['maxtime']) ? $thisItem['maxtime'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>

       <tr>
		<td><a href="#" class="info"><?php echo _("Maximum Number of Calls:")?><span><?php echo _("Enter the maximum number of call per billing period.")?></span></a></td>
		<td><input type="text" name="maxnumber" value="<?php echo (isset($thisItem['maxnumber']) ? $thisItem['maxnumber'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>

       <tr>
		<td><a href="#" class="info"><?php echo _("Max. Different Calls:")?><span><?php echo _("Enter the maximum of different phone numbers dialled per billing period")?></span></a></td>
		<td><input type="text" name="maxidentical" value="<?php echo (isset($thisItem['maxidentical']) ? $thisItem['maxidentical'] : ''); ?>" tabindex="<?php echo ++$tabindex;?>"></td>
	</tr>

	<tr>
		<td colspan="2"><br><h6><input name="submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>"></h6></td>		
	</tr>

	</table>


<script language="javascript">
<!--


var theForm = document.edit;
theForm.description.focus();

//displaySourceParameters(document.getElementById('sourcetype'), document.getElementById('sourcetype').selectedIndex);

function edit_onsubmit() {
	
	if (isEmpty(theForm.description.value)) return warnInvalid(theForm.description, "Please enter a valid description");
	if (!isAlphanumeric(theForm.description.value)) return warnInvalid(theForm.description, "Please enter a valid description");

	if ((theForm.desttrunk_id.value)=="0") return warnInvalid(theForm.desttrunk_id, "Please select a valid trunk");

	
		
	return true;
}


-->
</script>
</form>


<?php		
} //end if action == delete
?>