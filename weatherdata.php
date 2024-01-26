<? include '../db_config.php'; //DB 연결 ?>

<?
$city = $_POST['city'];
$sql = "SELECT * FROM korea_latlon WHERE docity LIKE '%$city%'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);

$docity = $row['docity'];
$lat = $row['lat'];
$lon = $row['lon'];
?>