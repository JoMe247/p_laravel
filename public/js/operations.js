const baseUrl = document.querySelector('meta[name="base-url"]').content;

// --- Overlay para Settings ---
function dimScreenOn() {
    let overlay = document.getElementById('dim-settings-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'dim-settings-overlay';
        overlay.style.position = 'fixed';
        overlay.style.top = 0;
        overlay.style.left = 0;
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
        overlay.style.zIndex = 5;
        overlay.style.display = 'block';
        document.body.appendChild(overlay);
    } else {
        overlay.style.display = 'block';
    }
}

function dimScreenOff() {
    const overlay = document.getElementById('dim-settings-overlay');
    if (overlay) overlay.style.display = 'none';
}

function openSettings(){
    dimScreenOn();
    document.getElementById("settings-menu").style.display = "flex";

    setTimeout(function(){
        document.getElementById("settings-menu").style.marginTop = "0px";
    }, 10);

    document.querySelector("#home-image").style.backgroundImage;
}

function closeSettings(){
    dimScreenOff();
    document.getElementById("settings-menu").style.display = "";

    setTimeout(function(){
        document.getElementById("settings-menu").style.marginTop = "";
    }, 10);
}

let imageN;

// Seleccionar Imagen Side y Guardar sideImage en localStorage
function selectImage(n){
    $(".color-pick").removeAttr("color-tile");
    $(".thumb-options").removeAttr("image");
    $("#dash-options").removeAttr("color");

    document.getElementById("main-logo").setAttribute("src", `${baseUrl}/img/logo-short-white.png`);
    localStorage.setItem("sideImage", n);
    $(".thumb-options").eq(n-1).attr("image", "selected");
    document.getElementById("dash-options").style.backgroundColor = "unset";
    $(".lateral-row").eq(0).css("background-color", "unset");
    document.getElementById("lateral").style.backgroundImage = 
        `linear-gradient(to bottom, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.35)), url(${baseUrl}/img/menu/${n}.jpg)`;
}

// Seleccionar Color Side y Guardar sideColor en localStorage
function selectColor(element){
    $(".color-pick").removeAttr("color-tile");
    element.setAttribute("color-tile", "selected");
    $(".thumb-options").removeAttr("image");
    let color = element.getAttribute("color");

    if(localStorage.getItem("sideImage")){
        localStorage.removeItem('sideImage');
    } 

    localStorage.setItem("sideColor", color);
    $(".lateral-row").eq(0).css("background-color", "");
    document.getElementById("dash-options").style.backgroundColor = "";
    $(".lateral-row").eq(0).attr("color", color);
    document.getElementById("dash-options").setAttribute("color", color)
    document.getElementById("lateral").style.backgroundImage = "";

    if(color == "white"){
        document.getElementById("main-logo").setAttribute("src", `${baseUrl}/img/logo-black.png`);
    }else{
        document.getElementById("main-logo").setAttribute("src", `${baseUrl}/img/logo-short-white.png`);
    }
}

