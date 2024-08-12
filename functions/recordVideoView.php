<?php
function recordVideoView($idVideo, $idUsers, $ipAddress)
{
    global $pdo;
    $timeInterval = 1800;

    if ($idUsers) {
        $query = "SELECT * FROM video_views WHERE idVideo = :idVideo AND idUsers = :idUsers ORDER BY viewed_at DESC LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':idVideo', $idVideo);
        $stmt->bindParam(':idUsers', $idUsers);
        $stmt->execute();
        $existingView = $stmt->fetch(PDO::FETCH_ASSOC);

        $currentTime = time();
        if ($existingView) {
            $lastViewTime = strtotime($existingView['viewed_at']);

            if (($currentTime - $lastViewTime) > $timeInterval) {
                $query = "INSERT INTO video_views (idVideo, idUsers, ip_address, viewed_at) VALUES (:idVideo, :idUsers, :ipAddress, NOW())";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':idVideo', $idVideo);
                $stmt->bindParam(':idUsers', $idUsers);
                $stmt->bindParam(':ipAddress', $ipAddress);
                $stmt->execute();
            }
        } else {
            $query = "INSERT INTO video_views (idVideo, idUsers, ip_address, viewed_at) VALUES (:idVideo, :idUsers, :ipAddress, NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':idVideo', $idVideo);
            $stmt->bindParam(':idUsers', $idUsers);
            $stmt->bindParam(':ipAddress', $ipAddress);
            $stmt->execute();
        }
    } else {
        $query = "SELECT * FROM video_views WHERE idVideo = :idVideo AND idUsers IS NULL AND ip_address = :ipAddress ORDER BY viewed_at DESC LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':idVideo', $idVideo);
        $stmt->bindParam(':ipAddress', $ipAddress);
        $stmt->execute();
        $existingView = $stmt->fetch(PDO::FETCH_ASSOC);

        $currentTime = time();
        if ($existingView) {
            $lastViewTime = strtotime($existingView['viewed_at']);

            if (($currentTime - $lastViewTime) > $timeInterval) {
                $query = "UPDATE video_views SET viewed_at = NOW() WHERE idVideo = :idVideo AND idUsers IS NULL AND ip_address = :ipAddress";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':idVideo', $idVideo);
                $stmt->bindParam(':ipAddress', $ipAddress);
                $stmt->execute();
            }
        } else {
            $query = "INSERT INTO video_views (idVideo, idUsers, ip_address, viewed_at) VALUES (:idVideo, NULL, :ipAddress, NOW())";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':idVideo', $idVideo);
            $stmt->bindParam(':ipAddress', $ipAddress);
            $stmt->execute();
        }
    }
}
