<?php 

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if ($id) {
            $stmt = $dbh->prepare("SELECT * FROM players WHERE id = ?");
            $stmt->execute([$id]);
            $player = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get player's heroes
            if ($player) {
                $stmt = $dbh->prepare("SELECT * FROM heroes WHERE player_id = ?");
                $stmt->execute([$id]);
                $player['heroes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

                echo json_encode($player);
            } else {
                http_response_code(404);
                echo json_encode(["error" => "Player not found"]);
            }
        } else {
            // Get all players
            $stmt = $dbh->query("SELECT * FROM players");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['username'])) {
            http_response_code(400);
            echo json_encode(["error" => "Username is required"]);
            exit;
        }

        $stmt = $dbh->prepare("INSERT INTO players (username, created_at) VALUES (?, CURDATE())");
        $stmt->execute([$data['username']]);

        http_response_code(201);
        echo json_encode([
            "message" => "Player created successfully",
            "id" => $dbh->lastInsertId()
        ]);
        break;
    case 'DELETE':
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Player ID is required"]);
            exit;
        }
    
        // Check if player exists
        $stmt = $dbh->prepare("SELECT * FROM players WHERE id = ?");
        $stmt->execute([$id]);
    
        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(["error" => "Player not found"]);
            exit;
        }
    
    
        // Delete hero
        $stmt = $dbh->prepare("DELETE FROM players WHERE id = ?");
        $stmt->execute([$id]);
    
        echo json_encode(["message" => "Player deleted successfully"]);
        break;
        
    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}   

?>