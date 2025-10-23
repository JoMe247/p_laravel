function dimScreenOn(){
    document.getElementById("dim-screen").style.display = "flex";

    setTimeout(function(){
        document.getElementById("dim-screen").style.opacity = "1";
    }, 10)
}

function dimScreenOff(){
    document.getElementById("dim-screen").style.display = "";
    document.getElementById("dim-screen").style.opacity = "";
}

function confirmBoxOn(title, desc, funct){
    dimScreenOn();
    document.getElementsByClassName("window-confirm")[0].style.display = "flex";

    setTimeout(function(){
        document.getElementsByClassName("window-confirm")[0].style.marginTop = "0px";
    }, 10)

    document.getElementsByClassName("confirm-window-title")[0].innerText = title;
    document.getElementsByClassName("confirm-window-description")[0].innerText = desc;
    document.getElementsByClassName("confirm-window-confirm-btn")[0].setAttribute("onclick", funct);
}

function confirmBoxOff(){
    dimScreenOff()
    document.getElementsByClassName("window-confirm")[0].style.display = "";
    document.getElementsByClassName("window-confirm")[0].style.marginTop = "";
    document.getElementsByClassName("confirm-window-title")[0].innerText = "";
    document.getElementsByClassName("confirm-window-description")[0].innerText = "";
    document.getElementsByClassName("confirm-window-confirm-btn")[0].removeAttribute("onclick");
}

function logOut(){
    window.location.href = 'operations/sess/logout.php';
}