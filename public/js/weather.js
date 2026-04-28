document.addEventListener('DOMContentLoaded', function () {
    let divCity = document.getElementById('city');
    let divTemp = document.getElementById('temperature');
    let divWeather = document.getElementById('weather');
    let weatherImg = document.getElementById('weather-img');
    let weatherInfo = document.getElementById('weather-info');

    const apiKey = 'ebe0133254fc50d11cc677bf6c90d773';

    if (!divCity || !divTemp || !divWeather || !weatherImg || !weatherInfo) return;

    function showWeatherInfo() {
        weatherInfo.style.display = '';
        weatherInfo.style.visibility = 'visible';
        weatherInfo.style.opacity = '1';
    }

    function setLoadingWeather() {
        showWeatherInfo();
        divCity.innerHTML = "<i class='bx bx-map'></i> Loading location...";
        divTemp.innerText = "-- °C";
        divWeather.innerText = "Loading weather";
        weatherImg.src = "";
    }

    function setWeatherError(message = 'Weather unavailable') {
        showWeatherInfo();
        divCity.innerHTML = "<i class='bx bx-map'></i> Approximate location unavailable";
        divTemp.innerText = "-- °C";
        divWeather.innerText = message;
        weatherImg.src = "";
    }

    async function getTemperatura(lat, lon, fallbackCity = '') {
        showWeatherInfo();

        const URL = "https://api.openweathermap.org/data/2.5/weather?lat=" +
            lat +
            "&lon=" +
            lon +
            "&units=metric&appid=" +
            apiKey;

        const response = await fetch(URL);

        if (!response.ok) {
            throw new Error('OpenWeather request failed');
        }

        const data = await response.json();

        if (!data.main || !data.weather || !data.weather[0]) {
            throw new Error('Invalid OpenWeather data');
        }

        const cityName = data.name || fallbackCity || 'Approximate location';
        const country = data.sys && data.sys.country ? data.sys.country : '';

        divCity.innerHTML = "<i class='bx bx-map'></i> " + cityName + (country ? ", " + country : "");
        divTemp.innerText = Math.round(data.main.temp) + " °C";
        divWeather.innerText = data.weather[0].description;

        let iconURL = "img/weather/" + data.weather[0].icon + ".png";
        weatherImg.src = iconURL;
    }

    async function getLocationFromIpWhoIs() {
        const response = await fetch('https://ipwho.is/');

        if (!response.ok) {
            throw new Error('ipwho.is failed');
        }

        const data = await response.json();

        if (!data.success || !data.latitude || !data.longitude) {
            throw new Error('ipwho.is location unavailable');
        }

        return {
            latitude: data.latitude,
            longitude: data.longitude,
            city: [data.city, data.region].filter(Boolean).join(', ') || 'Approximate location'
        };
    }

    async function getLocationFromIpApiCo() {
        const response = await fetch('https://ipapi.co/json/');

        if (!response.ok) {
            throw new Error('ipapi.co failed');
        }

        const data = await response.json();

        if (!data.latitude || !data.longitude) {
            throw new Error('ipapi.co location unavailable');
        }

        return {
            latitude: data.latitude,
            longitude: data.longitude,
            city: [data.city, data.region].filter(Boolean).join(', ') || 'Approximate location'
        };
    }

    async function getLocationFromIpInfo() {
        const response = await fetch('https://ipinfo.io/json');

        if (!response.ok) {
            throw new Error('ipinfo failed');
        }

        const data = await response.json();

        if (!data.loc) {
            throw new Error('ipinfo location unavailable');
        }

        const parts = data.loc.split(',');

        if (parts.length !== 2) {
            throw new Error('ipinfo coordinates invalid');
        }

        return {
            latitude: parts[0],
            longitude: parts[1],
            city: [data.city, data.region].filter(Boolean).join(', ') || 'Approximate location'
        };
    }

    async function loadWeatherByPublicIp() {
        const providers = [
            getLocationFromIpWhoIs,
            getLocationFromIpApiCo,
            getLocationFromIpInfo
        ];

        let lastError = null;

        for (const provider of providers) {
            try {
                const location = await provider();

                await getTemperatura(
                    location.latitude,
                    location.longitude,
                    location.city
                );

                return;
            } catch (error) {
                lastError = error;
                console.warn('IP fallback failed:', error.message);
            }
        }

        throw lastError || new Error('All IP providers failed');
    }

    function loadUrl() {
        setLoadingWeather();

        if (!navigator.geolocation) {
            loadWeatherByPublicIp().catch(() => {
                setWeatherError('Approximate weather unavailable');
            });

            return;
        }

        navigator.geolocation.getCurrentPosition(
            function (position) {
                let lat = position.coords.latitude;
                let lon = position.coords.longitude;

                getTemperatura(lat, lon).catch(() => {
                    loadWeatherByPublicIp().catch(() => {
                        setWeatherError('Weather unavailable');
                    });
                });
            },
            function () {
                loadWeatherByPublicIp().catch(() => {
                    setWeatherError('Approximate weather unavailable');
                });
            },
            {
                enableHighAccuracy: false,
                timeout: 8000,
                maximumAge: 300000
            }
        );
    }

    loadUrl();
});