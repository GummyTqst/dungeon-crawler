<?php

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);

        $action = $data['action'] ?? null;

        switch($action) {
            case 'equip_weapon':
                if (!isset($data['hero_id']) || !isset($data['weapon_id'])) {
                    http_response_code(400);
                    echo json_encode(["error" => "hero_id and weapon_id are required"]);
                    exit;
                }

                $is_equipped = $data['is_equipped'] ?? 1;

                $stmt = $dbh->prepare("INSERT INTO hero_weapon (hero_id, weapon_id, is_equipped) VALUES (?, ?, ?)");
                $stmt->execute([$data['hero_id'], $data['weapon_id'], $is_equipped]);

                echo json_encode([
                    "message" => "Weapon equipped successfully",
                ]);
                break;
            case 'equip_armor':
                if (!isset($data['hero_id']) || !isset($data['armor_id'])) {
                    http_response_code(400);
                    echo json_encode(["error" => "hero_id and armor_id are required"]);
                    exit;
                }

                $is_equipped = $data['is_equipped'] ?? 1;

                $stmt = $dbh->prepare("INSERT INTO hero_armor (hero_id, armor_id, is_equipped) VALUES (?, ?, ?)");
                $stmt->execute([$data['hero_id'], $data['armor_id'], $is_equipped]);

                echo json_encode([
                    "message" => "Armor equipped successfully",
                ]);
                break;
        }
        break;
    
    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}

?>