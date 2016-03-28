<?php
class CedarParameters extends SpecialPage
{
    var $dbuser, $dbpwd ;
    function CedarParameters() {
	SpecialPage::SpecialPage("CedarParameters");
	#wfLoadExtensionMessages( 'CedarParameters' ) ;

	$this->dbuser = "madrigal" ;
	$this->dbpwd = "shrot-kash-iv-po" ;
    }
    
    function execute( $par )
    {
	global $wgRequest, $wgOut, $wgDBserver, $wgServer ;
	
	$this->setHeaders();

	CedarNote::addScripts() ;

	$action = $wgRequest->getText( 'action' ) ;
	$code = $wgRequest->getInt( 'code' ) ;
	if( $action == 'detail' )
	{
	    $this->parameterDetail( $code ) ;
	    return ;
	}
	else if( $action == "create" )
	{
	    $this->parameterEdit( $code, 1, $action ) ;
	    return ;
	}
	else if( $action == 'edit' )
	{
	    $this->parameterEdit( $code, 0, $action ) ;
	    return ;
	}
	else if( $action == "update" )
	{
	    $this->parameterUpdate( $code ) ;
	    return ;
	}
	else if( $action == 'delete' )
	{
	    $this->parameterDelete( $code ) ;
	    return ;
	}
	else if( $action == "newnote" )
	{
	    $is_successful = CedarNote::newNote( "tbl_parameter_code", "PARAMETER_ID", $code ) ;
	    if( $is_successful )
		$this->parameterDetail( $code ) ;
	    return ;
	}
	else if( $action == "delete_note" )
	{
	    $is_successful = CedarNote::deleteNote( "CedarParameters", "code", $code, "tbl_parameter_code", "PARAMETER_ID" ) ;
	    if( $is_successful )
	    {
		$this->parameterDetail( $code ) ;
	    }
	    return ;
	}
	else if( $action == "edit_note" )
	{
	    $is_successful = CedarNote::editNoteForm( "CedarParameters", "code", $code ) ;
	    if( !$is_successful )
	    {
		$this->parameterDetail( $code ) ;
	    }
	    return ;
	}
	else if( $action == "update_note" )
	{
	    $is_successful = CedarNote::updateNote( ) ;
	    if( $is_successful )
	    {
		$this->parameterDetail( $code ) ;
	    }
	    return ;
	}
	$this->parameterList() ;
    }

    function parameterList()
    {
	global $wgUser, $wgRequest, $wgOut, $wgDBserver, $wgServer ;
	
	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;

	if( $allowed )
	{
	    $wgOut->addHTML( "    <TABLE ALIGN=\"CENTER\" BORDER=\"0\" WIDTH=\"100%\" CELLPADDING=\"0\" CELLSPACING=\"0\">\n" ) ;
	    $wgOut->addHTML( "	<TR>\n" ) ;
	    $wgOut->addHTML( "	    <TD WIDTH=\"100%\" ALIGN=\"LEFT\">\n" ) ;
	    $wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;font-size:11pt;\"><A HREF='$wgServer/wiki/index.php/Special:CedarParameters?action=create'>Create a New Parameter</A></SPAN>\n" ) ;
	    $wgOut->addHTML( "	    </TD>\n" ) ;
	    $wgOut->addHTML( "	</TR>\n" ) ;
	    $wgOut->addHTML( "    </TABLE>\n" ) ;
	    $wgOut->addHTML( "    <BR/>\n" ) ;
	}

	$param = $wgRequest->getText('sort');
	if( $param == "id" )
	{
	    $sort_by = "PARAMETER_ID" ;
	}
	else if( $param == "long" )
	{
	    $sort_by = "LONG_NAME" ;
	}
	else if( $param == "short" )
	{
	    $sort_by = "SHORT_NAME" ;
	}
	else if( $param == "madrigal" )
	{
	    $sort_by = "MADRIGAL_NAME" ;
	}
	else
	{
	    $sort_by = "PARAMETER_ID" ;
	}

	$dbh = new DatabaseMysql( $wgDBserver, $this->dbuser, $this->dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database" ) ;
	}
	else
	{
	    // sort_by variable is created within this code, not passed
	    // in by client, so does not need to be cleaned
	    $res = $dbh->query( "select PARAMETER_ID, LONG_NAME, SHORT_NAME, MADRIGAL_NAME, UNITS, SCALE from tbl_parameter_code ORDER BY $sort_by" ) ;
	    if( !$res )
	    {
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />" ) ;
	    }
	    else
	    {
		$wgOut->addHTML( "    <TABLE ALIGN=\"LEFT\" BORDER=\"1\" WIDTH=\"100%\" CELLPADDING=\"0\" CELLSPACING=\"0\">" ) ;
		$wgOut->addHTML( "	<TR style=\"background-color:gainsboro;\">\n" ) ;
		$wgOut->addHTML( "	    <TD WIDTH=\"12%\" ALIGN=\"CENTER\">" ) ;
		$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;font-size:11pt;\"><A HREF='$wgServer/wiki/index.php/Special:CedarParameters?sort=id'>Code</A></SPAN>" ) ;
		$wgOut->addHTML( "	    </TD>" ) ;
		$wgOut->addHTML( "	    <TD WIDTH=\"40%\" ALIGN=\"CENTER\">" ) ;
		$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;font-size:11pt;\"><A HREF='$wgServer/wiki/index.php/Special:CedarParameters?sort=long'>Long Name</A></SPAN>" ) ;
		$wgOut->addHTML( "	    </TD>" ) ;
		$wgOut->addHTML( "	    <TD WIDTH=\"12%\" ALIGN=\"CENTER\">" ) ;
		$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;font-size:11pt;\"><A HREF='$wgServer/wiki/index.php/Special:CedarParameters?sort=short'>Short Name</A></SPAN>" ) ;
		$wgOut->addHTML( "	    </TD>" ) ;
		$wgOut->addHTML( "	    <TD WIDTH=\"12%\" ALIGN=\"CENTER\">" ) ;
		$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;font-size:11pt;\"><A HREF='$wgServer/wiki/index.php/Special:CedarParameters?sort=madrigal'>Prefix</A></SPAN>" ) ;
		$wgOut->addHTML( "	    </TD>" ) ;
		$wgOut->addHTML( "	    <TD WIDTH=\"12%\" ALIGN=\"CENTER\">" ) ;
		$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;font-size:11pt;\">Units</SPAN>" ) ;
		$wgOut->addHTML( "	    </TD>" ) ;
		$wgOut->addHTML( "	    <TD WIDTH=\"12%\" ALIGN=\"CENTER\">" ) ;
		$wgOut->addHTML( "		<SPAN STYLE=\"font-weight:bold;font-size:11pt;\">Scale</SPAN>" ) ;
		$wgOut->addHTML( "	    </TD>" ) ;
		$wgOut->addHTML( "	</TR>" ) ;
		$rowcolor="white" ;
		while( ( $obj = $dbh->fetchObject( $res ) ) )
		{
		    $code = $obj->PARAMETER_ID ;
		    $long_name = $obj->LONG_NAME ;
		    $short_name = $obj->SHORT_NAME ;
		    $madrigal_name = $obj->MADRIGAL_NAME ;
		    $units = $obj->UNITS ;
		    $scale = $obj->SCALE ;
		    $wgOut->addHTML( "	<TR style=\"background-color:$rowcolor;\">\n" ) ;
		    if( $rowcolor == "white" ) $rowcolor = "gainsboro" ;
		    else $rowcolor = "white" ;
		    $wgOut->addHTML( "	    <TD WIDTH=\"12%\" ALIGN=\"CENTER\">" ) ;
		    $wgOut->addHTML( "		<A HREF='$wgServer/wiki/index.php/Special:CedarParameters?action=detail&code=$code'><IMG SRC='$wgServer/wiki/icons/detail.png' ALT='detail' TITLE='Detail'></A>&nbsp;&nbsp;" ) ;
		    if( $allowed )
		    {
			$wgOut->addHTML( "                <A HREF='$wgServer/wiki/index.php/Special:CedarParameters?action=edit&code=$code'><IMG SRC='$wgServer/wiki/icons/edit.png' ALT='edit' TITLE='Edit'></A>&nbsp;&nbsp;<A HREF='$wgServer/wiki/index.php/Special:CedarParameters?action=delete&code=$code'><IMG SRC='$wgServer/wiki/icons/delete.png' ALT='delete' TITLE='Delete'></A>&nbsp;&nbsp;</SPAN>\n" ) ;
		    }
		    $wgOut->addHTML( "		<SPAN STYLE=\"font-size:9pt;\">$code</SPAN>" ) ;
		    $wgOut->addHTML( "	    </TD>" ) ;
		    $wgOut->addHTML( "	    <TD WIDTH=\"40%\" ALIGN=\"LEFT\">" ) ;
		    $wgOut->addHTML( "		<SPAN STYLE=\"font-size:9pt;\">&nbsp;&nbsp;&nbsp;&nbsp;$long_name</SPAN>" ) ;
		    $wgOut->addHTML( "	    </TD>" ) ;
		    $wgOut->addHTML( "	    <TD WIDTH=\"12%\" ALIGN=\"LEFT\">" ) ;
		    $wgOut->addHTML( "		<SPAN STYLE=\"font-size:9pt;\">&nbsp;&nbsp;&nbsp;&nbsp;$short_name</SPAN>" ) ;
		    $wgOut->addHTML( "	    </TD>" ) ;
		    $wgOut->addHTML( "	    <TD WIDTH=\"12%\" ALIGN=\"CENTER\">" ) ;
		    $wgOut->addHTML( "		<SPAN STYLE=\"font-size:9pt;\">$madrigal_name</SPAN>" ) ;
		    $wgOut->addHTML( "	    </TD>" ) ;
		    $wgOut->addHTML( "	    <TD WIDTH=\"12%\" ALIGN=\"CENTER\">" ) ;
		    $wgOut->addHTML( "		<SPAN STYLE=\"font-size:9pt;\">$units</SPAN>" ) ;
		    $wgOut->addHTML( "	    </TD>" ) ;
		    $wgOut->addHTML( "	    <TD WIDTH=\"12%\" ALIGN=\"CENTER\">" ) ;
		    $wgOut->addHTML( "		<SPAN STYLE=\"font-size:9pt;\">$scale</SPAN>" ) ;
		    $wgOut->addHTML( "	    </TD>" ) ;
		    $wgOut->addHTML( "	</TR>" ) ;
		}
		$wgOut->addHTML( "</TABLE>" ) ;
		$wgOut->addHTML( "<BR />" ) ;
	    }
	    $dbh->close() ;
	}
    }

    function parameterDetail( $code )
    {
	global $wgUser, $wgRequest, $wgOut, $wgDBserver, $wgServer ;
	
	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;

	$wgOut->addHTML( "<SPAN STYLE=\"font-size:12pt;\">Return to the <A HREF=\"$wgServer/wiki/index.php/Special:CedarParameters\">parameter list</A></SPAN><BR /><BR />\n" ) ;

	// Get the catalog database
	$dbh = new DatabaseMysql( $wgDBserver, $this->dbuser, $this->dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database\n" ) ;
	    return ;
	}

	$code = $dbh->strencode( $code ) ;
	$res = $dbh->query( "select LONG_NAME, SHORT_NAME, MADRIGAL_NAME, UNITS, SCALE, NOTE_ID from tbl_parameter_code WHERE PARAMETER_ID = $code" ) ;
	if( !$res )
	{
	    $db_error = $dbh->lastError() ;
	    $dbh->close() ;
	    $wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	$obj = $dbh->fetchObject( $res ) ;
	if( !$obj )
	{
	    $db_error = $dbh->lastError() ;
	    $dbh->close() ;
	    $wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( $db_error ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	$long_name = $obj->LONG_NAME ;
	$short_name = $obj->SHORT_NAME ;
	$madrigal_name = $obj->MADRIGAL_NAME ;
	$units = $obj->UNITS ;
	$scale = $obj->SCALE ;
	$note_id = intval( $obj->NOTE_ID ) ;

	$wgOut->addHTML( "    <TABLE ALIGN=\"LEFT\" BORDER=\"1\" WIDTH=\"800\" CELLPADDING=\"0\" CELLSPACING=\"0\">\n" ) ;
	$wgOut->addHTML( "        <TR>\n" ) ;
	$wgOut->addHTML( "            <TD ALIGN='CENTER' HEIGHT='30px' BGCOLOR='Aqua'>\n" ) ;
	if( $allowed )
	{
	    $wgOut->addHTML( "                <A HREF='$wgServer/wiki/index.php/Special:CedarParameters?action=edit&code=$code'><IMG SRC='$wgServer/wiki/icons/edit.png' ALT='edit' TITLE='Edit'></A>&nbsp;&nbsp;<A HREF='$wgServer/wiki/index.php/Special:CedarParameters?action=delete&code=$code'><IMG SRC='$wgServer/wiki/icons/delete.png' ALT='delete' TITLE='Delete'></A>&nbsp;&nbsp;\n" ) ;
	}
	$wgOut->addHTML( "                <SPAN STYLE='font-weight:bold;font-size:14pt;'>$code - $madrigal_name - $short_name</SPAN>\n" ) ;
	$wgOut->addHTML( "            </TD>\n" ) ;
	$wgOut->addHTML( "        </TR>\n" ) ;
	$wgOut->addHTML( "        <TR>\n" ) ;
	$wgOut->addHTML( "            <TD BGCOLOR='White'>\n" ) ;
	$wgOut->addHTML( "                <DIV STYLE='line-height:2.0;font-weight:normal;font-size:10pt;'>\n" ) ;
	$wgOut->addHTML( "                    Long Name: $long_name<BR>\n" ) ;
	$wgOut->addHTML( "                    Units: $units<BR>\n" ) ;
	$wgOut->addHTML( "                    Scale: $scale<BR>\n" ) ;
	$wgOut->addHTML( "                </DIV>\n" ) ;
	$wgOut->addHTML( "            </TD>\n" ) ;
	$wgOut->addHTML( "        </TR>\n" ) ;
	$wgOut->addHTML( "        <TR>\n" ) ;
	$wgOut->addHTML( "            <TD BGCOLOR='White'>\n" ) ;
	$wgOut->addHTML( "                <DIV STYLE='font-weight:normal;font-size:10pt;'>\n" ) ;
	$wgOut->addHTML( "                    Notes:<BR />\n" ) ;
	$last_note_id = CedarNote::displayNote( $note_id, "CedarParameters", "code", $code, 0, $dbh ) ;
	CedarNote::newNoteForm( $last_note_id, "CedarParameters", "code", $code ) ;
	$wgOut->addHTML( "                </DIV>\n" ) ;
	$wgOut->addHTML( "            </TD>\n" ) ;
	$wgOut->addHTML( "        </TR>\n" ) ;
	$wgOut->addHTML( "    </TABLE>\n" ) ;
    }

    function parameterEdit( $code, $isnew, $action )
    {
	global $wgUser, $wgRequest, $wgOut, $wgDBserver, $wgServer ;
	
	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;
	if( !$allowed )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">You do not have permission to edit parameter information</SPAN><BR />\n" ) ;
	    return ;
	}

	$dbh = new DatabaseMysql( $wgDBserver, $this->dbuser, $this->dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	$code = $dbh->strencode( $code ) ;
	$long_name = "" ;
	$short_name = "" ;
	$madrigal_name = "" ;
	$units = "" ;
	$scale = "" ;

	if( $isnew == 0 && $action == "edit" )
	{
	    $res = $dbh->query( "select LONG_NAME, SHORT_NAME, MADRIGAL_NAME, UNITS, SCALE from tbl_parameter_code WHERE PARAMETER_ID = $code" ) ;
	    if( !$res )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }

	    if( $res->numRows() != 1 )
	    {
		$dbh->close() ;
		$wgOut->addHTML( "Unable to edit the parameter $code, does not exist<BR />\n" ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }

	    $obj = $dbh->fetchObject( $res ) ;
	    if( !$obj )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }

	    $long_name = $obj->LONG_NAME ;
	    $short_name = $obj->SHORT_NAME ;
	    $madrigal_name = $obj->MADRIGAL_NAME ;
	    $units = $obj->UNITS ;
	    $scale = $obj->SCALE ;
	}
	else if( $action == "update" )
	{
	    $long_name = $dbh->strencode( $wgRequest->getText( 'long_name' ) ) ;
	    $short_name = $dbh->strencode( $wgRequest->getText( 'short_name' ) ) ;
	    $madrigal_name = $dbh->strencode( $wgRequest->getText( 'madrigal_name' ) ) ;
	    $units = $dbh->strencode( $wgRequest->getText( 'units' ) ) ;
	    $scale = $dbh->strencode( $wgRequest->getText( 'scale' ) ) ;
	}

	$wgOut->addHTML( "<FORM name=\"parameter_edit\" action=\"$wgServer/wiki/index.php/Special:CedarParameters\" method=\"POST\">\n" ) ;
	$wgOut->addHTML( "  <INPUT type=\"hidden\" name=\"action\" value=\"update\">\n" ) ;
	$wgOut->addHTML( "  <INPUT type=\"hidden\" name=\"code\" value=\"$code\">\n" ) ;
	$wgOut->addHTML( "  <INPUT type=\"hidden\" name=\"isnew\" value=\"$isnew\">\n" ) ;
	$wgOut->addHTML( "  <TABLE WIDTH=\"800\" CELLPADDING=\"2\" CELLSPACING=\"0\" BORDER=\"0\">\n" ) ;

	// parameter id text box
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Parameter ID:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" name=\"new_code\" size=\"10\" value=\"$code\"><BR />\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// long_name text box
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Long Name:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" name=\"long_name\" size=\"50\" value=\"$long_name\"><BR />\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// short_name text box
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Short Name:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" name=\"short_name\" size=\"30\" value=\"$short_name\"><BR />\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// madrigal_name text box
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Madrigal Name:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" name=\"madrigal_name\" size=\"30\" value=\"$madrigal_name\"><BR />\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// units text box
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Units:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" name=\"units\" size=\"30\" value=\"$units\"><BR />\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// scale text box
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        Scale:&nbsp;&nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT type=\"text\" name=\"scale\" size=\"30\" value=\"$scale\"><BR />\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	// submit, cancel and reset buttons
	$wgOut->addHTML( "    <TR>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"200\" ALIGN=\"right\">\n" ) ;
	$wgOut->addHTML( "        &nbsp;\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "      <TD WIDTH=\"600\" ALIGN=\"left\">\n" ) ;
	$wgOut->addHTML( "        <INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"Submit\">\n" ) ;
	$wgOut->addHTML( "        &nbsp;&nbsp;<INPUT TYPE=\"SUBMIT\" NAME=\"submit\" VALUE=\"Cancel\">\n" ) ;
	$wgOut->addHTML( "        &nbsp;&nbsp;<INPUT TYPE=\"RESET\" VALUE=\"Reset\">\n" ) ;
	$wgOut->addHTML( "      </TD>\n" ) ;
	$wgOut->addHTML( "    </TR>\n" ) ;

	$wgOut->addHTML( "  </TABLE>\n" ) ;

	$wgOut->addHTML( "</FORM>\n" ) ;

	$dbh->close() ;
    }

    function parameterUpdate( $code )
    {
	global $wgUser, $wgRequest, $wgOut, $wgDBserver, $wgServer ;
	
	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;
	if( !$allowed )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">You do not have permission to edit parameter information</SPAN><BR />\n" ) ;
	    return ;
	}

	// if the cancel button was pressed then go to parameter detail
	$submit = $wgRequest->getText( 'submit' ) ;
	if( $submit == "Cancel" )
	{
	    $this->parameterDetail( $code ) ;
	    return ;
	}

	$dbh = new DatabaseMysql( $wgDBserver, $this->dbuser, $this->dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	$code = $dbh->strencode( $code ) ;
	$new_code = $dbh->strencode( $wgRequest->getInt( 'new_code' ) ) ;
	$isnew = $dbh->strencode( $wgRequest->getInt( 'isnew' ) ) ;
	$long_name = $dbh->strencode( $wgRequest->getText( 'long_name' ) ) ;
	$short_name = $dbh->strencode( $wgRequest->getText( 'short_name' ) ) ;
	$madrigal_name = $dbh->strencode( $wgRequest->getText( 'madrigal_name' ) ) ;
	$units = $dbh->strencode( $wgRequest->getText( 'units' ) ) ;
	$scale = $dbh->strencode( $wgRequest->getText( 'scale' ) ) ;

	// if editing a parameter, check to make sure the parameter exists
	if( $isnew == 0 )
	{
	    $res = $dbh->query( "select PARAMETER_ID from tbl_parameter_code where PARAMETER_ID = $code" ) ;
	    if( !$res )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }

	    if( $res->numRows() != 1 )
	    {
		$dbh->close() ;
		$wgOut->addHTML( "<SPAN STYLE=\"color:red\">The specified parameter $code does not exist</SPAN><BR />\n" ) ;
		$this->parameterEdit( $code, $isnew, "update" ) ;
		return ;
	    }
	}

	if( $new_code == 0 )
	{
	    $dbh->close() ;
	    $wgOut->addHTML( "<SPAN STYLE=\"color:red\">The specified parameter id $new_code can not be 0</SPAN><BR />\n" ) ;
	    $this->parameterEdit( $code, $isnew, "update" ) ;
	    return ;
	}

	// if the code and new_code are not the same, make sure new_code
	// doesn't already exist
	if( $code != $new_code )
	{
	    $res = $dbh->query( "select PARAMETER_ID from tbl_parameter_code where PARAMETER_ID = $new_code" ) ;
	    if( !$res )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }

	    if( $res->numRows() != 0 )
	    {
		$dbh->close() ;
		$wgOut->addHTML( "<SPAN STYLE=\"color:red\">The new parameter id $new_code already exists</SPAN><BR />\n" ) ;
		$this->parameterEdit( $code, $isnew, "update" ) ;
		return ;
	    }
	}

	// if isnew then insert the new parameter
	// if not new, code != 0, then update code (remember to use new_code)
	if( $isnew == 1 )
	{
	    $insert_success = $dbh->insert( 'tbl_parameter_code',
		    array(
			    'PARAMETER_ID' => $new_code,
			    'LONG_NAME' => $long_name,
			    'SHORT_NAME' => $short_name,
			    'MADRIGAL_NAME' => $madrigal_name,
			    'UNITS' => $units,
			    'SCALE' => $scale
		    ),
		    __METHOD__
	    ) ;

	    if( $insert_success == false )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Failed to insert new parameter $new_code<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }
	}
	else if( $isnew == 0 )
	{
	    $update_success = $dbh->update( 'tbl_parameter_code',
		    array(
			    'PARAMETER_ID' => $new_code,
			    'LONG_NAME' => $long_name,
			    'SHORT_NAME' => $short_name,
			    'MADRIGAL_NAME' => $madrigal_name,
			    'UNITS' => $units,
			    'SCALE' => $scale
		    ),
		    array(
			    'PARAMETER_ID' => $code
		    ),
		    __METHOD__
	    ) ;

	    if( $update_success == false )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Failed to update parameter $code<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }
	}

	$dbh->close() ;

	$this->parameterDetail( $new_code ) ;
    }

    function parameterDelete( $code )
    {
	global $wgUser, $wgRequest, $wgOut, $wgDBserver, $wgServer ;
	
	$allowed = $wgUser->isAllowed( 'cedar_admin' ) ;
	if( !$allowed )
	{
	    $wgOut->addHTML( "<SPAN STYLE=\"font-weight:bold;font-size:14pt;\">You do not have permission to delete parameter information</SPAN><BR />\n" ) ;
	    return ;
	}

	// if confirm_delete is not set or is false then go to the
	// instrument detail for this instrument
	$confirm = $wgRequest->getText( 'confirm' ) ;

	if( !$confirm )
	{
	    $wgOut->addHTML( "Are you sure you want to delete the parameter with id $code?\n" ) ;
	    $wgOut->addHTML( "(<A HREF=\"$wgServer/wiki/index.php/Special:CedarParameters?action=delete&confirm=yes&code=$code\">Yes</A>" ) ;
	    $wgOut->addHTML( " | <A HREF=\"$wgServer/wiki/index.php/Special:CedarParameters?action=delete&confirm=no&code=$code\">No</A>)" ) ;
	    return ;
	}

	if( $confirm && $confirm == "no" )
	{
	    $this->parameterDetail( $code ) ;
	    return ;
	}

	$dbh = new DatabaseMysql( $wgDBserver, $this->dbuser, $this->dbpwd, "CEDARCATALOG" ) ;
	if( !$dbh )
	{
	    $wgOut->addHTML( "Unable to connect to the CEDAR Catalog database<BR />\n" ) ;
	    $wgOut->addHTML( "<BR />\n" ) ;
	    return ;
	}

	$code = $dbh->strencode( $code ) ;

	// if confirm_delete is true then delete the instrument
	if( $confirm && $confirm == "yes" )
	{
	    // need to delete all of the associated notes as well
	    $res = $dbh->query( "select NOTE_ID from tbl_parameter_code WHERE PARAMETER_ID = $code" ) ;
	    if( !$res )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Unable to query the CEDAR Catalog database<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }

	    $obj = $dbh->fetchObject( $res ) ;
	    if( $obj )
	    {
		$note_id = intval( $obj->NOTE_ID ) ;
		if( $note_id != 0 )
		{
		    CedarNote::deleteNotes( $note_id, $dbh ) ;
		}
	    }

	    // delete the parameter
	    $delete_success = $dbh->delete( 'tbl_parameter_code', array( 'PARAMETER_ID' => $code ) ) ;

	    if( $delete_success == false )
	    {
		$db_error = $dbh->lastError() ;
		$dbh->close() ;
		$wgOut->addHTML( "Failed to delete parameter $code:<BR />\n" ) ;
		$wgOut->addHTML( $db_error ) ;
		$wgOut->addHTML( "<BR />\n" ) ;
		return ;
	    }
	}

	$dbh->close() ;

	$this->parameterList() ;
    }
}

?>
