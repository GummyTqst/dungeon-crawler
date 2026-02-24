<?php
// db.php
try {
    // For MAMP, the default is usually "root" and "root"
    $dbh = new PDO("mysql:host=localhost;port=8888;dbname=dungeon-crawler", "root", "root");
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // This will stop the script and tell you EXACTLY why it failed
    header("Content-Type: application/json");
    http_response_code(500);
    die(json_encode(["db_error" => $e->getMessage()]));
}
?>