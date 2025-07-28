function overFlowH(){//Función que deshabilita el overflow/scroll cuando se abre el menu y añade la función de volver a deslizar a nuestro label/menu hamburguesa
    document.body.style.overflow = "hidden";
    document.getElementsByClassName("menu-icon")[0].setAttribute("onclick", "overFlowV()");

    document.getElementById("bottom-menu-close").style.borderTop = "none";
    document.getElementById("bottom-menu-close").style.backgroundColor = "transparent";

    document.querySelectorAll("#bottom-menu-close img")[0].style.display = "none";


    document.querySelectorAll("#bottom-menu-close label div")[0].style.backgroundColor = "#fff";
    document.querySelectorAll("#bottom-menu-close label div")[1].style.backgroundColor = "#fff";
    document.querySelectorAll("#bottom-menu-close label div")[2].style.backgroundColor = "#fff";
    document.getElementById("bottom-menu-close").style.color = "#fff";
}
function overFlowV(){//Función que lo habilita (overflow/scroll) y al reves, agrega la otra función para que cuando se abra el menu se vuelva a poder deslizar
    document.body.style.overflow = "";
    document.getElementsByClassName("menu-icon")[0].setAttribute("onclick", "overFlowH()");

    document.querySelectorAll("#bottom-menu-close img")[0].style.display = "";

    document.querySelectorAll("#bottom-menu-close label div")[0].style.backgroundColor = "#111";
    document.querySelectorAll("#bottom-menu-close label div")[1].style.backgroundColor = "#111";
    document.querySelectorAll("#bottom-menu-close label div")[2].style.backgroundColor = "#111";

    document.getElementById("bottom-menu-close").style.borderTop = "1px solid #ebebeb";
    document.getElementById("bottom-menu-close").style.backgroundColor = "#fff";
    document.getElementById("bottom-menu-close").style.color = "#000";
}