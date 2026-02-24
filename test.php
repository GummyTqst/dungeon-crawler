<?php

header("Content-Type: application/json");

require_once "db.php";

$id = $_GET['id'] ?? null;
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    /* =======================
       GET ARMOR
    ======================== */
    case "GET":

        if ($id) {

            $stmt = $dbh->prepare("
                SELECT 
                    a.id,
                    a.name,
                    a.rarity,
                    a.defense,
                    ap.id AS property_id,
                    ap.property_name,
                    ap.value
                FROM armor a
                LEFT JOIN armor_properties ap 
                    ON ap.armor_id = a.id
                WHERE a.id = ?
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
                    a.id,
                    a.name,
                    a.rarity,
                    a.defense,
                    ap.id AS property_id,
                    ap.property_name,
                    ap.value
                FROM armor a
                LEFT JOIN armor_properties ap 
                    ON ap.armor_id = a.id
                ORDER BY a.id
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


    /* =======================
       CREATE ARMOR
    ======================== */
    case "POST":

        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['name'])) {
            http_response_code(400);
            echo json_encode(["error" => "Name is required"]);
            exit;
        }

        $rarity = $data['rarity'] ?? 'common';
        $defense = $data['defense'] ?? 10;

        $allowedRarities = ['common', 'rare', 'epic', 'unique'];

        if (!in_array($rarity, $allowedRarities)) {
            http_response_code(400);
            echo json_encode(["error" => "Invalid rarity"]);
            exit;
        }

        if (!is_numeric($defense)) {
            http_response_code(400);
            echo json_encode(["error" => "Defense must be numeric"]);
            exit;
        }

        // Insert armor
        $stmt = $dbh->prepare("
            INSERT INTO armor (name, rarity, defense) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$data['name'], $rarity, $defense]);

        $armorId = $dbh->lastInsertId();

        // Insert properties if provided
        if (isset($data['properties']) && is_array($data['properties'])) {

            foreach ($data['properties'] as $property) {

                if (!isset($property['property_name']) || !isset($property['value'])) {
                    continue;
                }

                $stmt = $dbh->prepare("
                    INSERT INTO armor_properties (armor_id, property_name, value)
                    VALUES (?, ?, ?)
                ");

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


    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}
