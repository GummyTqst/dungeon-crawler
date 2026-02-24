<?php
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case "GET":
        if ($id) {
            $stmt = $dbh->prepare("
                SELECT
                    armor.id,
                    armor.name,
                    armor.rarity,
                    armor.defense,
                    armor_properties.id AS property_id,
                    armor_properties.property_name,
                    armor_properties.value
                FROM armor
                LEFT JOIN armor_properties
                    ON armor_properties.armor_id = armor.id
                WHERE armor.id = ?
            ");
            $stmt->execute([$id]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$rows) {
                http_response_code(404);
                echo json_encode(["error" => "Armor not found"]);
                exit;
            }

            $armor = [
                "id" => $rows[0]['id'],
                "name" => $rows[0]['name'],
                "rarity" => $rows[0]['rarity'],
                "defense" => $rows[0]['defense'],
                "properties" => []
            ];

            foreach ($rows as $row) {
                if ($row['property_id']) {
                    $armor['properties'][] = [
                        "id" => $row['property_id'],
                        "property_name" => $row['property_name'],
                        "value" => $row['value']
                    ];
                }
            }

            echo json_encode($armor);

        } else {
            $stmt = $dbh->query("
                SELECT
                    armor.id,
                    armor.name,
                    armor.rarity,
                    armor.defense,
                    armor_properties.id AS property_id,
                    armor_properties.property_name,
                    armor_properties.value
                FROM armor
                LEFT JOIN armor_properties
                    ON armor_properties.armor_id = armor.id
                ORDER BY armor.id
            ");

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $armorList = [];

            foreach ($rows as $row) {
                $armorId = $row['id'];

                if (!isset($armorList[$armorId])) {
                    $armorList[$armorId] = [
                        "id" => $row['id'],
                        "name" => $row['name'],
                        "rarity" => $row['rarity'],
                        "defense" => $row['defense'],
                        "properties" => []
                    ];
                }

                if ($row['property_id']) {
                    $armorList[$armorId]['properties'][] = [
                        "id" => $row['property_id'],
                        "property_name" => $row['property_name'],
                        "value" => $row['value']
                    ];
                }
            }

            echo json_encode(array_values($armorList));
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
        $defense = $data['defense'] ?? 10;

        // Validate rarity
        $allowedRarities = ['common', 'rare', 'epic', 'unique'];
        if (!in_array($rarity, $allowedRarities)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid rarity. Allowed values: common, rare, epic, unique"]);
            exit;
        }

        // Validate defense is a number
        if (!is_numeric($defense)) {
            http_response_code(400);
            echo json_encode(["error" => "Defense must be a number"]);
            exit;
        }

        $stmt = $dbh->prepare("INSERT INTO armor (name, rarity, defense) VALUES (?, ?, ?)");
        $stmt->execute([$data['name'], $rarity, $defense]);

        $armorId = $dbh->lastInsertId();

        //Insert properties if provided
        if (isset($data['properties']) && is_array($data['properties'])) {
            foreach ($data['properties'] as $property) {
                if (!isset($property['property_name']) || !isset($property['value'])) {
                    continue; // Skip invalid properties
                }

                $stmt = $dbh->prepare("INSERT INTO armor_properties (armor_id, property_name, value) VALUES (?, ?, ?)");
                $stmt->execute([
                    $armorId,
                    $property['property_name'],
                    $property['value']
                ]);
            }
        }

        http_response_code(201);
        echo json_encode([
            "message" => "Armor created successfully",
            "id" => $armorId
        ]);
        break;

    case "DELETE":
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Armor ID is required"]);
            exit;
        }

        // Check if armor exists
        $stmt = $dbh->prepare("SELECT id FROM armor WHERE id = ?");
        $stmt->execute([$id]);

        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(["error" => "Armor not found"]);
            exit;
        }

        // Delete armor
        $stmt = $dbh->prepare("DELETE FROM armor WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(["message" => "Armor deleted successfully"]);
        break;

    case 'PATCH':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Armor ID is required"]);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        // Check if armor exists
        $stmt = $dbh->prepare("SELECT id FROM armor WHERE id = ?");
        $stmt->execute([$id]);
        $armor = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$armor) {
            http_response_code(404);
            echo json_encode(["error" => "Armor not found"]);
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

        if (isset($data['defense'])) {
            if (!is_numeric($data['defense'])) {
                http_response_code(400);
                echo json_encode(["error" => "Defense must be a number"]);
                exit;
            }
            $fields[] = "defense = ?";
            $values[] = $data['defense'];
        }

        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(["error" => "No valid fields to update"]);
            exit;
        }

        $values[] = $id;

        // Execute update
        $sql = "UPDATE armor SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $dbh->prepare($sql);
        $stmt->execute($values);

        echo json_encode(["message" => "Armor updated successfully"]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}
?>