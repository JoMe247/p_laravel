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
let blurValue = "2";

// Verificar si el valor está en blurEffect
let blurConfig = localStorage.getItem(blurEffect);

function saveLocalValue(blurEffect, blurValue) {
    
    // Almacenar la imagen en localStorage
    localStorage.setItem(blurEffect, blurValue);

    // Almacenar URL Original de Pixabay
    // localStorage.setItem(imagePage, originalURl);

    // Mostrar la imagen en el div

}

saveLocalValue(blurEffect, blurValue);

if (!blurConfig) {

    // setTimeout(function(){
    //     // console.log(backImage)
    //     saveLocalValue(backImage, byImage, originalImage);
    // }, 1000);

    // // expDate = new Date("12 14 2023 11:44:30 GMT-0600");
    // expDate = new Date();
    // // console.log(backImage);
    // localStorage.setItem("expDate", expDate);
    
}else{
     setTimeout(function(){
    
        document.getElementById("home-image-content").style.backdropFilter = `blur(${localStorage.getItem(blurEffect)}px)`;
        
    }, 100);

}