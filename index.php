<? include '../db_config.php'; //DB 연결 ?>

<?
$city = isset($_POST['city']) ? $_POST['city'] : null;
$lat = null;
$lon = null;

// 특정 지역 리스트
$city_list = '';

$sql = "SELECT * FROM korea_latlon WHERE city='' ";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_array($result)) {
    $city_list = $city_list. "<li><a href=\"index.php?id={$row['lat']}.{$row['lon']}\"> {$row['docity']} </a></li>";
}

// 클릭 시
if (isset($_GET['id'])) {
    $sql = "SELECT * FROM korea_latlon WHERE CONCAT(lat, '.', lon) = '{$_GET['id']}'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result);

    $docity = $row['docity'];
    $lat = $row['lat'];
    $lon = $row['lon'];
    
} elseif ($city) {
    // 검색된 경우
    $sql = "SELECT * FROM korea_latlon WHERE docity LIKE '%$city%'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_array($result);

    $docity = $row['docity'];
    $lat = $row['lat'];
    $lon = $row['lon'];
} else {
    // 검색되지 않은 경우, 여기서 현재 위치를 가져오는 기본값 설정
    $lat = 37.5519;
    $lon = 126.9918;
}


?>


<!DOCTYPE html>
<html lang="ko">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>날씨 검색</title>
    <link rel="stylesheet" type="text/css" href="CSS/index.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="//dapi.kakao.com/v2/maps/sdk.js?appkey=2de9bcff0bbb6dbf3bb37662fb4d66ed"></script>
</head>

<body>

    <header>
        <h2>한국 날씨 검색</h2>
        <form method="POST" id="header-form">
            <label for="city">도시를 입력하세요: </label>
            <input type="text" id="city" name="city" required>
            <button type="submit" name="get_weather" onclick="get_weather()">날씨 확인</button>
        </form>
    </header>

    <div id="wrap">
        <!--카카오맵-->
        <div id="map"></div>

        <!--특정 지역 날씨정보-->
        
        <div id="click_weather">
            <form><button type="submit" name="current_weather" onClick="current_weather()">현재 위치</button></form>
            <ul><?=$city_list?></ul>
        </div>
    </div>

    <!--날씨정보-->
    <div id="weather_container">
        <h3 id="w_city"></h3>
        <img id="w_img">
        <h1 id="w_temp"></h1>
        <p id="w_main"></p>
        <p id="w_tempM"></p>
    </div>

    

    <script>
    var lat = <?= $lat; ?>;
    var lon = <?= $lon; ?>;
    var docity = <?= json_encode($docity); ?>; // 문자열로 인코딩

    // 날씨확인 클릭시 실행될 함수
    function get_weather() {    
        const map_container = $('#map')[0];
        const locposition = new kakao.maps.LatLng(lat, lon);
        const options = {
            center: locposition,
            level: 10
        };
        const map = new kakao.maps.Map(map_container, options);
        const marker = new kakao.maps.Marker({
            position: locposition
        });
        marker.setMap(map);

        const Weather_API_KEY = "ff12c486f1dbf5e75139f5217b0d7d8e";
        const url = `https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${Weather_API_KEY}&units=metric`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                const city = docity || data.name;
                const main = data.weather[0].main;
                const temp = Math.round(data.main.temp);
                const temp_min = Math.round(data.main.temp_min);
                const temp_max = Math.round(data.main.temp_max);

                $("#w_city").text(`${city}`);
                $("#w_img").attr("src", "http://openweathermap.org/img/w/" + data.weather[0].icon + ".png");
                $("#w_temp").text(`${temp}°C`);
                $("#w_main").text(`${main}`);
                $("#w_tempM").text(`최고 ${temp_max}°C / 최저 ${temp_min}°C`);
            });     
}


var map;
var marker;

// 현재 위치인지 검색 위치인지 확인하여 지도 및 날씨 표시
function current_weather() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function (position) {
            lat = position.coords.latitude,
            lon = position.coords.longitude;

            const locposition = new kakao.maps.LatLng(lat, lon);
            showMapAndWeather(locposition);
        })
    } else {
        const locposition = new kakao.maps.LatLng(37.5519, 126.9918);
        showMapAndWeather(locposition);
        $('#weather_container').append('<h3><br>위치정보를 찾을 수 없습니다. 검색 혹은 지도를 클릭하여 이용해주세요.</h3>');
    }
}
    

    // 지도 및 날씨 정보 표시 함수
    function showMapAndWeather(locposition) {
        const map_container = $('#map')[0];
        const options = {
            center: locposition,
            level: 10
        };
        map = new kakao.maps.Map(map_container, options);

        // 기존 마커가 있으면 제거
        if (marker) {
            marker.setMap(null);
        }

        marker = new kakao.maps.Marker({
            position: locposition
        });
        marker.setMap(map);

        const Weather_API_KEY = "ff12c486f1dbf5e75139f5217b0d7d8e";
        const url = `https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${Weather_API_KEY}&units=metric`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                const city = docity || data.name; // 검색된 도시가 있다면 검색된 도시 사용, 아니면 날씨 API의 도시 사용
                const main = data.weather[0].main;
                const temp = Math.round(data.main.temp);
                const temp_min = Math.round(data.main.temp_min);
                const temp_max = Math.round(data.main.temp_max);

                $("#w_city").text(`${city}`);
                $("#w_img").attr("src", "http://openweathermap.org/img/w/" + data.weather[0].icon + ".png");
                $("#w_temp").text(`${temp}°C`);
                $("#w_main").text(`${main}`);
                $("#w_tempM").text(`최고 ${temp_max}°C / 최저 ${temp_min}°C`);
            });
    }

    // 검색 여부에 따라 다르게 처리
    if (<?php echo isset($_GET['id']) ? 'true' : 'false'; ?>) {
        // GET 변수 'id'가 있는 경우
        get_weather();
    } else if (<?php echo $city ? 'true' : 'false'; ?>) {
        // 검색된 경우
        get_weather();
    } else {
        // 검색되지 않은 경우
        current_weather();
    }

    </script>

</body>
</html>