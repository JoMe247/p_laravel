function openSettings(){
    dimScreenOn();
    document.getElementById("settings-menu").style.display = "flex";

    setTimeout(function(){
        document.getElementById("settings-menu").style.marginTop = "0px";
    }, 10)

    document.querySelector("#home-image").style.backgroundImage;
}

function closeSettings(){
    dimScreenOff();
    document.getElementById("settings-menu").style.display = "";

    setTimeout(function(){
        document.getElementById("settings-menu").style.marginTop = "";
    }, 10)
}


//Blur Value Settings

let blurEffect = 'blurEffect';
let blurValue = "1";

// Verificar si el valor está en blurEffect
let blurConfig = localStorage.getItem(blurEffect);

function saveLocalValue(blurEffect, blurValue) {
    localStorage.setItem(blurEffect, blurValue);
}

saveLocalValue(blurEffect, blurValue);

if (!blurConfig) {

    
}else{
     setTimeout(function(){
    
        document.getElementById("home-image-content").style.backdropFilter = `blur(${localStorage.getItem(blurEffect)}px)`;
        
    }, 100);

}

let imageN;

//Sleecionar Imagen Side y Guardar sideImage en localstorage
function selectImage(n){
    // $(".your-class").attr("attribute-name", "attribute-value");
    $(".color-pick").removeAttr("color-tile");
    $(".thumb-options").removeAttr("image");
    $("#dash-options").removeAttr("color");
     document.getElementById("main-logo").setAttribute("src", "img/logo-short-white.png");
    // Almacenar la imagen en localStorage
    localStorage.setItem("sideImage", n);

    $(".thumb-options").eq(n-1).attr("image", "selected");

    document.getElementById("dash-options").style.backgroundColor = "unset";

    $(".lateral-row").eq(0).css("background-color", "unset");

    document.getElementById("lateral").style.backgroundImage = `linear-gradient(to bottom, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.35)), url(img/menu/${n}.jpg)`;
}

//Seleccionar Color Side y Guardar sideColor en localStorage
function selectColor(element){

    $(".color-pick").removeAttr("color-tile");
    element.setAttribute("color-tile", "selected");
    $(".thumb-options").removeAttr("image");
    let color = element.getAttribute("color");

    if(localStorage.getItem("sideImage")){
        localStorage.removeItem('sideImage');
    } 

    localStorage.setItem("sideColor", color);
    // console.log(color);

    $(".lateral-row").eq(0).css("background-color", "");
    document.getElementById("dash-options").style.backgroundColor = "";

    $(".lateral-row").eq(0).attr("color", color);
    document.getElementById("dash-options").setAttribute("color", color)
    document.getElementById("lateral").style.backgroundImage = "";

    if(color == "white"){
        document.getElementById("main-logo").setAttribute("src", "img/logo-black.png")
    }else{
        document.getElementById("main-logo").setAttribute("src", "img/logo-short-white.png")
    }
}

//Función que Carga la config guardada en localStorage
if(localStorage.getItem("sideImage")){
    let imageN = localStorage.getItem("sideImage");

    document.getElementById("dash-options").style.backgroundColor = "unset";

    $(".thumb-options").eq(imageN-1).attr("image", "selected");

    $(".lateral-row").eq(0).css("background-color", "unset");
    $("#lateral-blur").css("backdrop-filter", `blur(4px)`);

    document.getElementById("lateral").style.backgroundImage = `linear-gradient(to bottom, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.35)), url(img/menu/${imageN}.jpg)`;
   
}else{

    if(localStorage.getItem("sideColor")){

        let colorN = localStorage.getItem("sideColor");
        $(".lateral-row").eq(0).attr("color", colorN);
        document.getElementById("dash-options").setAttribute("color", colorN);

        if(colorN == "white"){
            document.getElementById("main-logo").setAttribute("src", "img/logo-black.png")
        }else{
            document.getElementById("main-logo").setAttribute("src", "img/logo-short-white.png")
        }

        document.querySelectorAll('#background-color-option-container > .color-pick-container .color-pick')
        .forEach(el => {
            if (el.getAttribute('color') === colorN) {
            el.setAttribute('color-tile', 'selected');
            }
        });

    }else{
        console.log("no existe");
        localStorage.setItem("sideColor", "default");
        $(".lateral-row").eq(0).attr("color", "default");
        document.getElementById("dash-options").setAttribute("color", "default");
        document.querySelectorAll('#background-color-option-container > .color-pick-container .color-pick')[0].setAttribute("color-tile", "selected")
    }
    $("#lateral-blur").css("backdrop-filter", `blur(4px)`);
}

if (localStorage.getItem("sideBlur")) {

    let blurValue = localStorage.getItem("sideBlur");
    $("#lateral-blur").css("backdrop-filter", `blur(${blurValue}rem)`);
    document.querySelector("#frac").value = blurValue;
}else{
     
//   $("#lateral-blur").css("backdrop-filter", `blur(${}rem)`);

}

//Slider Blur Script

(function () {
    const slider = document.getElementById('frac');
    const outDec = document.getElementById('val-dec');
    const outPct = document.getElementById('val-pct');

    function update(v) {
      // Normaliza a 2 decimales
      const dec = Number(v).toFixed(2);
      const pct = Math.round(Number(v) * 100);
    //   outDec.textContent = dec;
      outPct.textContent = pct + '%';
    //   console.log('slider value:', Number(dec)); // <- aquí el console.log en cada movimiento
      localStorage.setItem("sideBlur", dec);
      $("#lateral-blur").css("backdrop-filter", `blur(${dec}rem)`);
    }

    // Dispara en tiempo real mientras se arrastra
    slider.addEventListener('input', (e) => update(e.target.value));
    // (Opcional) también al soltar
    slider.addEventListener('change', (e) => update(e.target.value));


    
    // Init
    update(slider.value);
  })();