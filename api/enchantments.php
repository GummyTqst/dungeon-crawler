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
    
    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['name'])) {
            http_response_code(400);
            echo json_encode(["error" => "Name is required"]);
            exit;
        }

        $description = $data['description'] ?? null;

        $stmt = $dbh->prepare("INSERT INTO enchantments (name, description) VALUES (?, ?)");
        $stmt->execute([$data['name'], $description]);

        http_response_code(201);
        echo json_encode([
            "message" => "Enchantment created successfully",
            "id" => $dbh->lastInsertId()
        ]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}

?>