<!-- DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" -->
<!-- <html xmlns="http://www.w3.org/1999/xhtml"> -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="Content-Style-Type" content="text/css" />
 <link rel="top" href="index.php" title="" />
<link rel="search" href="search.php" title="" />
<link rel="help" href="faq.php" title="" />
<link rel="author" href="memberlist.php" title="" />

<title>Solar Empire :: Post a reply</title>
<link rel="stylesheet" href="templates/se/se.css" type="text/css" />
<script language="Javascript" type="text/javascript">
<!--
	var new_pm_flag = 0;

	if( new_pm_flag )
	{
		window.open('privmsg.php?mode=newpm', '_phpbbprivmsg', 'HEIGHT=225,resizable=yes,WIDTH=400');;
	}
//-->
</script>
<style type="text/css">
<!--
@import url("templates/phpVB2/formIE.css");
TD.catrow
{
font-family: Verdana;
font-size : 12px;
font-weight: bold;
height: 25px;
}

TD.toprow
{
font-family: Verdana;
font-size : 9px;
font-weight: bold;
height: 25px;
}
-->
</style></HEAD>
<BODY BGCOLOR=#000000 marginwidth="5" marginheight="5" topmargin="5" leftmargin="5">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td bgcolor="#000000"><img src="templates/se/images/header.jpg"></td>
    <td bgcolor="#000000" width="90%" align="center" valign="middle"><img src="templates/se/images/menu.jpg" usemap="#Map" border="0"></td>
  </tr>
</table>
<map name="Map">
<area shape="rect" coords="23,14,55,32" href="faq.php">
<area shape="rect" coords="64,15,119,30" href="search.php">
<area shape="rect" coords="127,12,224,30" href="memberlist.php">
<area shape="rect" coords="233,14,325,30" href="groupcp.php">
<area shape="rect" coords="28,35,100,50" href="profile.php?mode=register">
<area shape="rect" coords="109,35,167,50" href="profile.php?mode=editprofile">
<area shape="rect" coords="176,33,323,50" href="privmsg.php?folder=inbox">
<area shape="rect" coords="112,54,236,69" href="login.php?logout=true">
</map>
<br /><br />
<script language="JavaScript" type="text/javascript">
<!--
// bbCode control by
// subBlue design
// www.subBlue.com

// Startup variables
var imageTag = false;
var theSelection = false;

// Check for Browser & Platform for PC & IE specific bits
// More details from: http://www.mozilla.org/docs/web-developer/sniffer/browser_type.html
var clientPC = navigator.userAgent.toLowerCase(); // Get client info
var clientVer = parseInt(navigator.appVersion); // Get browser version

var is_ie = ((clientPC.indexOf("msie") != -1) && (clientPC.indexOf("opera") == -1));
var is_nav  = ((clientPC.indexOf('mozilla')!=-1) && (clientPC.indexOf('spoofer')==-1)
                && (clientPC.indexOf('compatible') == -1) && (clientPC.indexOf('opera')==-1)
                && (clientPC.indexOf('webtv')==-1) && (clientPC.indexOf('hotjava')==-1));

var is_win   = ((clientPC.indexOf("win")!=-1) || (clientPC.indexOf("16bit") != -1));
var is_mac    = (clientPC.indexOf("mac")!=-1);


// Helpline messages
b_help = "Bold text: [b]text[/b]  (alt+b)";
i_help = "Italic text: [i]text[/i]  (alt+i)";
u_help = "Underline text: [u]text[/u]  (alt+u)";
q_help = "Quote text: [quote]text[/quote]  (alt+q)";
c_help = "Code display: [code]code[/code]  (alt+c)";
l_help = "List: [list]text[/list] (alt+l)";
o_help = "Ordered list: [list=]text[/list]  (alt+o)";
p_help = "Insert image: [img]http://image_url[/img]  (alt+p)";
w_help = "Insert URL: [url]http://url[/url] or [url=http://url]URL text[/url]  (alt+w)";
a_help = "Close all open bbCode tags";
s_help = "Font color: [color=red]text[/color]  Tip: you can also use color=#FF0000";
f_help = "Font size: [size=x-small]small text[/size]";

// Define the bbCode tags
bbcode = new Array();
bbtags = new Array('[b]','[/b]','[i]','[/i]','[u]','[/u]','[quote]','[/quote]','[code]','[/code]','[list]','[/list]','[list=]','[/list]','[img]','[/img]','[url]','[/url]');
imageTag = false;

// Shows the help messages in the helpline window
function helpline(help) {
	document.post.helpbox.value = eval(help + "_help");
}


// Replacement for arrayname.length property
function getarraysize(thearray) {
	for (i = 0; i < thearray.length; i++) {
		if ((thearray[i] == "undefined") || (thearray[i] == "") || (thearray[i] == null))
			return i;
		}
	return thearray.length;
}

// Replacement for arrayname.push(value) not implemented in IE until version 5.5
// Appends element to the array
function arraypush(thearray,value) {
	thearray[ getarraysize(thearray) ] = value;
}

// Replacement for arrayname.pop() not implemented in IE until version 5.5
// Removes and returns the last element of an array
function arraypop(thearray) {
	thearraysize = getarraysize(thearray);
	retval = thearray[thearraysize - 1];
	delete thearray[thearraysize - 1];
	return retval;
}


function checkForm() {

	formErrors = false;

	if (document.post.message.value.length < 2) {
		formErrors = "You must enter a message when posting";
	}

	if (formErrors) {
		alert(formErrors);
		return false;
	} else {
		bbstyle(-1);
		//formObj.preview.disabled = true;
		//formObj.submit.disabled = true;
		return true;
	}
}

function emoticon(text) {
	text = ' ' + text + ' ';
	if (document.post.message.createTextRange && document.post.message.caretPos) {
		var caretPos = document.post.message.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? text + ' ' : text;
		document.post.message.focus();
	} else {
	document.post.message.value  += text;
	document.post.message.focus();
	}
}

function bbfontstyle(bbopen, bbclose) {
	if ((clientVer >= 4) && is_ie && is_win) {
		theSelection = document.selection.createRange().text;
		if (!theSelection) {
			document.post.message.value += bbopen + bbclose;
			document.post.message.focus();
			return;
		}
		document.selection.createRange().text = bbopen + theSelection + bbclose;
		document.post.message.focus();
		return;
	} else {
		document.post.message.value += bbopen + bbclose;
		document.post.message.focus();
		return;
	}
	storeCaret(document.post.message);
}


function bbstyle(bbnumber) {

	donotinsert = false;
	theSelection = false;
	bblast = 0;

	if (bbnumber == -1) { // Close all open tags & default button names
		while (bbcode[0]) {
			butnumber = arraypop(bbcode) - 1;
			document.post.message.value += bbtags[butnumber + 1];
			buttext = eval('document.post.addbbcode' + butnumber + '.value');
			eval('document.post.addbbcode' + butnumber + '.value ="' + buttext.substr(0,(buttext.length - 1)) + '"');
		}
		imageTag = false; // All tags are closed including image tags :D
		document.post.message.focus();
		return;
	}

	if ((clientVer >= 4) && is_ie && is_win)
		theSelection = document.selection.createRange().text; // Get text selection

	if (theSelection) {
		// Add tags around selection
		document.selection.createRange().text = bbtags[bbnumber] + theSelection + bbtags[bbnumber+1];
		document.post.message.focus();
		theSelection = '';
		return;
	}

	// Find last occurance of an open tag the same as the one just clicked
	for (i = 0; i < bbcode.length; i++) {
		if (bbcode[i] == bbnumber+1) {
			bblast = i;
			donotinsert = true;
		}
	}

	if (donotinsert) {		// Close all open tags up to the one just clicked & default button names
		while (bbcode[bblast]) {
				butnumber = arraypop(bbcode) - 1;
				document.post.message.value += bbtags[butnumber + 1];
				buttext = eval('document.post.addbbcode' + butnumber + '.value');
				eval('document.post.addbbcode' + butnumber + '.value ="' + buttext.substr(0,(buttext.length - 1)) + '"');
				imageTag = false;
			}
			document.post.message.focus();
			return;
	} else { // Open tags

		if (imageTag && (bbnumber != 14)) {		// Close image tag before adding another
			document.post.message.value += bbtags[15];
			lastValue = arraypop(bbcode) - 1;	// Remove the close image tag from the list
			document.post.addbbcode14.value = "Img";	// Return button back to normal state
			imageTag = false;
		}

		// Open tag
		document.post.message.value += bbtags[bbnumber];
		if ((bbnumber == 14) && (imageTag == false)) imageTag = 1; // Check to stop additional tags after an unclosed image tag
		arraypush(bbcode,bbnumber+1);
		eval('document.post.addbbcode'+bbnumber+'.value += "*"');
		document.post.message.focus();
		return;
	}
	storeCaret(document.post.message);
}

// Insert at Claret position. Code from
// http://www.faqts.com/knowledge_base/view.phtml/aid/1052/fid/130
function storeCaret(textEl) {
	if (textEl.createTextRange) textEl.caretPos = document.selection.createRange().duplicate();
}

//-->
</script>
<table cellpadding="2" cellspacing="0" border="0" width="98%"  align="center">
  <tr>
    <td valign="top"><img src="templates/millenniumFalcon/images/folder_new.gif" border="0" align="absmiddle">
      <span class="largetext"><a href="index.php" class="largelink"><b>Solar Empire
      Forum Index</b></a></span></td>
  </tr>
</table>
<form action="posting.php" method="post" name="post" onsubmit="return checkForm(this)">

  <table width="98%" cellspacing="2" cellpadding="2" border="0" align="center">
    <tr>
      <td align="left"><span  class="nav"><a href="index.php" class="nav">Solar Empire Forum Index</a>
        -> <a href="viewforum.php?f=2" class="nav">Development</a></span></td>
    </tr>
  </table>
  <table width="98%" cellpadding="0" cellspacing="0" align="center">
    <tr>
      <td class="tableborder">
        <table border="0" cellpadding="3" cellspacing="1" width="100%">
          <tr>
            <td class="toprow" colspan="2"><b>Post a reply</b></td>
          </tr>
          <!-- This is for private messaging -->
          <tr>
            <td class="row1" width="22%"><span class="gen"><b>Subject</b></span></td>
            <td class="row2" width="78%"> <span class="gen">
              <input type="text" name="subject" size="45" maxlength="60" style="width:450px" tabindex="2" class="post" value="" />
              </span> </td>
          </tr>
          <tr>
            <td class="row1" valign="top">
              <table width="100%" border="0" cellspacing="0" cellpadding="1">
                <tr>
                  <td><span class="gen"><b>Message body</b></span> </td>
                </tr>
                <tr>
                  <td valign="middle" align="center"> <br />
                    <table width="100" border="0" cellspacing="0" cellpadding="5">
                      <tr align="center">
                        <td colspan="4" class="gensmall"><b>Emoticons</b></td>
                      </tr>
                      <tr align="center" valign="middle">
                        <td><a href="javascript:emoticon(':D')"><img src="images/smiles/icon_biggrin.gif" border="0" alt="Very Happy" title="Very Happy" /></a></td>
                        <td><a href="javascript:emoticon(':)')"><img src="images/smiles/icon_smile.gif" border="0" alt="Smile" title="Smile" /></a></td>
                        <td><a href="javascript:emoticon(':(')"><img src="images/smiles/icon_sad.gif" border="0" alt="Sad" title="Sad" /></a></td>
                        <td><a href="javascript:emoticon(':o')"><img src="images/smiles/icon_surprised.gif" border="0" alt="Surprised" title="Surprised" /></a></td>
                      </tr>
                      <tr align="center" valign="middle">
                        <td><a href="javascript:emoticon('8O')"><img src="images/smiles/icon_eek.gif" border="0" alt="Shocked" title="Shocked" /></a></td>
                        <td><a href="javascript:emoticon(':?')"><img src="images/smiles/icon_confused.gif" border="0" alt="Confused" title="Confused" /></a></td>
                        <td><a href="javascript:emoticon('8)')"><img src="images/smiles/icon_cool.gif" border="0" alt="Cool" title="Cool" /></a></td>
                        <td><a href="javascript:emoticon(':lol:')"><img src="images/smiles/icon_lol.gif" border="0" alt="Laughing" title="Laughing" /></a></td>
                      </tr>
                      <tr align="center" valign="middle">
                        <td><a href="javascript:emoticon(':x')"><img src="images/smiles/icon_mad.gif" border="0" alt="Mad" title="Mad" /></a></td>
                        <td><a href="javascript:emoticon(':P')"><img src="images/smiles/icon_razz.gif" border="0" alt="Razz" title="Razz" /></a></td>
                        <td><a href="javascript:emoticon(':oops:')"><img src="images/smiles/icon_redface.gif" border="0" alt="Embarassed" title="Embarassed" /></a></td>
                        <td><a href="javascript:emoticon(':cry:')"><img src="images/smiles/icon_cry.gif" border="0" alt="Crying or Very sad" title="Crying or Very sad" /></a></td>
                      </tr>
                      <tr align="center" valign="middle">
                        <td><a href="javascript:emoticon(':evil:')"><img src="images/smiles/icon_evil.gif" border="0" alt="Evil or Very Mad" title="Evil or Very Mad" /></a></td>
                        <td><a href="javascript:emoticon(':twisted:')"><img src="images/smiles/icon_twisted.gif" border="0" alt="Twisted Evil" title="Twisted Evil" /></a></td>
                        <td><a href="javascript:emoticon(':roll:')"><img src="images/smiles/icon_rolleyes.gif" border="0" alt="Rolling Eyes" title="Rolling Eyes" /></a></td>
                        <td><a href="javascript:emoticon(':wink:')"><img src="images/smiles/icon_wink.gif" border="0" alt="Wink" title="Wink" /></a></td>
                      </tr>
                      <tr align="center" valign="middle">
                        <td><a href="javascript:emoticon(':!:')"><img src="images/smiles/icon_exclaim.gif" border="0" alt="Exclamation" title="Exclamation" /></a></td>
                        <td><a href="javascript:emoticon(':?:')"><img src="images/smiles/icon_question.gif" border="0" alt="Question" title="Question" /></a></td>
                        <td><a href="javascript:emoticon(':idea:')"><img src="images/smiles/icon_idea.gif" border="0" alt="Idea" title="Idea" /></a></td>
                        <td><a href="javascript:emoticon(':arrow:')"><img src="images/smiles/icon_arrow.gif" border="0" alt="Arrow" title="Arrow" /></a></td>
                      </tr>
                      <tr align="center">
                        <td colspan="4"><span  class="nav"><a href="posting.php?mode=smilies" onclick="window.open('posting.php?mode=smilies', '_phpbbsmilies', 'HEIGHT=300,resizable=yes,scrollbars=yes,WIDTH=250');return false;" target="_phpbbsmilies" class="nav">View more Emoticons</a></span></td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
            <td class="row2" valign="top"><span class="gen"> <span class="genmed">
              </span>
              <table width="450" border="0" cellspacing="0" cellpadding="2">
                <tr align="center" valign="middle">
                  <td><span class="genmed">
                    <input type="button" class="button" accesskey="b" name="addbbcode0" value=" B " style="font-weight:bold; width: 30px" onClick="bbstyle(0)" onMouseOver="helpline('b')" />
                    </span></td>
                  <td><span class="genmed">
                    <input type="button" class="button" accesskey="i" name="addbbcode2" value=" i " style="font-style:italic; width: 30px" onClick="bbstyle(2)" onMouseOver="helpline('i')" />
                    </span></td>
                  <td><span class="genmed">
                    <input type="button" class="button" accesskey="u" name="addbbcode4" value=" u " style="text-decoration: underline; width: 30px" onClick="bbstyle(4)" onMouseOver="helpline('u')" />
                    </span></td>
                  <td><span class="genmed">
                    <input type="button" class="button" accesskey="q" name="addbbcode6" value="Quote" style="width: 50px" onClick="bbstyle(6)" onMouseOver="helpline('q')" />
                    </span></td>
                  <td><span class="genmed">
                    <input type="button" class="button" accesskey="c" name="addbbcode8" value="Code" style="width: 40px" onClick="bbstyle(8)" onMouseOver="helpline('c')" />
                    </span></td>
                  <td><span class="genmed">
                    <input type="button" class="button" accesskey="l" name="addbbcode10" value="List" style="width: 40px" onClick="bbstyle(10)" onMouseOver="helpline('l')" />
                    </span></td>
                  <td><span class="genmed">
                    <input type="button" class="button" accesskey="o" name="addbbcode12" value="List=" style="width: 40px" onClick="bbstyle(12)" onMouseOver="helpline('o')" />
                    </span></td>
                  <td><span class="genmed">
                    <input type="button" class="button" accesskey="p" name="addbbcode14" value="Img" style="width: 40px"  onClick="bbstyle(14)" onMouseOver="helpline('p')" />
                    </span></td>
                  <td><span class="genmed">
                    <input type="button" class="button" accesskey="w" name="addbbcode16" value="URL" style="text-decoration: underline; width: 40px" onClick="bbstyle(16)" onMouseOver="helpline('w')" />
                    </span></td>
                </tr>
                <tr>
                  <td colspan="9">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                      <tr>
                        <td><span class="genmed"> &nbsp;Font colour:
                          <select name="addbbcode18" onChange="bbfontstyle('[color=' + this.form.addbbcode18.options[this.form.addbbcode18.selectedIndex].value + ']', '[/color]')" onMouseOver="helpline('s')">
                            <option style="color:black; background-color: #FFFFFF " value="#" class="genmed">Default</option>
                            <option style="color:darkred; background-color: #DEE3E7" value="darkred" class="genmed">Dark Red</option>
                            <option style="color:red; background-color: #DEE3E7" value="red" class="genmed">Red</option>
                            <option style="color:orange; background-color: #DEE3E7" value="orange" class="genmed">Orange</option>
                            <option style="color:brown; background-color: #DEE3E7" value="brown" class="genmed">Brown</option>
                            <option style="color:yellow; background-color: #DEE3E7" value="yellow" class="genmed">Yellow</option>
                            <option style="color:green; background-color: #DEE3E7" value="green" class="genmed">Green</option>
                            <option style="color:olive; background-color: #DEE3E7" value="olive" class="genmed">Olive</option>
                            <option style="color:cyan; background-color: #DEE3E7" value="cyan" class="genmed">Cyan</option>
                            <option style="color:blue; background-color: #DEE3E7" value="blue" class="genmed">Blue</option>
                            <option style="color:darkblue; background-color: #DEE3E7" value="darkblue" class="genmed">Dark Blue</option>
                            <option style="color:indigo; background-color: #DEE3E7" value="indigo" class="genmed">Indigo</option>
                            <option style="color:violet; background-color: #DEE3E7" value="violet" class="genmed">Violet</option>
                            <option style="color:white; background-color: #DEE3E7" value="white" class="genmed">White</option>
                            <option style="color:black; background-color: #DEE3E7" value="black" class="genmed">Black</option>
                          </select>
                          &nbsp;Font size:
                          <select name="addbbcode20" onChange="bbfontstyle('[size=' + this.form.addbbcode20.options[this.form.addbbcode20.selectedIndex].value + ']', '[/size]')" onMouseOver="helpline('f')">
                            <option value="7" class="genmed">Tiny</option>
                            <option value="9" class="genmed">Small</option>
                            <option value="12" selected class="genmed">Normal</option>
                            <option value="18" class="genmed">Large</option>
                            <option  value="24" class="genmed">Huge</option>
                          </select>
                          </span></td>
                        <td nowrap align="right"><span class="gensmall"><a href="javascript:bbstyle(-1)" class="genmed" onMouseOver="helpline('a')">Close Tags</a></span></td>
                      </tr>
                    </table>
                  </td>
                </tr>
                <tr>
                  <td colspan="9"> <span class="gensmall">
                    <input type="text" name="helpbox" size="45" maxlength="100" style="width:450px; font-size:10px" class="helpline" value="Tip: Styles can be applied quickly to selected text" />
                    </span></td>
                </tr>
                <tr>
                  <td colspan="9"><span class="gen">
                    <textarea name="message" rows="15" cols="35" wrap="virtual" style="width:450px" tabindex="3" class="post" onselect="storeCaret(this);" onclick="storeCaret(this);" onkeyup="storeCaret(this);"></textarea>
                    </span></td>
                </tr>
              </table>
              </span></td>
          </tr>
          <tr>
            <td class="row1" valign="top"><span class="gen"><b>Options</b></span><br />
              <span class="gensmall">HTML is <u>ON</u><br />
              <a href="faq.php?mode=bbcode" target="_phpbbcode">BBCode</a> is <u>ON</u><br />
              Smilies are <u>ON</u></span></td>
            <td class="row2"><span class="gen"> </span>
              <table cellspacing="0" cellpadding="1" border="0">
                <tr>
                  <td>
                    <input type="checkbox" name="disable_html"  />
                  </td>
                  <td><span class="gen">Disable HTML in this post</span></td>
                </tr>
                <tr>
                  <td>
                    <input type="checkbox" name="disable_bbcode"  />
                  </td>
                  <td><span class="gen">Disable BBCode in this post</span></td>
                </tr>
                <tr>
                  <td>
                    <input type="checkbox" name="disable_smilies"  />
                  </td>
                  <td><span class="gen">Disable Smilies in this post</span></td>
                </tr>
                <tr>
                  <td>
                    <input type="checkbox" name="attach_sig" checked="checked" />
                  </td>
                  <td><span class="gen">Attach signature (signatures can be changed in profile)</span></td>
                </tr>
                <tr>
                  <td>
                    <input type="checkbox" name="notify"  />
                  </td>
                  <td><span class="gen">Notify me when a reply is posted</span></td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td class="toprow" colspan="2" align="center"> <input type="hidden" name="mode" value="reply" /><input type="hidden" name="t" value="265" />
              <input type="submit" accesskey="s" tabindex="6" name="post" class="mainoption" value="Submit" />
              <input type="submit" tabindex="5" name="preview" class="mainoption" value="Preview" />
              &nbsp; </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <table width="100%" cellspacing="2" border="0" align="center" cellpadding="2">
    <tr>
      <td align="right" valign="top"><span class="gensmall">All times are GMT</span></td>
    </tr>
  </table>
</form>
<table width="100%" cellspacing="2" border="0" align="center">
  <tr>
    <td valign="top" align="right">
<form method="post" name="jumpbox" action="viewforum.php">
  <table cellspacing="0" cellpadding="0" border="0">
	<tr>
	  <td nowrap><span class="smalltext">Jump to:&nbsp;<select name="f" onChange="if(this.options[this.selectedIndex].value != -1){ forms['jumpbox'].submit() }"><option value="-1">Select a forum</option><option value="-1">&nbsp;</option><option value="-1">Solar Empire (Public)</option><option value="-1">----------------</option><option value="1">General Discussion</option><option value="2">Development</option><option value="3">Bugs</option></select><input type="hidden" name="sid" value="" />&nbsp;
		<input type="submit" value="Go" />
		</span></td>
	</tr>
  </table>
</form>

</td>
  </tr>
</table>

 <table cellpadding="0" cellspacing="0" border="0" width="98%" align="center">
  <tr>
    <td class="tableborder">
<table border="0" cellpadding="3" cellspacing="1" width="100%" class="forumline">
	<tr>

	<td class="catrow" height="28" align="center"><b><span class="catrowtext">Topic review</span></b></td>
	</tr>
	<tr>
		<td class="row1"><iframe width="100%" height="300" src="posting.php?mode=topicreview&amp;t=265" >
 <table cellpadding="0" cellspacing="0" border="0" width="98%" align="center">
  <tr>
    <td class="tableborder">
	  <table border="0" cellpadding="3" cellspacing="1" width="100%" class="forumline" style="border: 0px #006699 solid ">
        <tr>
          <td class="row1" width="22%" height="26">Author</td>
          <td class="row1">Message</td>
        </tr>
        <tr>
          <td width="22%" align="left" valign="top" class="row1"><span class="name"><a name=""></a><b>Fallen</b></span></td>
          <td class="row2" height="28" valign="top">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td width="100%"><img src="templates/se/images/icon_minipost.gif" alt="Post" title="Post" border="0" /><span class="postdetails">Posted:
                  Tue Nov 12, 2002 10:54 pm<span class="gen">&nbsp;</span>&nbsp;&nbsp;&nbsp;Post subject:
                  </span></td>
              </tr>
              <tr>
                <td colspan="2">
                  <hr />
                </td>
              </tr>
              <tr>
                <td colspan="2"><span class="postbody">Ohh okay...just &lt;a href=mailto:phantom_krypton19@hotmail.com&gt;email&lt;/a&gt; me when you're ready to begin <img src="images/smiles/icon_biggrin.gif" alt="icon_biggrin.gif" border="0" /></span></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td width="22%" align="left" valign="top" class="row1"><span class="name"><a name=""></a><b>Maugrim_The_Reaper</b></span></td>
          <td class="row2" height="28" valign="top">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td width="100%"><img src="templates/se/images/icon_minipost.gif" alt="Post" title="Post" border="0" /><span class="postdetails">Posted:
                  Tue Nov 12, 2002 1:19 pm<span class="gen">&nbsp;</span>&nbsp;&nbsp;&nbsp;Post subject:
                  </span></td>
              </tr>
              <tr>
                <td colspan="2">
                  <hr />
                </td>
              </tr>
              <tr>
                <td colspan="2"><span class="postbody">Forum will most likely remain closed until December when I can commit more time to the sub-project. Until then it's polishing off what's already in the code and releasing a version for Mori's SF release. This will not however include certain additions. I'm retaining some for the new server.</span></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td width="22%" align="left" valign="top" class="row1"><span class="name"><a name=""></a><b>Maugrim_The_Reaper</b></span></td>
          <td class="row2" height="28" valign="top">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td width="100%"><img src="templates/se/images/icon_minipost.gif" alt="Post" title="Post" border="0" /><span class="postdetails">Posted:
                  Tue Nov 12, 2002 1:05 pm<span class="gen">&nbsp;</span>&nbsp;&nbsp;&nbsp;Post subject:
                  </span></td>
              </tr>
              <tr>
                <td colspan="2">
                  <hr />
                </td>
              </tr>
              <tr>
                <td colspan="2"><span class="postbody">Been out sick for days so I haven't been checking, sorry about that. My email is <a href="mailto:patrick.brady@kpmg.ie">patrick.brady@kpmg.ie</a>.</span></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td width="22%" align="left" valign="top" class="row1"><span class="name"><a name=""></a><b>Fallen</b></span></td>
          <td class="row2" height="28" valign="top">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td width="100%"><img src="templates/se/images/icon_minipost.gif" alt="Post" title="Post" border="0" /><span class="postdetails">Posted:
                  Mon Nov 11, 2002 12:28 am<span class="gen">&nbsp;</span>&nbsp;&nbsp;&nbsp;Post subject:
                  </span></td>
              </tr>
              <tr>
                <td colspan="2">
                  <hr />
                </td>
              </tr>
              <tr>
                <td colspan="2"><span class="postbody">Maug was doing a subproject, closed source, a subproject of SE.  He said to post any questions on his forum somewhere, but he never replied to any of mine.</span></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td width="22%" align="left" valign="top" class="row1"><span class="name"><a name=""></a><b>Ryanpc</b></span></td>
          <td class="row2" height="28" valign="top">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td width="100%"><img src="templates/se/images/icon_minipost.gif" alt="Post" title="Post" border="0" /><span class="postdetails">Posted:
                  Sun Nov 10, 2002 1:50 am<span class="gen">&nbsp;</span>&nbsp;&nbsp;&nbsp;Post subject:
                  </span></td>
              </tr>
              <tr>
                <td colspan="2">
                  <hr />
                </td>
              </tr>
              <tr>
                <td colspan="2"><span class="postbody">  <blockquote><span class="smalltext">quote:</span>   <hr>  <i><b>Originally posted by Fallen: </b></i><br />Nothin to do with what server I'm on dude... <img src="images/smiles/icon_biggrin.gif" alt="icon_biggrin.gif" border="0" />  <img src="images/smiles/icon_biggrin.gif" alt="icon_biggrin.gif" border="0" /><hr></blockquote>      But I can't help you....it's noty the same on everyserver...it's not like when your on one server...your've been to them all... and I'm not sure what your talking about...I never heard about it.</span></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td width="22%" align="left" valign="top" class="row1"><span class="name"><a name=""></a><b>Fallen</b></span></td>
          <td class="row2" height="28" valign="top">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td width="100%"><img src="templates/se/images/icon_minipost.gif" alt="Post" title="Post" border="0" /><span class="postdetails">Posted:
                  Sat Nov 09, 2002 8:36 pm<span class="gen">&nbsp;</span>&nbsp;&nbsp;&nbsp;Post subject:
                  </span></td>
              </tr>
              <tr>
                <td colspan="2">
                  <hr />
                </td>
              </tr>
              <tr>
                <td colspan="2"><span class="postbody">Nothin to do with what server I'm on dude... <img src="images/smiles/icon_biggrin.gif" alt="icon_biggrin.gif" border="0" />  <img src="images/smiles/icon_biggrin.gif" alt="icon_biggrin.gif" border="0" /></span></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td width="22%" align="left" valign="top" class="row1"><span class="name"><a name=""></a><b>Ryanpc</b></span></td>
          <td class="row2" height="28" valign="top">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td width="100%"><img src="templates/se/images/icon_minipost.gif" alt="Post" title="Post" border="0" /><span class="postdetails">Posted:
                  Sat Nov 09, 2002 8:33 pm<span class="gen">&nbsp;</span>&nbsp;&nbsp;&nbsp;Post subject:
                  </span></td>
              </tr>
              <tr>
                <td colspan="2">
                  <hr />
                </td>
              </tr>
              <tr>
                <td colspan="2"><span class="postbody">What Server are you on?</span></td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td width="22%" align="left" valign="top" class="row1"><span class="name"><a name=""></a><b>Fallen</b></span></td>
          <td class="row2" height="28" valign="top">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td width="100%"><img src="templates/se/images/icon_minipost.gif" alt="Post" title="Post" border="0" /><span class="postdetails">Posted:
                  Sat Nov 09, 2002 8:19 pm<span class="gen">&nbsp;</span>&nbsp;&nbsp;&nbsp;Post subject:
                  BM-SE SUB-PROJECT</span></td>
              </tr>
              <tr>
                <td colspan="2">
                  <hr />
                </td>
              </tr>
              <tr>
                <td colspan="2"><span class="postbody">Uh..yeah....when exactly is the Sub Project going to begin?  You tell us on your forum to post any questions and then you just don't reply to them!  Bahg! <img src="images/smiles/icon_rolleyes.gif" alt="icon_rolleyes.gif" border="0" />  <img src="images/smiles/icon_biggrin.gif" alt="icon_biggrin.gif" border="0" /></span></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
</td></tr></table>
		</iframe></td>
	</tr>
</table>
</td>
</tr>
</table>




<div align="center"><b><a href="admin/index.php">Go to Administration Panel</a><br /><br /></b> <font size="-3"> Powered by phpBB: 2.1.0 [20020430]
  &copy; 2001 <a href="http://www.phpbb.com/" target="_phpbb" class="smalllink"><b>phpBB
  Group</b></a><br />
</BODY>
</HTML>
<br /><div align=\"center\"><font size="-2">phpBB Created this page in 1.327489 seconds : 16 queries executed : GZIP compression disabled : Debug Mode</font></div>