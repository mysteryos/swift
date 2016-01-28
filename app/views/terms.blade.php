<!DOCTYPE html>
<html lang="en-uk">
<head>
    <meta charset="utf-8"/>
    <!--<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">-->

    <title> {{ Config::get('website.name') }} - Terms & Conditions </title>
    <meta name="description" content=""/>
    <meta name="author" content="Pudaruth Keshav"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>

    <!-- Basic Styles -->
    <link rel="stylesheet" href="{{Bust::url('/css/bootstrap.min.css')}}" />
    <link rel="stylesheet" href="{{Bust::url('/css/font-awesome.min.css')}}" />

    <!-- SmartAdmin Styles -->
    <link rel="stylesheet" href="{{Bust::url('/css/smartadmin-production.css')}}" />

    <!-- FAVICONS -->
    <link rel="shortcut icon" href="/img/favicon/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/img/favicon/favicon.ico" type="image/x-icon">

    <!-- GOOGLE FONT -->
    <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,300,400,700"/>

</head>
<body id="login" class="animated fadeInDown">
<!-- possible classes: minified, no-right-panel, fixed-ribbon, fixed-header, fixed-width-->
<header id="header">
    <div id="logo-group">
        <span id="logo">
            <img src="/img/logo.png" alt="Scott Swift"/>
        </span>
    </div>
</header>

<div id="main" role="main">

    <!-- MAIN CONTENT -->
    <div id="content" class="container">
        @if(isset($msgalert))
            <div class="row">
                <div class="col-xs-12">
                    @if($msgalert['status']==1)
                        <div class="alert alert-danger fade in">
                            <i class="fa-fw fa fa-times"></i>
                            <strong>Error!</strong> {{ $msgalert['msg'] }}
                        </div>
                    @elseif($msgalert['status']==2)
                        <div class="alert alert-warning fade in">
                            <i class="fa-fw fa fa-warning"></i>
                            <strong>Warning</strong> {{ $msgalert['msg'] }}
                        </div>
                    @endif
                </div>
            </div>
        @endif
        @if(Session::has('expired'))
            <div class="row">
                <div class="col-xs-12">
                    <div class="alert alert-info fade in">
                        <i class="fa-fw fa fa-info"></i>
                        <strong>Info!</strong> Your session has expired. Please login to continue.
                    </div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-xs-12 text-center">
                <h1>Terms & Conditions</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 text-justify">
                <h2>Acceptance of terms</h2>
                <p>By using Swift`s online and offline products and services (collectively, "the Service"), provided by Scott & Co Ltd. (collectively, "Scott," "We" or "Us") you agree to be bound by the following Terms of Service ("TOS"). The TOS may be updated by us from time to time without notice.</p>
                <p>Swift only offers accounts on behalf of Scott and does not offer personal accounts on behalf of individuals. You represent and agree that (i) you are entering into this agreement on behalf of the company or other legal entity (collectively, the "Business User") that you may specify, (ii) that your account is for, and held in the name of, the Business User (and not any individual), (iii) such Business User has full legal capacity and is in good standing in the jurisdiction in which it is formed, (iv) you have full legal capacity and authority to bind yourself individually and such Business User to these TOS, and (iv) the terms "you" or "your," as used herein shall, unless the context otherwise reasonably requires, refer to both (A) such Business User, and (B) the individual or individuals (the "Individual User(s)") accessing or using the Service as authorized by such Business User; provided, however, that each such Individual User(s) shall remain vicariously liable and be required to comply with these TOS even though the account may be held in the name of the Business User. If you do not meet the requirements above, or if you do not agree with these terms and conditions, you may not use the Service.</p>
                <p>You also understand and agree that the Service may include certain communications from Scott, such as service announcements and administrative messages, and that these communications are considered part of Swift membership and that you will not be able to opt out of receiving them. Changes and features that augment or enhance the current Service shall be subject to the TOS. You understand and agree that the Service is provided "as is" and that Scott assumes no responsibility for the timeliness, deletion, mis-delivery of or failure to store any user content or settings. You are responsible for obtaining access to the Service, which access may involve third-party fees (such as Internet Service Provider charges). You are responsible for those fees, including fees associated with the display or delivery of advertisements. In addition, you must provide and are responsible for all equipment necessary to access the Service.</p>
                <h2>Your account</h2>
                <p>In consideration of your use of the Service, you represent and warrant that (i) you are not barred from receiving services under the laws of Mauritius or any other applicable jurisdiction, (ii) your use of the Service does not violate any applicable law or regulation, and (iii) you access the Service through one or more humans. Accounts registered by "bots" or other automated methods are not permitted. You also agree to: (a) provide true, accurate, current and complete information about yourself as prompted by the Service's registration form ("Registration Data"); and (b) maintain and promptly update the Registration Data to keep it true, accurate, current and complete. If you provide any information that is untrue, inaccurate, not current or incomplete, or Scott has reasonable grounds to suspect that such information is untrue, inaccurate, not current or incomplete, Scott has the right to suspend or terminate your account and refuse any and all current or future use of the Service (or any portion thereof).</p>
                <p>Registration Data and certain other information about you is subject to our Privacy Policy which is incorporated by reference herein in its entirety. For more information, see our full privacy policy at <a href="{{action('TOSController@privacy')}}" target="_blank">{{action('TOSController@privacy')}}</a>. You understand that through your use of the Service you consent to the collection and use (as set out in the Privacy Policy) of this information.</p>
                <p>You will receive an account designation upon completing the Service's registration process. You are responsible for maintaining the confidentiality of the account and are fully responsible for all activities that occur under your account. You agree to (a) immediately notify Scott of any unauthorized use of your account or any other breach of security; and (b) ensure that you exit from your account at the end of each session. Scott cannot and will not be liable for any loss or damage arising from your failure to comply with the TOS, including, without limitation, this Section 2.</p>
                <h2>Content</h2>
                <p>You understand that all information, data, text, or other materials ("Content"), whether publicly posted or privately transmitted, are the sole responsibility of the person from whom such Content originated. This means that you, and not Scott, are entirely responsible for all Content that you upload, post, email, transmit or otherwise make available via the Service. Scott does not control the Content posted via the Service and, as such, does not guarantee the accuracy, integrity or quality of such Content. You understand that by using the Service, you may be exposed to Content that is offensive, indecent, objectionable or illegal in your jurisdiction. Under no circumstances will Scott be liable in any way for any Content, including, but not limited to, any errors or omissions in any Content, or any loss or damage of any kind incurred as a result of the use of any Content posted, emailed, transmitted or otherwise made available via the Service.</p>
                <p>You agree to not use the Service to:<br>
                    <ul>
                        <li>upload, post, email, transmit or otherwise make available any Content that is unlawful, harmful, threatening, abusive, harassing, tortuous, defamatory, vulgar, obscene, libelous, invasive of another's privacy, hateful, or racially, ethnically or otherwise objectionable, under any applicable laws;</li>
                        <li>harm minors in any way;</li>
                        <li>impersonate any person or entity, or falsely state or otherwise misrepresent your affiliation with a person or entity;</li>
                        <li>disguise the origin of any Content transmitted through the Service;</li>
                        <li>upload, post, email, transmit or otherwise make available any Content that you do not have a right to make available under any applicable law or under contractual or fiduciary relationships (such as inside information, proprietary and confidential information learned or disclosed as part of employment relationships or under nondisclosure agreements);</li>
                        <li>upload, post, email, transmit or otherwise make available any Content that infringes any patent, trademark, trade secret, copyright or other proprietary rights of any party;</li>
                        <li>upload, post, email, transmit or otherwise make available any unsolicited or unauthorized advertising, promotional materials, "junk mail", "spam", or any other form of solicitation;</li>
                        <li>upload, post, email, transmit or otherwise make available any material that contains software viruses or any other computer code, files or programs designed to interrupt, destroy or limit the functionality of any computer software or hardware or telecommunications equipment;</li>
                        <li>act in a manner that negatively affects other users' ability to use the Service;</li>
                        <li>interfere with or disrupt the Service or servers or networks connected to the Service, or disobey any requirements, procedures, policies or regulations of networks connected to the Service;</li>
                        <li>intentionally or unintentionally violate any applicable local, state, national or international law;</li>
                    </ul>
                </p>
                <p>You acknowledge that Scott may or may not pre-screen Content, but that Scott and its designees shall have the right (but not the obligation) in their sole discretion to pre-screen, refuse, or move any Content that is available via the Service. Without limiting the foregoing, Scott and its designees shall have the right to remove any Content that violates the TOS or is otherwise objectionable. You agree that you must evaluate, and bear all risks associated with, the use of any Content, including any reliance on the accuracy, completeness, or usefulness of such Content. In this regard, you acknowledge that you may not rely on any Content created by Scott or submitted to Swift.</p>
                <p>You acknowledge, consent and agree that Scott may access, preserve and disclose your account information and Content if required to do so by any applicable law or in a good faith belief that such access preservation or disclosure is reasonably necessary to: (a) comply with any applicable legal process; (b) enforce the TOS; (c) respond to claims that any Content violates the rights of third parties; (d) respond to your requests for customer service; or (e) protect the rights, property or personal safety of Scott, its users and the public. If we receive a subpoena which requests disclosure of information contained in your account you agree that we may disclose any such requested information contained in the account regardless of whether such information is deemed to be owned or held in the name of (i) the Business User, or (ii) the name of Individual Users. For the sake of clarity, (i) if the subpoena is issued in the name of the Business User, we may disclose information regarding both the Business User and the Individual User(s), and (ii) if the subpoena is issued in the name of Individual User(s) we may disclose information regarding both the Business User and the Individual Users(s).</p>
                <p>You understand that the technical processing and transmission of the Service, including your Content, may involve (a) transmissions over various networks; and (b) changes to conform and adapt to technical requirements of connecting networks or devices.</p>
                <p>You may not attempt to override or circumvent any of the usage rules embedded into the Service. Any unauthorized reproduction, publication, further distribution or public exhibition of the materials provided on the Service, in whole or in part, is strictly prohibited.</p>
                <h2>Special admonitions for international use</h2>
                <p>Recognizing the global nature of the Internet, you agree to comply with any and all applicable local, state, national or international laws and regulations regarding online conduct, acceptable Content and use of the Service. Specifically, you also agree to comply with all applicable laws regarding the transmission of technical data exported from Mauritius or the country or jurisdiction in which you reside.</p>
                <h2>Indemnity</h2>
                <p>You (specifically including the Business User and Individual User(s)) agree to indemnify and hold Scott and its parent, subsidiaries, affiliates, officers, directors, stockholders, agents, attorneys, employees, partners, licensors and other representatives harmless from any claim or demand, including reasonable attorneys' fees, made by any third party due to or arising out of, or in connection with, (i) Content you submit, post, transmit or otherwise make available through the Service, (ii) your use or access of the Service, (iii) your connection to the Service, (iv) your violation of the TOS, (v) your violation of any rights of another, and (vi) any taxes arising in connection with your purchase or use of the Service in any jurisdiction, domestic or otherwise, including, without limitation, sales and use tax.</p>
                <h2>No resale of Service</h2>
                <p>You agree not to reproduce, duplicate, copy, sell, trade, resell or exploit for any commercial purposes, any portion of the Service, use of the Service, or access to the Service unless you otherwise have an agreement with us which specifically grants you such right(s).</p>
                <h2>General practices regarding use and storage</h2>
                <p>You acknowledge that Scott may establish general practices and limits concerning use of the Service, including without limitation the maximum number of days that Content will be retained by the Service, the maximum number of email messages that may be sent from or received by an account on the Service, the maximum size of any email message that may be sent from or received by an account on the Service, the maximum disk space that will be allotted on Scott's servers on your behalf, and the maximum number of times (and the maximum duration for which) you may access the Service in a given period of time. You agree that Scott has no responsibility or liability for the deletion or failure to store any Content and other communications maintained or transmitted by the Service. You acknowledge that Scott reserves the right to log off accounts that are inactive for an extended period of time. You further acknowledge that Scott reserves the right to modify these general practices and limits from time to time.</p>
                <h2>Modifications to Service</h2>
                <p>Scott reserves the right at any time and from time to time to modify or discontinue, temporarily or permanently, the Service (or any part thereof) with or without notice. You agree that Scott shall not be liable to you or to any third party for any modification, suspension or discontinuance of the Service.</p>
                <h2>Termination and cancellation</h2>
                <p>You agree that Scott may without prior notice immediately terminate your Swift account and access to the Service (both as a Business User and/or Individual User(s)). Such termination may be made in Scott's sole and absolute discretion with or without cause. For illustrative purposes only, the situations in which Scott may terminate your account and access to the Service shall include, but not be limited to: (a) breaches or violations of the TOS or other incorporated agreements or guidelines; (b) requests by law enforcement or other government agencies; (c) a request by you (self-initiated account deletions): (d) discontinuance or material modification to the Service (or any part thereof): (e) unexpected technical or security issues or problems; (f) extended periods of inactivity; (g) engagement by you in fraudulent or illegal activities; and/or (h) nonpayment of any fees owed by you in connection with the Service. Further, you agree that all terminations for cause shall be made in Scott's sole and absolute discretion and that Scott shall not be liable to you or any third party for any termination of your account, or access to the Service.</p>
                <h2>Links</h2>
                <p>The Service may provide, or third parties may provide, links to other World Wide Web sites or resources. Because Scott has no control over such sites and resources, you acknowledge and agree that Scott is not responsible for the availability of such external sites or resources, and does not endorse and is not responsible or liable for any Content, advertising, products or other materials on or available from such sites or resources. You further acknowledge and agree that Scott shall not be responsible or liable, directly or indirectly, for any damage or loss caused or alleged to be caused by or in connection with use of or reliance on any such Content, goods or services available on or through any such site or resource.</p>
                <h2>Swift's proprietary rights</h2>
                <p>You acknowledge and agree that the Service and any necessary software used in connection with the Service ("Software") contain proprietary and confidential information that is protected by applicable intellectual property and other laws. You further acknowledge and agree that Content contained in sponsor advertisements or information presented to you through the Service or by advertisers is protected by copyrights, trademarks, service marks, patents or other proprietary rights and laws. Except as expressly authorized by Scott, you agree not to modify, rent, lease, loan, sell, distribute or create derivative works based on the Service or the Software, in whole or in part.</p>
                <p>Scott grants you a personal, non-transferable and non-exclusive right and license to use the object code of its Software on your computing devices, subject to the terms and conditions of this Agreement. You shall not (and shall not allow any third party to) copy, modify, create a derivative work from, reverse engineer, reverse assemble or otherwise attempt to discover any source code, sell, assign, sublicense, grant a security interest in or otherwise transfer any right in the Software. You agree not to modify the Software in any manner or form, or to use modified versions of the Software, including (without limitation) for the purpose of obtaining unauthorized access to the Service. You agree not to access the Service by any means other than through the interface that is provided by Scott for use in accessing the Service.</p>
                <h2>Disclaimer of warranties</h2>
                <p>YOU EXPRESSLY UNDERSTAND AND AGREE THAT:<br>
                    <ul>
                        <li>YOUR USE OF THE SERVICE IS AT YOUR SOLE RISK. THE SERVICE IS PROVIDED ON AN "AS IS" AND "AS AVAILABLE" BASIS. SCOTT AND ITS PARENT,SUBSIDIARIES, AFFILIATES, OFFICERS, DIRECTORS, STOCKHOLDERS, EMPLOYEES, AGENTS, ATTORNEYS, PARTNERS, LICENSORS AND OTHER REPRESENTATIVES EXPRESSLY DISCLAIM ALL WARRANTIES OF ANY KIND, WHETHER EXPRESS OR IMPLIED, INCLUDING, BUT NOT LIMITED TO THE IMPLIED WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT.</li>
                        <li>SCOTT AND ITS PARENT, SUBSIDIARIES, AFFILIATES, OFFICERS, DIRECTORS, STOCKHOLDERS, EMPLOYEES, AGENTS, ATTORNEYS, PARTNERS, LICENSORS AND OTHER REPRESENTATIVES MAKE NO WARRANTY THAT: (i) THE SERVICE WILL MEET YOUR REQUIREMENTS; (ii) THE SERVICE WILL BE UNINTERRUPTED, TIMELY, SECURE OR ERROR-FREE; (iii) THE RESULTS THAT MAY BE OBTAINED FROM THE USE OF THE SERVICE WILL BE ACCURATE OR RELIABLE; (iv) THE QUALITY OF ANY PRODUCTS, SERVICES, INFORMATION OR OTHER MATERIAL PURCHASED OR OBTAINED BY YOU THROUGH THE SERVICE WILL MEET YOUR EXPECTATIONS; AND (v) ANY ERRORS IN THE SOFTWARE WILL BE CORRECTED.</li>
                        <li>ANY MATERIAL DOWNLOADED OR OTHERWISE OBTAINED THROUGH THE USE OF THE SERVICE IS ACCESSED AT YOUR OWN DISCRETION AND RISK, AND YOU WILL BE SOLELY RESPONSIBLE FOR ANY DAMAGE TO YOUR COMPUTER SYSTEM OR LOSS OF DATA THAT RESULTS FROM THE DOWNLOAD OF ANY SUCH MATERIAL.</li>
                        <li>NO ADVICE OR INFORMATION, WHETHER ORAL OR WRITTEN, OBTAINED BY YOU FROM SCOTT OR THROUGH OR FROM THE SERVICE SHALL CREATE ANY WARRANTY NOT EXPRESSLY STATED IN THE TOS.</li>
                    </ul>
                </p>
                <h2>Limitation of liability</h2>
                <p>YOU EXPRESSLY UNDERSTAND AND AGREE THAT SCOTT AND ITS PARENT, SUBSIDIARIES, AFFILIATES, OFFICERS, DIRECTORS, STOCKHOLDERS, EMPLOYEES, AGENTS, ATTORNEYS, PARTNERS, LICENSORS AND OTHER REPRESENTATIVES SHALL NOT BE LIABLE TO YOU FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL OR EXEMPLARY DAMAGES, INCLUDING, BUT NOT LIMITED TO, DAMAGES FOR LOSS OF PROFITS, GOODWILL, USE, DATA OR OTHER INTANGIBLE LOSSES (EVEN IF SCOTT HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES), RESULTING FROM: (i) THE USE OR THE INABILITY TO USE THE SERVICE; (ii) THE COST OF PROCUREMENT OF SUBSTITUTE GOODS AND SERVICES RESULTING FROM ANY GOODS, DATA, INFORMATION OR SERVICES PURCHASED OR OBTAINED OR MESSAGES RECEIVED OR TRANSACTIONS ENTERED INTO THROUGH OR FROM THE SERVICE; (iii) UNAUTHORIZED ACCESS TO OR ALTERATION OF YOUR TRANSMISSIONS OR DATA; (iv) STATEMENTS OR CONDUCT OF ANY THIRD PARTY ON THE SERVICE; OR (v) ANY OTHER MATTER RELATING TO THE SERVICE. NOTWITHSTANDING ANYTHING TO THE CONTRARY CONTAINED HEREIN, SCOTT'S MAXIMUM AGGREGATE LIABILITY TO YOU FOR ANY CAUSES WHATSOEVER, AND REGARDLESS OF THE FORM OF ACTION, WILL AT ALL TIMES BE LIMITED TO THE GREATER OF (i) THE AMOUNT PAID, IF ANY, BY YOU TO SCOTT FOR THE SERVICE IN THE 12 MONTHS PRIOR TO THE ACTION GIVING RISE TO LIABILITY OR (ii) Rs. 3000.</p>
                <h2>Exclusions and limitations</h2>
                <p>SOME JURISDICTIONS DO NOT ALLOW THE EXCLUSION OF CERTAIN WARRANTIES OR THE LIMITATION OR EXCLUSION OF LIABILITY FOR INCIDENTAL OR CONSEQUENTIAL DAMAGES. ACCORDINGLY, SOME OF THE ABOVE LIMITATIONS OF SECTIONS 15 AND 16 MAY NOT APPLY TO YOU.</p>
                <h2>General information</h2>
                <p>Entire Agreement. The TOS constitute the entire agreement between you and Scott and govern your use of the Service, superseding any prior agreements between you and Scott with respect to the Service. You also may be subject to additional terms and conditions that may apply when you use or purchase certain other Scott services, affiliate services, third-party content or third-party software.
                Choice of Law and Forum. The TOS and the relationship between you and Scott shall be governed by the laws of the State of California without regard to its conflict of law provisions. You and Scott agree to submit to the personal and exclusive jurisdiction of the courts located within the County of Santa Clara, State of California regardless of (i) your world-wide physical location, or (ii) the jurisdiction where you purchased or use the Service.
                Notice and Future Changes. Scott may provide you with notices, including those regarding modifications to the TOS (including the Privacy Policy), by email or via the web-site. You agree to review the TOS (including the Privacy Policy) periodically so that you are aware of any modifications. Your continued use of the Service after any modifications indicates your acceptance of the modified TOS (and all other agreements, policies, rules and guidelines referred to herein). Unless expressly stated otherwise by Scott, any new features, new services, enhancements or modifications to the Service implemented after your initial access to the Service shall be subject to these TOS.
                Waiver and Severability of Terms. The failure of Scott to exercise or enforce any right or provision of the TOS shall not constitute a waiver of such right or provision. If any provision of the TOS is found by a court of competent jurisdiction to be invalid under applicable law, the parties nevertheless agree that the court should endeavor to give effect to the parties' intentions to the greatest extent possible as reflected in the provision, and the other provisions of the TOS shall remain in full force and effect.
                No Right of Survivorship and Non-Transferability. You agree that your Scott account is non-transferable and any rights to your Scott ID or contents within your account terminate upon cessation of your legal existence or death, as applicable. Upon receipt of a copy of a certificate of dissolution or death certificate, as applicable, your account may be terminated and all contents therein permanently deleted.
                Statute of Limitations. You agree that regardless of any statute or law to the contrary, any claim or cause of action arising out of or related to use of the Service or the TOS must be filed within one (1) year after such claim or cause of action arose or be forever barred.
                The section titles in the TOS are for convenience only and have no legal or contractual effect.</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>