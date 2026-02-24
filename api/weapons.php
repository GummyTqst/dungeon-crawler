<?php
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case "GET":
        if ($id) {
            $stmt = $dbh->prepare("SELECT * FROM weapons WHERE id = ?");
            $stmt->execute([$id]);
            $weapon = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($weapon) {
                echo json_encode($weapon);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Weapon not found"]);
            }
        } else {
            $stmt = $dbh->query("SELECT * FROM weapons");
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

        $rarity = $data['rarity'] ?? 'common';
        $power = $data['power'] ?? 10;

        $stmt = $dbh->prepare("INSERT INTO weapons (name, rarity, power) VALUES (?, ?, ?)");
        $stmt->execute([$data['name'], $rarity, $power]);

        http_response_code(201);
        echo json_encode([
            "message" => "Weapon created successfully",
            "id" => $dbh->lastInsertId()
        ]);
        break;

    case "DELETE":
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "ID is required"]);
            exit;
        }

        // Check if weapon exists
        $stmt = $dbh->prepare("SELECT * FROM weapons WHERE id = ?");
        $stmt->execute([$id]);

        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(["error" => "Weapon not found"]);
            exit;
        }

        // Delete armor
        $stmt = $dbh->prepare("DELETE FROM weapons WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode([
            "message" => "Weapon deleted successfully"
        ]);
        break;

    case 'PATCH':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Weapon ID is required"]);
            exit;
        }
    
        $data = json_decode(file_get_contents("php://input"), true);
    
        // Check if armor exists
        $stmt = $dbh->prepare("SELECT id FROM weapons WHERE id = ?");
        $stmt->execute([$id]);
        $weapon = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$weapon) {
            http_response_code(404);
            echo json_encode(["error" => "Weapon not found"]);
            exit;
        }
    
        $fields = [];
        $values = [];
    
        if (isset($data['name'])) {
            $fields[] = "name = ?";
            $values[] = $data['name'];
        }
    
        if (isset($data['rarity'])) {
            $allowedRarities = ['common', 'rare', 'epic', 'unique'];
            if (!in_array($data['rarity'], $allowedRarities)) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid rarity. Allowed values: common, rare, epic, unique"]);
                exit;
            }
            $fields[] = "rarity = ?";
            $values[] = $data['rarity'];
        }
    
        if (isset($data['power'])) {
            if (!is_numeric($data['power'])) {
                http_response_code(400);
                echo json_encode(["error" => "Power must be a number"]);
                exit;
            }
            $fields[] = "power = ?";
            $values[] = $data['power'];
        }
    
        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(["error" => "No valid fields to update"]);
            exit;
        }
    
        $values[] = $id;
    
        // Execute update
        $sql = "UPDATE weapons SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $dbh->prepare($sql);
        $stmt->execute($values);
    
        echo json_encode(["message" => "Weapon updated successfully"]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}
?>