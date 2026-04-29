//Detectar Navegador
var browser = (function (agent) {
    switch (true) {
        case agent.indexOf("edge") > -1: ; return "Edge";//Si es Edge No Chromium
        case agent.indexOf("edg/") > -1: ; return "Edge basado en Chromium "; // Match also / to avoid matching for the older Edge
        case agent.indexOf("opr") > -1 && !!window.opr: ; return "Opera";
        case agent.indexOf("chrome") > -1 && !!window.chrome: return "Chrome";
        case agent.indexOf("trident") > -1: ; ; return "Internet Explorer";
        case agent.indexOf("firefox") > -1: ; ; return "Firefox";
        case agent.indexOf("safari") > -1: ; ; return "Safari";
        default: return "Otro";
    }
})(window.navigator.userAgent.toLowerCase());

//Detecta el Lenguaje del Navegador
var userLang = navigator.language || navigator.userLanguage; 
// document.getElementById("lenguaje").innerHTML = "Lenguaje: " + userLang;

    
(function (window) {
    {
    var unknown = 'Unknown';

    //Tamaño de la Pantalla
    var screenSize = '';
    if (screen.width) {
        width = (screen.width) ? screen.width : '';
        height = (screen.height) ? screen.height : '';
        screenSize += '' + width + " x " + height;
    }

    //Navegador y Versión
    var nVer = navigator.appVersion;
    var nAgt = navigator.userAgent;
    var browser = navigator.appName;
    var version = '' + parseFloat(navigator.appVersion);
    var majorVersion = parseInt(navigator.appVersion, 10);
    var nameOffset, verOffset, ix;

    // Opera
    if ((verOffset = nAgt.indexOf('Opera')) != -1) {
        browser = 'Opera';
        version = nAgt.substring(verOffset + 6);
        if ((verOffset = nAgt.indexOf('Version')) != -1) {
            version = nAgt.substring(verOffset + 8);
        }
    }
    // MSIE
    else if ((verOffset = nAgt.indexOf('MSIE')) != -1) {
        browser = 'Microsoft Internet Explorer';
        version = nAgt.substring(verOffset + 5);
    }

    //IE 11 no longer identifies itself as MS IE, so trap it
    //http://stackoverflow.com/questions/17907445/how-to-detect-ie11
    else if ((browser == 'Netscape') && (nAgt.indexOf('Trident/') != -1)) {

        browser = 'Microsoft Internet Explorer';
        version = nAgt.substring(verOffset + 5);
        if ((verOffset = nAgt.indexOf('rv:')) != -1) {
            version = nAgt.substring(verOffset + 3);
        }

    }

    // Chrome
    else if ((verOffset = nAgt.indexOf('Chrome')) != -1) {
        browser = 'Chrome';
        version = nAgt.substring(verOffset + 7);
    }
    // Safari
    else if ((verOffset = nAgt.indexOf('Safari')) != -1) {
        browser = 'Safari';
        version = nAgt.substring(verOffset + 7);
        if ((verOffset = nAgt.indexOf('Version')) != -1) {
            version = nAgt.substring(verOffset + 8);
        }

        // Chrome on iPad identifies itself as Safari. Actual results do not match what Google claims
        //  at: https://developers.google.com/chrome/mobile/docs/user-agent?hl=ja
        //  No mention of chrome in the user agent string. However it does mention CriOS, which presumably
        //  can be keyed on to detect it.
        if (nAgt.indexOf('CriOS') != -1) {
            //Chrome on iPad spoofing Safari...correct it.
            browser = 'Chrome';
            //Don't believe there is a way to grab the accurate version number, so leaving that for now.
        }
    }
    // Firefox
    else if ((verOffset = nAgt.indexOf('Firefox')) != -1) {
        browser = 'Firefox';
        version = nAgt.substring(verOffset + 8);
    }
    //Otro Navegador
    else if ((nameOffset = nAgt.lastIndexOf(' ') + 1) < (verOffset = nAgt.lastIndexOf('/'))) {
        browser = nAgt.substring(nameOffset, verOffset);
        version = nAgt.substring(verOffset + 1);
        if (browser.toLowerCase() == browser.toUpperCase()) {
            browser = navigator.appName;
        }
    }
    //Hace un substring
    if ((ix = version.indexOf(';')) != -1) version = version.substring(0, ix);
    if ((ix = version.indexOf(' ')) != -1) version = version.substring(0, ix);
    if ((ix = version.indexOf(')')) != -1) version = version.substring(0, ix);

    majorVersion = parseInt('' + version, 10);
    if (isNaN(majorVersion)) {
        version = '' + parseFloat(navigator.appVersion);
        majorVersion = parseInt(navigator.appVersion, 10);
    }

    //Versión Móvil
    var mobile = /Mobile|mini|Fennec|Android|iP(ad|od|hone)/.test(nVer);

    // Cookies
    var cookieEnabled = (navigator.cookieEnabled) ? true : false;

    if (typeof navigator.cookieEnabled == 'undefined' && !cookieEnabled) {
        document.cookie = 'testcookie';
        cookieEnabled = (document.cookie.indexOf('testcookie') != -1) ? true : false;
    }

    //Sistema Operativo
    var os = unknown;
    var clientStrings = [
        {s:'Windows 3.11', r:/Win16/},
        {s:'Windows 95', r:/(Windows 95|Win95|Windows_95)/},
        {s:'Windows ME', r:/(Win 9x 4.90|Windows ME)/},
        {s:'Windows 98', r:/(Windows 98|Win98)/},
        {s:'Windows CE', r:/Windows CE/},
        {s:'Windows 2000', r:/(Windows NT 5.0|Windows 2000)/},
        {s:'Windows XP', r:/(Windows NT 5.1|Windows XP)/},
        {s:'Windows Server 2003', r:/Windows NT 5.2/},
        {s:'Windows Vista', r:/Windows NT 6.0/},
        {s:'Windows 7', r:/(Windows 7|Windows NT 6.1)/},
        {s:'Windows 8.1', r:/(Windows 8.1|Windows NT 6.3)/},
        {s:'Windows 8', r:/(Windows 8|Windows NT 6.2)/},
        {s:'Windows 10', r:/(Windows NT 10.0|WinNT10.0)/},
        {s:'Windows ME', r:/Windows ME/},
        {s:'Android', r:/Android/},
        {s:'Open BSD', r:/OpenBSD/},
        {s:'Sun OS', r:/SunOS/},
        {s:'Linux', r:/(Linux|X11)/},
        {s:'iOS', r:/(iPhone|iPad|iPod)/},
        {s:'Mac OS X', r:/Mac OS X/},
        {s:'Mac OS', r:/(MacPPC|MacIntel|Mac_PowerPC|Macintosh)/},
        {s:'QNX', r:/QNX/},
        {s:'UNIX', r:/UNIX/},
        {s:'BeOS', r:/BeOS/},
        {s:'OS/2', r:/OS\/2/},
        {s:'Search Bot', r:/(nuhk|Googlebot|Yammybot|Openbot|Slurp|MSNBot|Ask Jeeves\/Teoma|ia_archiver)/}
    ];
    for (var id in clientStrings) {
        var cs = clientStrings[id];
        if (cs.r.test(nAgt)) {
            os = cs.s;
            break;
        }
    }

    var osVersion = unknown;

    if (/Windows/.test(os)) {
        osVersion = /Windows (.*)/.exec(os)[1];
        os = 'Windows';
        // document.getElementById("OS1").className = "icon-windows";
    }

    switch (os) {
        case 'Mac OS X':
            osVersion = /Mac OS X (10[\.\_\d]+)/.exec(nAgt)[1];
            // document.getElementById("OS1").className = "icon-apple";
            break;

        case 'Android':
            osVersion = /Android ([\.\_\d]+)/.exec(nAgt)[1];
            // document.getElementById("OS1").className = "icon-android";
            break;

        case 'iOS':
            osVersion = /OS (\d+)_(\d+)_?(\d+)?/.exec(nVer);
            osVersion = osVersion[1] + '.' + osVersion[2] + '.' + (osVersion[3] | 0);
            // document.getElementById("OS1").className = "icon-apple";
            break;

    }
}

window.browserInfo = {
    screen: screenSize,
    browser: browser,
    browserVersion: version,
    mobile: mobile,
    os: os,
    osVersion: osVersion,
    cookies: cookieEnabled
};
}(this));

    //Detecta la arquitectura del SO
    if (navigator.userAgent.indexOf("WOW64") != -1 || navigator.userAgent.indexOf("Win64") != -1 ){
        //Si es de 64 bits
        var bits = " (64 Bits)";
    } else {
        //Si no es de 64 bits
        var bits = " (32 Bits)";
    }
        
    console.log(
        'browserInfo result: OS: ' + browserInfo.os +' '+ browserInfo.osVersion + ''+
            'Browser: ' + browserInfo.browser +' '+ browserInfo.browserVersion + '' +
            'Mobile: ' + browserInfo.mobile + '' +
            'Cookies: ' + browserInfo.cookies + '' +
            'Screen Size: ' + browserInfo.screen
    );
    
    // document.getElementById("OS1").innerHTML ='Sistema Operativo: ' + browserInfo.os + ' ' + browserInfo.osVersion + bits;
    // document.getElementById("OS2").innerHTML ='Navegador: ' + browser;


    //AQUI SE CONSIGUE LA INFO DEL NAVEGADOR Y DEL OS (USUARIO DEL SISTEMA Y CLIENTE)
    try{
        document.getElementsByName("browser_client")[0].value = browser;
        document.getElementsByName("os_client")[0].value = browserInfo.os + " " + browserInfo.osVersion + bits;
        document.getElementsByName("dName_client")[0].value = window.navigator.userAgent.toLowerCase()+JSON.stringify(getVideoCardInfo());
    }catch(error){

    }
   
    try{
        document.getElementsByName("browser_agent")[0].value = browser;
        document.getElementsByName("os_agent")[0].value = browserInfo.os + " " + browserInfo.osVersion + bits;
        document.getElementsByName("dName_agent")[0].value = window.navigator.userAgent.toLowerCase()+JSON.stringify(getVideoCardInfo());
    }catch(error){

    }
    // document.getElementById("OS2-2").innerHTML = window.navigator.userAgent.toLowerCase();
    // document.getElementById("OS3").innerHTML ='Dispositivo Móvil: ' + browserInfo.mobile;
    // document.getElementById("OS4").innerHTML ='Cookies: ' + browserInfo.cookies;
    // document.getElementById("OS5").innerHTML ='Tamaño de la Pantalla: ' + browserInfo.screen;

    //RAM
    var memory = navigator.deviceMemory;
    // gets the "available" logical processors count
    let logicalProcessorCount = navigator.hardwareConcurrency;
        
        
   
    
    console.log ('The current device has at least '+ memory + 'GB of RAM.');
    // document.getElementById("RAM").innerHTML = 'Este dispositivo tiene al menos ' + memory +' GB de RAM.';
    
    console.log(logicalProcessorCount + " Logical Cores");
    // document.getElementById("CORES").innerHTML = logicalProcessorCount + " Núcleos Lógicos";
    
        
    navigator.getBattery().then(function(battery) {
function updateAllBatteryInfo(){
updateChargeInfo();
updateLevelInfo();
updateChargingInfo();
updateDischargingInfo();
}
updateAllBatteryInfo();

battery.addEventListener('chargingchange', function(){
updateChargeInfo();
});
        
function updateChargeInfo(){
console.log("Battery charging? "
            + (battery.charging ? "Yes" : "No"));
//   document.getElementById("BATT-CH").innerHTML = " Cargando Batería: " + battery.charging;
  
  if(battery.charging==true){
    //   document.getElementById("BATT-CH").style="color:#8acc26";
  }
  else{
    //  document.getElementById("BATT-CH").style="color:#fa1900"; 
  }
}


battery.addEventListener('levelchange', function(){
updateLevelInfo();
});
function updateLevelInfo(){
console.log("Nivel de Batería: "
            + battery.level * 100 + "%");
//   document.getElementById("BATT-LVL").innerHTML = " Nivel de Batería: " + battery.level * 100 + "%";
  
}

battery.addEventListener('chargingtimechange', function(){
updateChargingInfo(); 
});
function updateChargingInfo(){
console.log("Battery charging time: "
             + battery.chargingTime + " seconds");
}

battery.addEventListener('dischargingtimechange', function(){
updateDischargingInfo();
});
function updateDischargingInfo(){
console.log("Battery discharging time: "
             + battery.dischargingTime + " seconds");
}

});
        
function getDateTime() {
    var now     = new Date(); 
    var year    = now.getFullYear();
    var month   = now.getMonth()+1; 
    var day     = now.getDate();
    var hour    = now.getHours();
    var minute  = now.getMinutes();
    var second  = now.getSeconds(); 
    if(month.toString().length == 1) {
         month = '0'+month;
    }
    if(day.toString().length == 1) {
         day = '0'+day;
    }   
    
    var dateTime = day+'/'+ month + '/' + year;   
     return dateTime;
}

// example usage: realtime clock


function getVideoCardInfo() {
const gl = document.createElement('canvas').getContext('webgl');
if (!gl) {
return {
  error: "no webgl",
};
}
const debugInfo = gl.getExtension('WEBGL_debug_renderer_info');
return debugInfo ? {
vendor: gl.getParameter(debugInfo.UNMASKED_VENDOR_WEBGL),
renderer:  gl.getParameter(debugInfo.UNMASKED_RENDERER_WEBGL),
} : {
error: "no WEBGL_debug_renderer_info",
};

}

console.log(getVideoCardInfo());

var gpu0 = JSON.stringify(getVideoCardInfo());
var gpu1 = JSON.stringify(getVideoCardInfo());

var n1 = gpu1.indexOf('","');//Indica hasta donde termina el Vendor
var gpuT1 = gpu0.substring(11, n1);//Indica que se empieza desde el caracter 11
var gpuT2 = gpu1.indexOf('{"vendor":"');//Este string tiene 11 caracteres, indica el tamaño de donde se empieza, omite el texto

var gpuT3 = gpu1.indexOf('","renderer":"');//Debe indicar el tamaño de los caracteres que se deben omitir
var gpuT4 = n1 + 14;
var gpuT5 = gpu1.substring(gpuT4, 100);
var gpuT6 = gpuT5.replace('"}','');

var new_str = gpu1.substring(0, gpu1.indexOf('","'));


