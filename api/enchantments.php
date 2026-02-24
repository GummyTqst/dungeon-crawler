<?php

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case "GET":
        if ($id) {
            $stmt = $dbh->prepare("SELECT * FROM enchantments WHERE id = ?");
            $stmt->execute([$id]);
            $enchantment = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($enchantment) {
                echo json_encode($enchantment);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Enchantment not found"]);
            }
        } else {
            $stmt = $dbh->query("SELECT * FROM enchantments");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;
}

?>