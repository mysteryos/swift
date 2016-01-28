<!DOCTYPE html>
<html lang="en-uk">
<head>
    <meta charset="utf-8"/>
    <!--<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">-->

    <title> {{ Config::get('website.name') }} - Privacy </title>
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
                <h1>Privacy</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 text-justify">
                <p>This privacy policy ("Policy") discloses the privacy practices for any websites or services (including any of our applications for use with hardware or software of others, such as iPhone, Chrome, Android, Outlook, and Mac Mail) owned or controlled by Scott that link to this Policy. This Policy will apply with respect to your use of any such websites and services regardless of (i) your method of access (for example, computer browser, Outlook application, iPhone, etc.), or (ii) whether you are a registered user or a visitor. Each of the websites and/or services covered by this Policy are referred to as a "site", and all such websites and services are collectively referred to as the "sites".</p>
                <p>This Policy addresses the information and content we currently collect from you in the course of your visit to our sites and your use of them. Information you disclose to us in the course of your access and use of our sites is referred to generally in this Policy as "personal information"; this personal information includes your user information, billing information and user content (as those terms are defined below) as well as any other information disclosed, provided or obtained by us in the course of our relationship. The Policy may also address some types of content, information, means of collecting such content and information, and uses of it, that may not presently apply to any of the sites. We tell you about these types of possibilities now because we want to maintain flexibility in offering additional features without having to revise our Policy every time we revise a site or offer new functions. No description of any type of content or information, means of collecting the content or information, or use of the content or information will require us to collect or make any particular use of any such content or information, or offer any particular functionality through any site; your use of any of our sites is always as provided for in the Terms of Service specific for that site, and you should review those Terms to understand your rights and obligations with respect to your use of that site.</p>
                <h2>Content Ownership, Collection and Use</h2>
                <p>You always retain whatever ownership rights you may have in any content, data, or other such materials you upload or otherwise actively provide to us through the sites (the "user content"); provided, however, that you grant us (and our employees, agents, successors and assigns) a worldwide, irrevocable, perpetual, fully-paid license to use such user content in any manner reasonably necessary for us to operate and provide services in connection with the sites. We will not sell, share, or rent this user content to others in ways different from those disclosed in this Policy.</p>
                <p>In addition to user content you may upload or otherwise provide to us, we may collect additional information relating to, for example, your activities while visiting our site, at several different points on a site, and additional information on our collection and use of such additional information is provided below.</p>
                <h2>Registration, Ordering, and Related Processes</h2>
                <p>If you (i) order products or services using a site, (ii) use services from a site (regardless as to whether you are a registered user), (iii) sign up for a newsletter, (iv) participate in a survey or contest, or register for an event, or (v) otherwise contact us, we may request information from you using an order or registration form, survey form, or otherwise. Such information may include things such as your name, username, password, email address, business information, and general information about your interest in and use of the sites, as well as relevant demographic information. In addition we may automatically collect information about your computer or internet access, such as IP address, web browser, internet service provider (ISP), referring/exit pages, platform type, and your usage of our sites and applications. All of the foregoing types of information will be collectively referred to as "user information", and we may use such user information in connection with the operation of our services and/or your use of them or the sites, and otherwise in connection with our business operations. We may use the user information to better understand and market to our customers and site users, individually and in the aggregate; for example, we may use this user information for product and service development, and for sales and marketing purposes. We may share this user information with third parties who are working with us to identify, develop and/or offer products and services which may be of interest to you, but will not otherwise sell or rent such user information to unrelated third parties or otherwise give such user information to third parties for their use unconnected with a use by or for Scott.</p>
                <p>Please note that when invitations are sent to other individuals via our Service (a "New User") or you use our Service to share a task with an individual not registered on the Service (a "Collaborator"), that person may have access to your email address and/or other contact information shared in such invitation or shared task. Furthermore, Scott may also remind New Users of the aforementioned invitation or task (and/or any updates thereto) multiple times per invitation or shared task.</p>
                <h2>Cookies</h2>
                <p>We may use cookies in connection with our websites and services and some of the cookies may be linked to your personally identifiable information. Any time you register on a site, use a site, place an order through a site, or identify yourself or the computer you are using through a site, you will be deemed to have given use permission to link your personally identifiable information with cookies.</p>
                <p>Most or all browsers permit you to disable or reject cookies. You can do this by setting the preferences in the browser. Use the "help" feature of your browser to obtain more information about refusing cookies. However, if you set the browser you use to reject cookies or otherwise disable them, you may not be able to use any or all of the functionality at one or more of the sites or it may take additional time to utilize such functionality. If you wish to use any such functionality that requires the use of cookies at any of our sites, then you must accept the use of cookies for that site, and thereby, give us your permission to link your data as discussed above.</p>
                <p>One or more of the organizations working with us to develop or provide our services or products, or with which we otherwise do business, or to which we provide links from a site, may also use cookies of their own. We have no control over such organizations' uses of cookies and users should review the privacy policies of such organizations to determine the uses such organizations make of cookies.</p>
                <h2>Browser add-in for task tracking</h2>
                <p>In order to help track tasks you create on websites and enable you to share those tasks with others as you choose, and to check whether anyone else has shared any tasks with you, we may analyze information about the sites you have visited to determine if any relevant tasks are associated with them. To do this, Scott's browser extension will check the sites you visit to see if any tasks are associated with them in Scott accounts you are part of. If so, the browser extension then submits information about only those domain names which have tasks associated with them to Scott's servers, using an encrypted format, so that the servers can search for indexed tasks belonging to (or shared with) your existing account. If there are any such tasks associated with the domain and URL you have visited, the Scott server will return names of tasks relevant for the visited domain and URL back to your browser extension. The browser extension performs the initial screening of the domains you have visited to identify those for which tasks have been created and only sends information about those domains and URLs on to the Scott servers. The information sent to the Scott servers to perform this check is not stored on the Scott servers.</p>
                <h2>Sharing of Information</h2>
                <ul>
                    <li>Aggregated information. We and our agents may share aggregated demographic information with our users, our affiliated organizations, and other organizations with which we do, or contemplate doing, business. Such information is aggregated and is not linked to any information that can identify individual users.</li>
                    <li>Shipping/Service providers. We may use outside shipping or other service providers to process and ship orders or perform other functions including, without limitation, customer service and inquiry responses. Personally identifiable information may be provided to such service providers in order to allow them to provide shipping, services and/or other such functions. The requirements or requests that we impose on such service providers vary with the sensitivity of the information and can, but do not necessarily, include requirements that these outsourcing providers not retain, share, store, or use personally identifiable information for any secondary purposes, except for backup and recovery operations. Although we use commercially reasonable efforts to impose, and/or ensure compliance by our outsourcing providers, we cannot, and will not, be responsible to users for misuse of personally identifiable information by such service providers. This section is meant as a general description of our practices. It does not impose any duty upon us and it does not constitute a representation or warranty by us upon which you may rely.</li>
                    <li>Specific services. Although we generally host our own sites, we may have agreements with other parties to provide you with specific additional services, including, without limitation, third-party hosted collaboration tools and other hosted services. When you use such services, we may share personally identifiable information with such parties. In such cases, we will use commercially reasonable efforts to restrict the information provided to the information necessary for the provision of such services.</li>
                    <li>General use. Notwithstanding any other provision of this Privacy Policy, we may share personally identifiable information with various vendors, suppliers, and partners. While we use commercially-reasonable efforts to verify that such vendors, suppliers, and partners provide products and services of interest to site users, we cannot, and do not, endorse such vendors, suppliers, advertisers, products or services unless we expressly state otherwise. If you wish us to refrain from providing your personally identifiable information in this manner, please see the opt-out information and contact information provided below.</li>
                    <li>Aggregate and Anonymous Data. Scott (including our vendors, suppliers and partners) shall have a worldwide, perpetual, irrevocable, royalty-free right to use aggregated and/or anonymous data in connection with our and our vendor's, supplier's and partner's business operations including, without limitation, combining such data with other similar data from you and other users.</li>
                    <li>Assignment for Sale or Merger. In addition, as we develop our business, we might sell or otherwise transfer all or parts of our businesses or assets. We may also disclose your personal information (including user information, billing information and user content) to a third party as part of a sale of assets of Scott, merger, reorganization, dissolution or similar event, or as the result of a change in control of the company or one of its affiliates, or in preparation for any of these events. Any third party to which we transfer or sell our assets will have the right to continue to use the personal and other information that you provide to us in the manner set out in this Policy and then in accordance with the terms of their privacy policy, subject to the terms of the section below entitled "Changes to the Privacy Policy."</li>
                </ul>
                <h2>Supplementation of Information</h2>
                <p>We sometimes supplement the user information we receive from you with other information we receive from public and/or third party sources, such as credit card issuers or clearinghouses. We will consider any such supplemental information about you as user data and may use it in accordance with the terms of this Policy.</p>
                <h2>Links and Information Gathered by Others</h2>
                <p>One or more sites may contain links to, or involve processing of data or information by, other websites. We do not operate those websites and we cannot control the information that the operators of such websites gather or what the operators of such websites do with the information. We are therefore not responsible for the activities of the operators of such websites. If you choose to visit such websites and give them any information or they collect it during your visit, it will be governed by their privacy policy(ies).</p>
                <h2>Security</h2>
                <p>Where we or our agents collect nonpublic personal information from you, we or they will generally take reasonable steps to protect such information in transmission, but there may be circumstances when such information will not be protected that way. Unless we have or our agent has specifically identified the connection as secure or otherwise let you know your information is secure in a particular situation, you should assume that the connection is not secure and that it is possible for third parties to surreptitiously and/or illegally intercept the information shared by you and us during that part of the session. If you use third-party software integrated into our service (for example, Google Drive) your information will be handled by such third-party and we cannot assure you that such information will be handled in a secure manner.</p>
                <h2>Email Communications</h2>
                <p>We may send to you one or more welcome emails that may also verify password and user name information, and we may send to you updates, service announcements, administrative messages, or other important information about one or more of the sites and/or our services. We may also send you newsletters, notifications or other information about products, services, and special deals we think may be of interest to users like you. Some of these communications - such as those with service announcements or such -- are tied to the service and contain important information about the service or your use of it. For those types of communications, you can only unsubscribe from them by cancelling your subscription. When emails are not tied to use of the service, we will usually provide an unsubscribe link within them.</p>
                <h2>Misappropriation of Personal Information</h2>
                <p>For the purposes of any applicable law regarding notification of persons whose personal information was, or is reasonably believed to have been, acquired by an unauthorized person, our information security policy provides that any required notification may, where permitted by law, be made by the use of email, telephone, fax, mail (including a notice printed in an available area of a bill or statement) or posting a notice on a site. The specific means used is up to us and we will use our judgment based on the circumstances. Where any notice is to be sent to a specific address or number (such as email address, physical address, telephone number, etc.), we will use the latest available address in our records. EXCEPT TO THE EXTENT PROHIBITED BY LAW, YOU AGREE TO THIS MEANS OF NOTIFICATION.</p>
                <h2>Correcting or Updating Personal Information</h2>
                <p>If your personal information changes, or if you have reason to believe that your personal information as we maintain it is incorrect, you may contact us using the contact information below and we will accommodate all reasonable requests for such changes.</p>
                <h2>Information about Unsubscribing and Opting Out</h2>
                <p>Users who no longer wish to receive newsletters or other promotional materials will generally be provided with a link or other mechanism to use to unsubscribe from the receiving the respective materials. You may opt out of receiving such newsletters or other promotional materials by following the procedure provided there.</p>
                <p>Please note, for certain communications (such as related to your account information or system status) and the sharing of certain of your information, it may not be possible for you to just opt out of having your information shared; your only option would be to terminate your service.</p>
                <p>In most cases, it is impractical for us to stop any other third party to whom we have supplied your information pursuant to the terms of this Policy from continuing to use such information after you have opted out. In other words, opting out will usually not stop other third parties to whom we have provided your information from continuing to use it.</p>
                <h2>Response Times</h2>
                <p>We will use reasonable efforts to timely make any changes you request. Many such changes are accomplished using batch processing (i.e. collecting a number of similar change requests and making all such changes at once), so the changes may not be immediately effective but may take 30 days or longer. If you require an immediate change to your personally identifiable information and are unable to make such a change using the available site resources, please contact us.</p>
                <h2>Changes to This Privacy Policy</h2>
                <p>We may choose to make changes to this Policy at any time. If we decide to change this Policy, we will post the changes on one or more sites and/or other places we deem appropriate. We may, but are not obligated, to send you an email or other notification of such change; but you should review this Policy from time to time for significant changes. If you agree to the changes, you don't need to do anything. But if you do not agree to the changes, you must discontinue use of our sites and services. If you continue to use our sites and services after the effective date of any change, you are deemed to have accepted the change.</p>
                <h2>Exceptions</h2>
                <p>Except as stated below, we will use information in accordance with this Policy as it may be changed from time to time as set forth above. Notwithstanding anything else in this Policy to the contrary, we may collect personally identifiable information and use such information in ways other than those described above if we are required to do so by any applicable law or if we deem it advisable in the course of (i) assisting law enforcement activities, or (ii) investigating and resolving disputes between users; and (iii) protecting our site(s) or other property, including, without limitation, investigating, preventing or taking action with respect to illegal activities, suspected fraud, situations involving the potential safety of any person, violations of Scott's terms of use, or as otherwise required by law. Without limiting the foregoing, we reserve the right to use and disclose any information that you provide to us if we deem it advisable in the prosecution or defense of any litigation involving your use of any site.</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>