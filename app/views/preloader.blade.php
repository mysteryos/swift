<!-- PRE-LOADER -->
<div id="loading" style="position: fixed; top: 0; width: 100%;height: 100%;z-index: 2000;background-color: #f5f5f5;font-family:Arial;transition: opacity 1s ease-in-out;opacity:1;">
    <div style="text-align: center;">
        <div class="logo-container" style="margin-top:100px; text-align:center;">
            <img src="/img/logo-mini.png" alt="Scott Swift" />
        </div>
        <div class="cmsg" style="margin: 1em;">
            <div class="msg" style="margin-top: 50px;font-weight:bold;font-size:1.2em;">Loading {{ Sentry::getUser()['email'] }}…</div>
            <div class="pl-loading-bar-container" style="text-align: center;width: 420px; border: 1px solid #999; padding: 1px; height: 14px; margin-right: auto; margin-left: auto;">
                <div id="pl-loading-bar" style="width: 0%;height: 100%;background-color: #c2202d;background-repeat: repeat-x;background-position: 0 0;background-size: 16px 8px;background-image: linear-gradient(45deg, rgba(0, 0, 0, 0.15) 25%, transparent 25%, transparent 50%, rgba(0, 0, 0, 0.15) 50%, rgba(0, 0, 0, 0.15) 75%, transparent 75%, transparent);background-size: 40px 40px;line-height: 20px;overflow: hidden;@keyframes progress-bar-stripes {0% {background-position: 40px 0;} 100% {background-position: 0 0;}}animation: 1.5s linear 0s normal none infinite progress-bar-stripes;transition: width 500ms ease-in-out"></div>
            </div>
        </div>
        <div id="slowConnectionError" style="display:none;">
            <p style="font-size:large;margin:40px;">
                This is taking longer than usual. 
                <a href="#" onClick="location.reload()">
                    <b>Try reloading the page</b>
                </a>
            </p>
        </div>
        <div id="loadingError" style="display:none;">
            <p style="font-size:large;margin:40px;" id="loadingErrorParagraph">
                Your internet connection appears to be unstable. <a href="#" onClick="location.reload()"><b>Click here to reload the page</b></a>
            </p>            
        </div>
    </div>
</div>
<!-- END PRE-LOADER -->