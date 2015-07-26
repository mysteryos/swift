<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Scott - Form shared</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    </head>
    <body style="margin: 0; padding: 0;">
        <table width="100%" cellspacing="0" cellpadding="0" style="background:#efefef;" align="center" height="auto">
           <tbody>
              <tr>
                 <td>
                    <table id="header" border="0" cellspacing="0" cellpadding="0" width="700" bgcolor="#efefef" align="center" height="68">
                       <tbody>
                          <tr height="20">
                             <td width="150"></td>
                             <td width="548">&nbsp;</td>
                          </tr>
                          <tr>
                             <td height="47" valign="bottom" width="236">
                                 <a href="http://swift.scottltd.net" target="_blank">
                                     <img style="VERTICAL-ALIGN:bottom;" border="0" alt="Scott & co Ltd" src="http://swift.scottltd.net/img/logo.png" width="125" height="51">
                                 </a>
                             </td>
                             <td style="COLOR:#ccc;" class="ecxglobal-nav" height="51" valign="bottom" width="548" align="right">
                                 <a style="PADDING-BOTTOM:0px;PADDING-LEFT:4px;PADDING-RIGHT:4px;FONT-FAMILY:arial;COLOR:#666;FONT-SIZE:11px;TEXT-DECORATION:none;PADDING-TOP:0px;" href="http://swift.scottltd.net/order-tracking/" target="_blank">Order Process</a> |
                                 <a style="PADDING-BOTTOM:0px;PADDING-LEFT:4px;PADDING-RIGHT:4px;FONT-FAMILY:arial;COLOR:#666;FONT-SIZE:11px;TEXT-DECORATION:none;PADDING-TOP:0px;" href="http://swift.scottltd.net/aprequest/" target="_blank">A&P Request</a> |
                                 <a style="PADDING-BOTTOM:0px;PADDING-LEFT:4px;PADDING-RIGHT:4px;FONT-FAMILY:arial;COLOR:#666;FONT-SIZE:11px;TEXT-DECORATION:none;PADDING-TOP:0px;" href="http://swift.scottltd.net/acpayable/" target="_blank">Accounts Payable</a>
                             </td>
                          </tr>
                          <tr height="11">
                             <td width="150">&nbsp;</td>
                             <td width="548">&nbsp;</td>
                          </tr>
                       </tbody>
                    </table>
                 </td>
              </tr>
           </tbody>
        </table>
        <table style="BACKGROUND:#efefef;" border="0" cellspacing="0" cellpadding="0" width="100%" align="center">
           <tbody>
              <tr>
                 <td>
                    <table style="BORDER-BOTTOM:#ddd 1px solid;BORDER-LEFT:#ddd 1px solid;BACKGROUND:#fff;BORDER-TOP:#ddd 1px solid;BORDER-RIGHT:#ddd 1px solid;" class="content" border="0" cellspacing="0" cellpadding="0" width="700" align="center">
                       <tbody>
                          <tr>
                             <td height="20"></td>
                          </tr>
                          <tr>
                             <td>
                                <table border="0" cellspacing="0" cellpadding="0" width="620" align="center">
                                   <tbody>
                                      <tr>
                                         <td height="20"></td>
                                      </tr>
                                      <tr>
                                         <td style="LINE-HEIGHT:32px;FONT-FAMILY:arial;COLOR:#f60;FONT-SIZE:24px;" valign="top">{{$form['context']}} Form - Shared with you</td>
                                      </tr>
                                      <tr>
                                         <td height="14">&nbsp;</td>
                                      </tr>
                                      <tr>
                                         <td style="LINE-HEIGHT:40px;FONT-FAMILY:arial;COLOR:#666;FONT-SIZE:18px;" valign="top">Dear {{ ucfirst($user['first_name'])." ".ucfirst($user['last_name']) }},</td>
                                      </tr>
                                      <tr>
                                         <td style="LINE-HEIGHT:18px;FONT-FAMILY:arial;COLOR:#666;FONT-SIZE:12px;">
                                             <p>
                                                <b>{{ $form['name'] }} (ID: {{ $form['id'] }})</b> has been shared with you.

                                                <br/><span><b>Form Status: </b></span><span style="color:<?php
                                                switch($form['current_activity']['status'])
                                                {
                                                    case SwiftWorkflowActivity::INPROGRESS:
                                                        echo "#c79121;";
                                                        break;
                                                    case SwiftWorkflowActivity::COMPLETE:
                                                        echo "#356e35;";
                                                        break;
                                                    case SwiftWorkflowActivity::REJECTED:
                                                    default:
                                                        echo "#a90329;";
                                                        break;
                                                }
                                                ?>"><?php
                                                switch($form['current_activity']['status'])
                                                {
                                                    case SwiftWorkflowActivity::INPROGRESS:
                                                        echo "Pending for <u>".$form['current_activity']['label'];
                                                        break;
                                                    case SwiftWorkflowActivity::COMPLETE:
                                                        echo "Complete";
                                                        break;
                                                    case SwiftWorkflowActivity::REJECTED:
                                                        echo "Cancelled";
                                                        break;
                                                    default:
                                                        echo "Unknown";
                                                }

                                                ?></span>

                                                @if($form['msg'] !== null && $form['msg'] !== "")
                                                <br/><b>Message from {{$form['from_user_full_name']}}:</b>
                                                    <blockquote>
                                                        "{{nl2br($form['msg'])}}"
                                                    </blockquote>
                                                @endif
                                             </p>
                                             <br/>
                                             <br/>
                                         </td>
                                      </tr>
                                      <tr>
                                          <td style="LINE-HEIGHT:18px;FONT-FAMILY:arial;COLOR:#666;FONT-SIZE:12px;">
                                             <p>
                                                <a style="FONT-FAMILY:arial;COLOR:#999;TEXT-DECORATION:underline;" href="{{ $form['url'] }}" target="_blank">Click here to view this form</a>
                                             </p>
                                          </td>
                                      </tr>
                                   </tbody>
                                </table>
                                <table border="0" cellspacing="0" cellpadding="0" width="620" align="center" height="100">
                                   <tbody>
                                      <tr>
                                         <td style="LINE-HEIGHT:18px;FONT-FAMILY:arial;COLOR:#666;FONT-SIZE:12px;" width="450">
                                             <p><br>Sincerely,<br> Scott Swift <br><span>{{ date('Y.m.d h:m') }}</span><br><br>This is an automated system email. Please do not reply to this email.</p>
                                         </td>
                                      </tr>
                                   </tbody>
                                </table>
                             </td>
                          </tr>
                          <tr>
                             <td height="20">&nbsp;</td>
                          </tr>
                       </tbody>
                    </table>
                 </td>
              </tr>
           </tbody>
        </table>
        <table style="BACKGROUND:#efefef;" border="0" cellpadding="0" width="100%" align="center" height="auto">
           <tbody>
              <tr>
                 <td>
                    <table style="BACKGROUND:#efefef;" border="0" cellpadding="0" width="100%" align="center" height="auto">
                       <tbody>
                          <tr>
                             <td>
                                <table class="footer" border="0" cellspacing="0" cellpadding="0" width="700" bgcolor="#efefef" align="center" height="120">
                                   <tbody>
                                      <tr>
                                         <td style="LINE-HEIGHT:18px;FONT-FAMILY:arial;COLOR:#999;FONT-SIZE:11px;" valign="bottom">
                                             Site Access: <a style="FONT-FAMILY:arial;COLOR:#999;FONT-SIZE:11px;TEXT-DECORATION:underline;" href="{{ Config::get('app.url') }}" target="_blank">Homepage</a>
                                             <br>
                                             This email was sent to {{ $user['email'] }} <br>You are receiving this email because you are a registered member of <a style="FONT-FAMILY:arial;COLOR:#999;FONT-SIZE:11px;TEXT-DECORATION:underline;" href="{{ Config::get('app.url') }}" target="_blank">Scott Swift</a> <br>
                                             Scott & Co Ltd, Industrial Park 1, Riche-Terre, Mauritius.
                                         </td>
                                      </tr>
                                      <tr>
                                         <td height="20">&nbsp;</td>
                                      </tr>
                                   </tbody>
                                </table>
                             </td>
                          </tr>
                       </tbody>
                    </table>
                 </td>
              </tr>
           </tbody>
        </table>
    </body>
</html>