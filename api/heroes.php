<?php

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if ($id) {
            // GET single hero
            $stmt = $dbh->prepare("SELECT * FROM heroes WHERE id = ?");
            $stmt->execute([$id]);
            $hero = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($hero) {
                // Initialize empty arrays for equipment
                $hero['weapons'] = [];
                $hero['armor'] = [];
                
                // Only query if tables exist
                try {
                    $stmt = $dbh->prepare("
                        SELECT w.* FROM weapons w
                        JOIN hero_weapon hw ON w.id = hw.weapon_id
                        WHERE hw.hero_id = ? AND hw.is_equipped = 1
                    ");
                    $stmt->execute([$id]);
                    $hero['weapons'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    // Table doesn't exist yet
                }
                
                try {
                    $stmt = $dbh->prepare("
                        SELECT a.* FROM armor a
                        JOIN hero_armor ha ON a.id = ha.armor_id
                        WHERE ha.hero_id = ? AND ha.is_equipped = 1
                    ");
                    $stmt->execute([$id]);
                    $hero['armor'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    // Table doesn't exist yet
                }
                
                echo json_encode($hero);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Hero not found"]);
            }
        } else {
            // GET all heroes
            $stmt = $dbh->query("SELECT * FROM heroes");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['player_id']) || !isset($data['name'])) {
            http_response_code(400);
            echo json_encode(["error" => "player_id and name are required"]);
            exit;
        }

        $level = $data['level'] ?? 1;
        $stmt = $dbh->prepare("INSERT INTO heroes (player_id, name, level) VALUES (?, ?, ?)");
        $stmt->execute([$data['player_id'], $data['name'], $level]);

        http_response_code(201);
        echo json_encode([
            "message" => "Hero created successfully",
            "id" => $dbh->lastInsertId()
        ]);
        break;

    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Hero ID is required"]);
            exit;
        }

        // Check if hero exists
        $stmt = $dbh->prepare("SELECT * FROM heroes WHERE id = ?");
        $stmt->execute([$id]);

        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(["error" => "Hero not found"]);
            exit;
        }

        // Delete hero
        $stmt = $dbh->prepare("DELETE FROM heroes WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode(["message" => "Hero deleted successfully"]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}