<?php 
session_start();

if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
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
        $videoTitle = $_POST['video-title'] ?? '';
        $videoDescription = $_POST['video-description'] ?? '';
        $urlVideo = $_POST['urlVideo'] ?? '';
        $currentUserId = $_SESSION['userId'];

        if (strlen($videoTitle) > 60) {
            echo "Video title must not exceed 60 characters.";
            exit();
        }

        $pdo->beginTransaction(); // Start transaction

        // Update video info:
        $sql = "UPDATE video SET nameVideo = ?, description = ? WHERE urlVideo = ? AND authorsID = ?";
        $stmt = $pdo->prepare($sql);

        if (!$stmt->execute([$videoTitle, $videoDescription, $urlVideo, $currentUserId])) {
            throw new Exception("Failed to update video info.");
        }

        // Save markers and images:
        $baseDir = 'video/';
        $videoDir = $baseDir . $urlVideo . '/';

        if (!is_dir($videoDir)) {
            mkdir($videoDir, 0777, true);
        }

        for ($i = 0; $i < count($_FILES['imageFiles']['name']); $i++) {
            $timeMarker = $_POST["time_marker"][$i];
            $nameMarker = $_POST["name_marker"][$i];
            $imageName = $_FILES['imageFiles']['name'][$i];

            $targetPath = $videoDir . $imageName;
            move_uploaded_file($_FILES['imageFiles']['tmp_name'][$i], $targetPath);

            $sql = "INSERT INTO marker (idVideo, time_marker, name_marker, image_name) VALUES (:idVideo, :timeMarker, :nameMarker, :imageName)";
            $stmtMarker = $pdo->prepare($sql);

            $stmtMarker->bindParam(':idVideo', $currentUserId, PDO::PARAM_INT); // Update as necessary
            $stmtMarker->bindParam(':timeMarker', $timeMarker, PDO::PARAM_STR);
            $stmtMarker->bindParam(':nameMarker', $nameMarker, PDO::PARAM_STR);
            $stmtMarker->bindParam(':imageName', $imageName, PDO::PARAM_STR);

            if (!$stmtMarker->execute()) {
                throw new Exception("Failed to insert marker.");
            }
        }

        $pdo->commit(); // Commit transaction
        echo "Video and markers successfully updated.";
    }
} catch (PDOException $e) {
    $pdo->rollBack(); // Rollback transaction if necessary
    echo "Database error: " . $e->getMessage();
} catch (Exception $e) {
    $pdo->rollBack();
    echo $e->getMessage();
}