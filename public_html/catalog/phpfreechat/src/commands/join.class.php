<?php

require_once(dirname(__FILE__)."/../pfccommand.class.php");
set_include_path('/home/student/public_html/catalog/');
require_once('includes/application_top.php');
/*
$link=dirname(__FILE__)."/../../../includes/application_top.php";

if(file_exists(dirname(__FILE__)."/../../../includes/application_top.php"))
{
echo  get_include_path();
}
*/
class pfcCommand_join extends pfcCommand
{
  var $usage = "/join {channelname}";
  
  function run(&$xml_reponse, $p)
  {
    $clientid    = $p["clientid"];
    $param       = $p["param"];
    $sender      = $p["sender"];
    $recipient   = $p["recipient"];
    $recipientid = $p["recipientid"];
	

    $c =& pfcGlobalConfig::Instance();
    $u =& pfcUserConfig::Instance();

    $channame  = trim($param);
    $chanrecip = pfcCommand_join::GetRecipient($channame);
    $chanid    = pfcCommand_join::GetRecipientId($channame);
	
	//Modification of the file for assignement
	
    $verify=true;
	
	
	if(isset($p['verify'])){
		$verify=$p['verify'];
	}
	
	
	$safe_channame=mysql_real_escape_string($channame);
	$safe_sender=mysql_real_escape_string($sender);
	
	
	$check_room_query=tep_db_query("select count(*) as total ,room_owner from " . TABLE_CHAT_ROOM . " where room_name = '" . $safe_channame . "'");
    $check_room=tep_db_fetch_array($check_room_query);
	
	
	$room_owner=$check_room['room_owner'];
	if($check_room['total']==0)
	{
		$room_owner=$safe_sender;
		$check_chat_query = tep_db_query("INSERT INTO `".TABLE_CHAT_ROOM."`(`room_name`, `room_owner`) VALUES ('".$safe_channame."','".$room_owner."')");
		
	}
	
	
	if (($room_owner!=$safe_sender AND $safe_channame!="Common Room") AND $verify AND $safe_channame!="")
    {
      $cmdp = $p;
      $cmdp["param"] = _pfc("You can't join this room");
      $cmdp["param"] .= " (".$this->usage.")";
      $cmd =& pfcCommand::Factory("error");
      $cmd->run($xml_reponse, $cmdp);
      return;
    }
	 //isset($res['verify']) ? $res['verify']:true;   

	

	

    
    if(!isset($u->channels[$chanid]))
    {
      if ($c->max_channels <= count($u->channels))
      {
        // the maximum number of joined channels has been reached
        $xml_reponse->script("pfc.handleResponse('".$this->name."', 'max_channels', Array());");
        return;
      }

      $u->channels[$chanid]["recipient"] = $chanrecip;
      $u->channels[$chanid]["name"]      = $channame;
      $u->saveInCache();
      
      // show a join message
      $cmdp = $p;
      $cmdp["param"] = _pfc("%s joins %s",$u->getNickname(), $channame);
      $cmdp["recipient"] = $chanrecip;
      $cmdp["recipientid"] = $chanid;
      $cmdp["flag"] = 2;
      $cmd =& pfcCommand::Factory("notice");
      $cmd->run($xml_reponse, $cmdp);
    }

    // register the user (and his metadata) in the channel
    $ct =& pfcContainer::Instance();
    //    $ct->createNick($chanrecip, $u->nick, $u->nickid);
    $ct->joinChan($u->nickid, $chanrecip);
    $this->forceWhoisReload($u->nickid);
    
    // return ok to the client
    // then the client will create a new tab
    $xml_reponse->script("pfc.handleResponse('".$this->name."', 'ok', Array('".$chanid."','".addslashes($channame)."'));");
  }

  function GetRecipient($channame)
  {
    return "ch_".$channame;
  }

  function GetRecipientId($channame)
  {
    return md5(pfcCommand_join::GetRecipient($channame));
  }
  
}

?>