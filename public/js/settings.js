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

function selectActionColor(element){

    $(".color-pick").removeAttr("color-action");
    element.setAttribute("color-action", "selected");
    
    let colorAction = element.getAttribute("color");

    localStorage.setItem("actionColor", colorAction);
    // console.log(color);

    $(".quick-item").attr("color", colorAction);
    $(".graph-bar-text").css("color", "#111");

    if(colorAction == "white"){
        $(".quick-item text").css("color","#333");
        $(".quick-item i").css("color","#333");
        $(".graph-bar-height").attr("color", "black");
    }
    else if(colorAction == "gray"){
        $(".quick-item text").css("color","#111");
        $(".quick-item i").css("color","#fff");
        $(".graph-bar-height").attr("color", colorAction);
    }else{
        $(".quick-item text").css("color","#fff");
        $(".quick-item i").css("color","#fff");
        $(".graph-bar-height").attr("color", colorAction); 
    }
}


try {

    //Función que Carga la config guardada en localStorage
    if(localStorage.getItem("sideImage")){
        let imageN = localStorage.getItem("sideImage");

        document.getElementById("dash-options").style.backgroundColor = "unset";

        $(".thumb-options").eq(imageN-1).attr("image", "selected");

        $(".lateral-row").eq(0).css("background-color", "unset");
        $("#lateral-blur").css("backdrop-filter", `blur(4px)`);

<<<<<<< Updated upstream
        document.getElementById("lateral").style.backgroundImage = `linear-gradient(to bottom, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.35)), url(img/menu/${imageN}.jpg)`;
=======
        document.getElementById("lateral").style.backgroundImage = `linear-gradient(to bottom, rgba(0, 0, 0, 0.3), rgba(0, 0, 0, 0.35)), url(../img/menu/${imageN}.jpg)`;
        // console.log("aqui");
>>>>>>> Stashed changes
    
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

    if(localStorage.getItem("actionColor")){

        let colorA = localStorage.getItem("actionColor");
       
        $(".quick-item").attr("color", colorA);
        
        $(".graph-bar-text").css("color", "#111");

        if(colorA == "white"){
            $(".quick-item text").css("color","#333");
            $(".quick-item i").css("color","#333");
            $(".graph-bar-height").attr("color", "black");
        }
        else if(colorA == "gray"){
            $(".quick-item text").css("color","#111");
            $(".quick-item i").css("color","#fff");
            $(".graph-bar-height").attr("color", colorA);
        }else{
            $(".quick-item text").css("color","#fff");
            $(".quick-item i").css("color","#fff");
            $(".graph-bar-height").attr("color", colorA);
        }

        document.querySelectorAll('#action-color-container .color-pick')
            .forEach(el => {
                if (el.getAttribute('color') === colorA) {
                  el.setAttribute('color-action', 'selected');
                }
        });

        

    }else{
        console.log("no existe");
        localStorage.setItem("actionColor", "default");
        $(".color-pick").eq(0).attr("color", "default");
        $(".color-pick").eq(0).attr("color-action", "selected");
        document.getElementById("dash-options").setAttribute("color", "default");
        document.querySelectorAll('#background-color-option-container > .color-pick-container .color-pick')[0].setAttribute("color-tile", "selected")
    }

    if (localStorage.getItem("sideBlur")) {

        let blurValue = localStorage.getItem("sideBlur");
        $("#lateral-blur").css("backdrop-filter", `blur(${blurValue}rem)`);
        document.querySelector("#frac").value = blurValue;
    }


    if (localStorage.getItem("homeBlur")) {

        let homeBlur = localStorage.getItem("homeBlur");
        $("#home-image-content").css("backdrop-filter", `blur(${homeBlur}rem)`);
        document.querySelector("#frac2").value = homeBlur;
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

    (function () {
        const slider2 = document.getElementById('frac2');
        const outDec2 = document.getElementById('val-dec2');
        const outPct2 = document.getElementById('val-pct2');

        function update(v) {
        // Normaliza a 2 decimales
        const dec2 = Number(v).toFixed(2);
        const pct2 = Math.round(Number(v) * 100);
        //   outDec.textContent = dec;
        outPct2.textContent = pct2 + '%';
        //   console.log('slider value:', Number(dec)); // <- aquí el console.log en cada movimiento
        localStorage.setItem("homeBlur", dec2);
        $("#home-image-content").css("backdrop-filter", `blur(${dec2}rem)`);
        }

        // Dispara en tiempo real mientras se arrastra
        slider2.addEventListener('input', (e) => update(e.target.value));
        // (Opcional) también al soltar
        slider2.addEventListener('change', (e) => update(e.target.value));
        
        // Init
        update(slider2.value);
    })();
    
} catch (error) {
    
}
