<?php
$apiKey = "8c6ddc4dbcf5821d4d17d4ffe5fdf14e";

$weatherData = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cityInput = $_POST["city"];

    $cityId = is_numeric($cityInput) ? $cityInput : null;
    $cityName = !$cityId ? $cityInput : null;

    if ($cityId || $cityName) {
        $googleApiUrl = $cityId ? 
            "http://api.openweathermap.org/data/2.5/weather?id=" . $cityId . "&lang=en&units=metric&APPID=" . $apiKey :
            "http://api.openweathermap.org/data/2.5/weather?q=" . $cityName . "&lang=en&units=metric&APPID=" . $apiKey;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $googleApiUrl);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $weatherData = json_decode($response);

        // Set the time zone based on the city's coordinates
        if ($weatherData && isset($weatherData->coord->lat) && isset($weatherData->coord->lon)) {
            $timezone = get_nearest_timezone($weatherData->coord->lat, $weatherData->coord->lon);
            date_default_timezone_set($timezone);
        }
    }
}

function get_nearest_timezone($cur_lat, $cur_long, $country_code = '') {
    $timezone_ids = ($country_code) ? DateTimeZone::listIdentifiers(DateTimeZone::PER_COUNTRY, $country_code)
                                    : DateTimeZone::listIdentifiers();

    if ($timezone_ids && is_array($timezone_ids) && isset($timezone_ids[0])) {
        $time_zone = '';
        $tz_distance = 0;

        foreach ($timezone_ids as $timezone_id) {
            $timezone = new DateTimeZone($timezone_id);
            $location = $timezone->getLocation();
            $tz_lat = $location['latitude'];
            $tz_long = $location['longitude'];

            $theta = $cur_long - $tz_long;
            $distance = (sin(deg2rad($cur_lat)) * sin(deg2rad($tz_lat))) 
                      + (cos(deg2rad($cur_lat)) * cos(deg2rad($tz_lat)) * cos(deg2rad($theta)));
            $distance = acos($distance);
            $distance = abs(rad2deg($distance));

            if (!$time_zone || $tz_distance > $distance) {
                $time_zone = $timezone_id;
                $tz_distance = $distance;
            }
        }
        return $time_zone;
    }
    return 'unknown';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weather Forecast using OpenWeatherMap with PHP</title>
    <style>
        body {
            font-family: Arial;
            font-size: 0.95em;
            color: #929292;
        }
        .report-container {
            border: #E0E0E0 1px solid;
            padding: 20px 40px 40px 40px;
            border-radius: 2px;
            width: 550px;
            margin: 20px auto;
        }
        .weather-icon {
            vertical-align: middle;
            margin-right: 20px;
        }
        .weather-forecast {
            color: #212121;
            font-size: 1.2em;
            font-weight: bold;
            margin: 20px 0px;
        }
        span.min-temperature {
            margin-left: 15px;
            color: #929292;
        }
        .time {
            line-height: 25px;
        }
    </style>
</head>
<body>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="city">Enter City Name or ID:</label>
        <input type="text" id="city" name="city">
        <button type="submit">Get Weather</button>
    </form>

    <?php if (!empty($weatherData)): ?>
    <div class="report-container">
        <h2><?php echo $weatherData->name; ?> Weather Status</h2>
        <div class="time">
            <div><?php echo date("l g:i a", time()); ?></div>
            <div><?php echo date("jS F, Y", time()); ?></div>
            <div><?php echo ucwords($weatherData->weather[0]->description); ?></div>
        </div>
        <div class="weather-forecast">
            <img src="http://openweathermap.org/img/w/<?php echo $weatherData->weather[0]->icon; ?>.png" class="weather-icon" />
            <?php echo $weatherData->main->temp_max; ?>&deg;C
            <span class="min-temperature"><?php echo $weatherData->main->temp_min; ?>&deg;C</span>
        </div>
        <div class="time">
            <div>Humidity: <?php echo $weatherData->main->humidity; ?> %</div>
            <div>Wind: <?php echo $weatherData->wind->speed; ?> km/h</div>
        </div>
    </div>
    <?php endif; ?>
</body>
</html>
