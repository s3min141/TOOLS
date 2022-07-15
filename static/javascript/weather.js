const API_KEY = "02da3867254af39271dad6ad89fa9969";
const WEATHER_API = "https://api.openweathermap.org/data/2.5/weather?";
const weatherIcon = {
  "01": "fas fa-sun",
  "02": "fas fa-cloud-sun",
  "03": "fas fa-cloud",
  "04": "fas fa-cloud-meatball",
  "09": "fas fa-cloud-sun-rain",
  "10": "fas fa-cloud-showers-heavy",
  "11": "fas fa-poo-storm",
  "13": "far fa-snowflake",
  "50": "fas fa-smog",
};

function getTime() {
  var today = new Date();
  var hour = today.getHours() == 0 ? 12 : today.getHours();
  var min = today.getMinutes();
  var amPm = today.getHours() < 12 ? "AM" : "PM";

  hour = (hour < 10) ? `0${hour}` : hour;
  min = (min < 10) ? `0${min}` : min;
  return `${amPm} ${hour}:${min}`;
}

function getRemainTime(targetTime) {
  var diff = (targetTime * 1000) - new Date().getTime();
  var elapsedHour = diff / 1000 / 60 / 60;
  elapsedHour = (elapsedHour < 0) ? 0 : elapsedHour;
  return elapsedHour;
}

function getWeather(coords) {
  var jsonData = new Object();

  return new Promise(function (resolve, reject) {
    $.get(`${WEATHER_API}lat=${coords.lat}&lon=${coords.lng}&appid=${API_KEY}&units=metric`, function (json) {
      jsonData.nowtime = getTime();
      jsonData.location = json.name;
      jsonData.temperature = Math.round(json.main.temp);
      jsonData.humidity = json.main.humidity;
      jsonData.weather = json.weather[0].main;
      jsonData.wind = Math.round(json.wind.speed);
      jsonData.icon = json.weather[0].icon.substr(0, 2);
      jsonData.icon = weatherIcon[jsonData.icon];
      jsonData.sunset = getRemainTime(json.sys.sunset).toFixed(1);

      resolve(jsonData);
    });
  });
}

function handleGeoSuccess(position) {
  const lat = position.coords.latitude;
  const lng = position.coords.longitude;
  const coords = {
    lat,
    lng,
  };
  localStorage.setItem("coords", JSON.stringify(coords));
  return coords;
}

function handleGeoFailure() {
  console.log("no location");
}

function getGeoInformation() {
  const currentCoords = localStorage.getItem("coords");
  if (currentCoords !== null) {
    const parsedCoords = JSON.parse(currentCoords);
    return parsedCoords;
    return;
  } else {
    navigator.geolocation.getCurrentPosition(
      handleGeoSuccess,
      handleGeoFailure
    );
  }
}
