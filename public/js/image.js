var imagenURL;
let backImage;
let byImage;
let expDate;
let originalImage;
let randomIndex;

function getImage(){
    var API_KEY = '14157943-b86ebc22bc54ac787c16053e2';
    var URL = "https://pixabay.com/api/?key="+API_KEY+"&q=paisaje&per_page=200&image_type=photo";

    randomIndex = Math.floor(Math.random() * 200) + 1;
    
    imagenURL = fetch(URL).then(r => r.json())
    .then(function(data){
        // console.log(data.hits[0].largeImageURL)
        backImage = data.hits[randomIndex].largeImageURL;
        byImageImage = data.hits[randomIndex].user;
        originalImage = data.hits[randomIndex].pageURL;
    })
    .catch(e => console.log("Booo"));

}

getImage();

let localStorageKey = 'homeImage';
let imagePage = 'pageImage';
// Verificar si la imagen ya está en localStorage
let storedImage = localStorage.getItem(localStorageKey);

function guardarImagenEnLocalStorage(imagenFondo, byUser, originalURl) {
    // console.log(imagenFondo);
    // Almacenar la imagen en localStorage
    localStorage.setItem(localStorageKey, imagenFondo);

    // Almacenar URL Original de Pixabay
    localStorage.setItem(imagePage, originalURl);

    // Mostrar la imagen en el div
    document.getElementById('home-image').style.backgroundImage = `url(${imagenFondo})`;
    document.getElementById('view-full-picture').setAttribute('onclick',`window.open('${originalURl}')`);

}


function resetImage(){
    localStorage.removeItem('homeImage');
    randomIndex = Math.floor(Math.random() * 200) + 1;
    getImage();
    guardarImagenEnLocalStorage(backImage, byImage, originalImage);
}

if (!storedImage) {

    setTimeout(function(){
        // console.log(backImage)
        guardarImagenEnLocalStorage(backImage, byImage, originalImage);
    }, 1000);

    // expDate = new Date("12 14 2023 11:44:30 GMT-0600");
    expDate = new Date();
    // console.log(backImage);
    localStorage.setItem("expDate", expDate);
    
}else{
    
        imageExp();
}

function imageExp() {

    var storedDate = localStorage.getItem("expDate");
    var now = new Date(storedDate);//Stored Date
    // var now = new Date();
    var endDate = new Date(); // Now

    // console.log(now);
    // console.log(endDate);


    var diff = endDate - now; 

    var hours   = Math.floor(diff / 3.6e6);
    var minutes = Math.floor((diff % 3.6e6) / 6e4);
    var seconds = Math.floor((diff % 6e4) / 1000);
    // console.log('Hours Between: \n' + hours + '\n Minutes Between: ' +  minutes);

    var storedUndefined = localStorage.getItem("homeImage");

    if (storedUndefined == "undefined"){
        console.log("NewPic due to undefined");
        
        localStorage.removeItem('homeImage');
        randomIndex = Math.floor(Math.random() * 200) + 1;
        getImage();
        setTimeout(function(){
            // console.log(backImage)
            guardarImagenEnLocalStorage(backImage, byImage, originalImage);
        }, 1000);

        // expDate = new Date("12 14 2023 11:44:30 GMT-0600");
        expDate = new Date();
        // console.log(backImage);
        localStorage.setItem("expDate", expDate);

    }else if(hours >= 3){
        console.log("NewPic");
        
        localStorage.removeItem('homeImage');
        randomIndex = Math.floor(Math.random() * 200) + 1;
        getImage();
        setTimeout(function(){
            // console.log(backImage)
            guardarImagenEnLocalStorage(backImage, byImage, originalImage);
        }, 1000);

        // expDate = new Date("12 14 2023 11:44:30 GMT-0600");
        expDate = new Date();
        // console.log(backImage);
        localStorage.setItem("expDate", expDate);

    }else{
        // console.log("SamePic");

        // console.log("La imagen ya está registrada");
        document.getElementById('home-image').style.backgroundImage = `url(${localStorage.getItem(localStorageKey)})`;
        document.getElementById('view-full-picture').setAttribute('onclick',`window.open('${localStorage.getItem(imagePage)}')`);
    }
}

function getEnglishDate() {
    const daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
  
    const currentDate = new Date();
    const dayOfWeek = daysOfWeek[currentDate.getDay()];
    const day = currentDate.getDate();
    const month = months[currentDate.getMonth()];
    const year = currentDate.getFullYear();
  
    return `${dayOfWeek} ${day} ${month} ${year}`;
  }
  
  // Example of usage
  const englishDate = getEnglishDate();
//   console.log(englishDate);

  document.getElementById("welcome-date").innerText = englishDate;

  