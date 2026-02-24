<?php

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case "GET":
        if (!empty($id)) {
            $stmt = $dbh->prepare("SELECT * FROM enchantments WHERE id = ?");
            $stmt->execute([$id]);
            $enchantment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$enchantment) {
                http_response_code(404);
                echo json_encode(["error" => "Enchantment not found"]);
                exit;
            }

            $stmt = $dbh->prepare("
                SELECT tier_level, value
                FROM enchantment_tiers
                WHERE enchantment_id = ?
                ORDER BY tier_level ASC
            ");
            $stmt->execute([$id]);
            $enchantment['tiers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode($enchantment);
        } else {
            $stmt = $dbh->query("SELECT * FROM enchantments");
            $enchantments = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($enchantments as &$enchantment) {
                $stmtTiers = $dbh->prepare("
                    SELECT tier_level, value
                    FROM enchantment_tiers
                    WHERE enchantment_id = ?
                    ORDER BY tier_level ASC
                ");
                $stmtTiers->execute([$enchantment['id']]);
                $enchantment['tiers'] = $stmtTiers->fetchAll(PDO::FETCH_ASSOC);
            }

            echo json_encode($enchantments);
        }
        break;
    
    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);
    
        if (!isset($data['name']) || !isset($data['equipment_type'])) {
            http_response_code(400);
            echo json_encode(["error" => "Name and equipment_type are required"]);
            exit;
        }
    
        try {
    
            $dbh->beginTransaction();
    
            $stmt = $dbh->prepare("
                INSERT INTO enchantments 
                (name, description, equipment_type, enchantment_cost) 
                VALUES (?, ?, ?, ?)
            ");
    
            $stmt->execute([
                $data['name'],
                $data['description'] ?? null,
                $data['equipment_type'],
                $data['enchantment_cost'] ?? 1
            ]);
    
            $enchantmentId = $dbh->lastInsertId();
    
            if (!empty($data['tiers']) && is_array($data['tiers'])) {
    
                $stmtTier = $dbh->prepare("
                    INSERT INTO enchantment_tiers
                    (enchantment_id, tier_level, value)
                    VALUES (?, ?, ?)
                ");
    
                foreach ($data['tiers'] as $tier) {
                    if (isset($tier['tier_level'], $tier['value'])) {
                        $stmtTier->execute([
                            $enchantmentId,
                            $tier['tier_level'],
                            $tier['value']
                        ]);
                    }
                }
            }
    
            $dbh->commit();
    
            http_response_code(201);
            echo json_encode([
                "message" => "Enchantment created successfully",
                "id" => $enchantmentId
            ]);
    
        } catch (Exception $e) {
    
            $dbh->rollBack();
    
            http_response_code(500);
            echo json_encode([
                "error" => "Failed to create enchantment"
            ]);
        }
    
        break;
    
        default:
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            break;
}

?>