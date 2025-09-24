let divCity = document.getElementById('city');
let divTemp = document.getElementById('temperature');
let divWeather = document.getElementById('weather');
let weatherImg = document.getElementById('weather-img');
let jeyson;

const apiKey = 'ebe0133254fc50d11cc677bf6c90d773';

// const URL_MAIN = 'https://api.openweathermap.org/data/2.5/weather';
// const apiKey = 'ebe0133254fc50d11cc677bf6c90d773';

// const UNITS = 'metric';



function loadUrl() {

  navigator.geolocation.getCurrentPosition((position) => {
    let lat = position.coords.latitude;
    let lon = position.coords.longitude;

    getTemperatura(lat,lon);

  });

};

loadUrl();

// async function fetchApi(urlWeather) {
//   response = await fetch(urlWeather);
//   let { main, name } = await response.json();
//   let temperature = (main.temp).toFixed(1);
//   let desc = (weather.description);
//   WEATHER.innerText = `${desc}:`;
//   CITY.innerText = `${name}:`;
//   TEMPERATURE.innerText = `${temperature} ºC`;
// }

function getTemperatura(lat,lon){

    // lat = 45.2966875;
    // lon = -121.7708928;

    var URL = "https://api.openweathermap.org/data/2.5/weather?lat="+lat+"&lon="+lon+"&units=metric&appid="+apiKey;

    weatherURL = fetch(URL).then(r => r.json())
    .then(function(data){
        jeyson = data;
        

        divCity.innerHTML = "<i class='bx bx-map'></i> " + data.name + ", " + data.sys.country;
        divTemp.innerText = Math.round(data.main.temp) + " °C";
        divWeather.innerText = data.weather[0].description;

        let iconURL = "img/weather/"+ data.weather[0].icon +".png";

        weatherImg.src = iconURL;

        // console.log(backImage);
    })
    .catch(e => console.log("Error"));

}