const baseUrl = document.querySelector('meta[name="base-url"]').content;

function openSettings(){
    dimScreenOn();
    document.getElementById("settings-menu").style.display = "flex";

    setTimeout(function(){
        document.getElementById("settings-menu").style.marginTop = "0px";z
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
    document.getElementById("dash-options").style.backgroundColor = "";
    $(".lateral-row").eq(0).css("background-color", "");

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
    document.getElementById("dash-options").setAttribute("color", color);
    document.getElementById("lateral").style.backgroundImage = "";

    if(color == "white"){
        document.getElementById("main-logo").setAttribute("src", `${baseUrl}/img/logo-black.png`);
    }else{
        document.getElementById("main-logo").setAttribute("src", `${baseUrl}/img/logo-short-white.png`);
    }
}

function selectActionColor(element){
    $(".color-pick").removeAttr("color-action");
    element.setAttribute("color-action", "selected");
    
    let colorAction = element.getAttribute("color");
    localStorage.setItem("actionColor", colorAction);

    $(".quick-item").attr("color", colorAction);
    $(".graph-bar-text").css("color", "#111");

    if(colorAction == "white"){
        $(".quick-item text").css("color","#333");
        $(".quick-item i").css("color","#333");
        $(".graph-bar-height").attr("color", "black");
    } else if(colorAction == "gray"){
        $(".quick-item text").css("color","#111");
        $(".quick-item i").css("color","#fff");
        $(".graph-bar-height").attr("color", colorAction);
    } else {
        $(".quick-item text").css("color","#fff");
        $(".quick-item i").css("color","#fff");
        $(".graph-bar-height").attr("color", colorAction);
    }
}

try {
    // Carga config guardada en localStorage
    if(localStorage.getItem("sideImage")){
        let imageN = localStorage.getItem("sideImage");

        document.getElementById("dash-options").style.backgroundColor = "";

        $(".thumb-options").eq(imageN-1).attr("image", "selected");
        $(".lateral-row").eq(0).css("background-color", "");
        $("#lateral-blur").css("backdrop-filter", `blur(4px)`);

        document.getElementById("lateral").style.backgroundImage = 
            `linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0.35)), url(${baseUrl}/img/menu/${imageN}.jpg)`;
    
    } else if(localStorage.getItem("sideColor")){

        let colorN = localStorage.getItem("sideColor");
        $(".lateral-row").eq(0).attr("color", colorN);
        document.getElementById("dash-options").setAttribute("color", colorN);

        if(colorN == "white"){
            document.getElementById("main-logo").setAttribute("src", `${baseUrl}/img/logo-black.png`);
        } else {
            document.getElementById("main-logo").setAttribute("src", `${baseUrl}/img/logo-short-white.png`);
        }

        document.querySelectorAll('#background-color-option-container > .color-pick-container .color-pick')
            .forEach(el => {
                if (el.getAttribute('color') === colorN) el.setAttribute('color-tile', 'selected');
            });

        $("#lateral-blur").css("backdrop-filter", `blur(4px)`);

    } else {
        localStorage.setItem("sideColor", "default");
        $(".lateral-row").eq(0).attr("color", "default");
        document.getElementById("dash-options").setAttribute("color", "default");
        document.querySelectorAll('#background-color-option-container > .color-pick-container .color-pick')[0].setAttribute("color-tile", "selected");
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
        } else if(colorA == "gray"){
            $(".quick-item text").css("color","#111");
            $(".quick-item i").css("color","#fff");
            $(".graph-bar-height").attr("color", colorA);
        } else {
            $(".quick-item text").css("color","#fff");
            $(".quick-item i").css("color","#fff");
            $(".graph-bar-height").attr("color", colorA);
        }

        document.querySelectorAll('#action-color-container .color-pick')
            .forEach(el => {
                if (el.getAttribute('color') === colorA) el.setAttribute('color-action', 'selected');
            });

    } else {
        localStorage.setItem("actionColor", "default");
        $(".color-pick").eq(0).attr("color", "default");
        $(".color-pick").eq(0).attr("color-action", "selected");
        document.getElementById("dash-options").setAttribute("color", "default");
        document.querySelectorAll('#background-color-option-container > .color-pick-container .color-pick')[0].setAttribute("color-tile", "selected");
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

    // Slider Blur Side
    (function () {
        const slider = document.getElementById('frac');
        const outPct = document.getElementById('val-pct');
        function update(v){
            const dec = Number(v).toFixed(2);
            const pct = Math.round(Number(v)*100);
            outPct.textContent = pct + '%';
            localStorage.setItem("sideBlur", dec);
            $("#lateral-blur").css("backdrop-filter", `blur(${dec}rem)`);
        }
        slider.addEventListener('input', (e) => update(e.target.value));
        slider.addEventListener('change', (e) => update(e.target.value));
        update(slider.value);
    })();

    // Slider Blur Home
    (function () {
        const slider2 = document.getElementById('frac2');
        const outPct2 = document.getElementById('val-pct2');
        function update(v){
            const dec2 = Number(v).toFixed(2);
            const pct2 = Math.round(Number(v)*100);
            outPct2.textContent = pct2 + '%';
            localStorage.setItem("homeBlur", dec2);
            $("#home-image-content").css("backdrop-filter", `blur(${dec2}rem)`);
        }
        slider2.addEventListener('input', (e) => update(e.target.value));
        slider2.addEventListener('change', (e) => update(e.target.value));
        update(slider2.value);
    })();

} catch (error){
    console.error(error);
}
