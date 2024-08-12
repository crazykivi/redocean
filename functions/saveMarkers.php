<?php
/*
session_start();

if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}

if (!defined('SITE_ROOT')) {
    define('SITE_ROOT', realpath(dirname(__FILE__, 2))); // 2 levels up
}

$host = 'localhost';
$dbname = 'web-player';
$username = 'root';
$password = '';
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $urlVideo = $_POST['urlVideo'] ?? ''; // Retrieve or define explicitly

        // Fetch idVideo:
        $stmtVideo = $pdo->prepare("SELECT idVideo FROM video WHERE urlVideo = :urlVideo");
        $stmtVideo->bindParam(':urlVideo', $urlVideo, PDO::PARAM_STR);
        $stmtVideo->execute();

        $rowVideo = $stmtVideo->fetch(PDO::FETCH_ASSOC);

        if (!$rowVideo) {
            echo "No video found with the given URL.";
            exit();
        }

        $idVideo = $rowVideo['idVideo'];

        // Directory setup:
        $baseDir = SITE_ROOT . '/video/';
        $videoDir = $baseDir . $urlVideo . '/'; // Valid concatenation

        if (!is_dir($videoDir)) {
            mkdir($videoDir, 0777, true);
        }

        // Insert markers:
        if (isset($_FILES['imageFiles']) && count($_FILES['imageFiles']['name']) > 0) {
            for ($i = 0; $i < count($_FILES['imageFiles']['name']); $i++) {
                $timeMarker = $_POST["time_marker"][$i];
                $nameMarker = $_POST["name_marker"][$i];
                $imageName = $_FILES['imageFiles']['name'][$i];

                $targetPath = $videoDir . $imageName;
                move_uploaded_file($_FILES['imageFiles']['tmp_name'][$i], $targetPath);

                // Convert time to valid `TIME` format:
                $timeMarkerFormatted = date("H:i:s", strtotime($timeMarker));

                // Insert into database:
                $sqlMarker = "INSERT INTO marker (idVideo, time_marker, name_marker, image_name) VALUES (:idVideo, :timeMarker, :nameMarker, :imageName)";
                $stmtMarker = $pdo->prepare($sqlMarker);

                $stmtMarker->bindParam(':idVideo', $idVideo, PDO::PARAM_INT);
                $stmtMarker->bindParam(':timeMarker', $timeMarkerFormatted, PDO::PARAM_STR);
                $stmtMarker->bindParam(':nameMarker', $nameMarker, PDO::PARAM_STR);
                $stmtMarker->bindParam(':imageName', $imageName, PDO::PARAM_STR);

                if (!$stmtMarker->execute()) {
                    throw new Exception("Failed to insert marker.");
                }
            }
        }

        echo "Markers saved successfully.";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
*/
session_start();

if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}

if (!defined('SITE_ROOT')) {
    define('SITE_ROOT', realpath(dirname(__FILE__, 2))); // 2 levels up
}

$host = 'localhost';
$dbname = 'web-player';
$username = 'root';
$password = '';
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $urlVideo = $_POST['urlVideo'] ?? ''; // Retrieve or define explicitly

        // Fetch idVideo:
        $stmtVideo = $pdo->prepare("SELECT idVideo FROM video WHERE urlVideo = :urlVideo");
        $stmtVideo->bindParam(':urlVideo', $urlVideo, PDO::PARAM_STR);
        $stmtVideo->execute();

        $rowVideo = $stmtVideo->fetch(PDO::FETCH_ASSOC);

        if (!$rowVideo) {
            echo "No video found with the given URL.";
            exit();
        }

        $idVideo = $rowVideo['idVideo'];

        $stmtCount = $pdo->prepare("SELECT COUNT(*) as markerCount FROM marker WHERE idVideo = :idVideo");
        $stmtCount->bindParam(':idVideo', $idVideo, PDO::PARAM_INT);
        $stmtCount->execute();

        $rowCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $currentMarkerCount = $rowCount['markerCount'];

        if ($currentMarkerCount >= 5) {
            echo "Достигнут лимит по маркерам на видео (Лимит 5).";
            exit();
        }

        // Directory setup:
        $baseDir = SITE_ROOT . '/video/';
        $videoDir = $baseDir . $urlVideo . '/';

        if (!is_dir($videoDir)) {
            mkdir($videoDir, 0777, true);
        }

        // Insert markers:
        if (isset($_FILES['imageFiles']) && count($_FILES['imageFiles']['name']) > 0) {
            for ($i = 0; $i < count($_FILES['imageFiles']['name']); $i++) {
                $timeMarker = $_POST["time_marker"][$i];
                $nameMarker = $_POST["name_marker"][$i];

                // Get file extension:
                $imageExt = pathinfo($_FILES['imageFiles']['name'][$i], PATHINFO_EXTENSION);

                // Generate a unique name:
                $uniqueName = 'slide-' . mt_rand(10000, 99999) . '.' . $imageExt;

                $targetPath = $videoDir . $uniqueName;
                move_uploaded_file($_FILES['imageFiles']['tmp_name'][$i], $targetPath);

                // Convert time to a valid `TIME` format:
                $timeMarkerFormatted = date("H:i:s", strtotime($timeMarker));

                // Insert into database:
                $sqlMarker = "INSERT INTO marker (idVideo, time_marker, name_marker, image_name) VALUES (:idVideo, :timeMarker, :nameMarker, :uniqueName)";
                $stmtMarker = $pdo->prepare($sqlMarker);

                $stmtMarker->bindParam(':idVideo', $idVideo, PDO::PARAM_INT);
                $stmtMarker->bindParam(':timeMarker', $timeMarkerFormatted, PDO::PARAM_STR);
                $stmtMarker->bindParam(':nameMarker', $nameMarker, PDO::PARAM_STR);
                $stmtMarker->bindParam(':uniqueName', $uniqueName, PDO::PARAM_STR);

                if (!$stmtMarker->execute()) {
                    throw new Exception("Failed to insert marker.");
                }
            }
        }

        echo "Markers saved successfully.";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}
