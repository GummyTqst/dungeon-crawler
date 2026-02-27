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
    
    case "DELETE":
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "ID is required"]);
            exit;
        }

        // Check if weapon exists
        $stmt = $dbh->prepare("SELECT * FROM enchantments WHERE id = ?");
        $stmt->execute([$id]);

        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(["error" => "Enchantment not found"]);
            exit;
        }

        // Delete armor
        $stmt = $dbh->prepare("DELETE FROM enchantments WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode([
            "message" => "Enchantment deleted successfully"
        ]);
        break;

    case "PATCH":
        if (!$id) {
            http_response_code(400);
            echo json_encode(["error" => "Enchantment ID is required"]);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        $stmt = $dbh->prepare("SELECT id FROM enchantments WHERE id = ?");
        $stmt->execute([$id]);

        if (!$stmt->fetch()) {
            http_response_code(404);
            echo json_encode(["error" => "Enchantment not found"]);
            exit;
        }

        $fields = [];
        $values = [];

        // Update name field
        if (isset($data['name'])) {
            $fields[] = "name = ?";
            $values[] = $data['name'];
        }

        // Update description field
        if (isset($data['description'])) {
            $fields[] = "description = ?";
            $values[] = $data['description'];
        }

        // Update enchantment_type field
        if (isset($data['equipment_type'])) {
            $allowedTypes = ['weapon', 'armor', 'both'];
            if (!in_array($data['equipment_type'], $allowedTypes)) {
                http_response_code(400);
                echo json_encode(["error" => "Invalid equipment_type. Allowed values: weapon, armor, both"]);
                exit;
            }
            $fields[] = "equipment_type = ?";
            $values[] = $data['equipment_type'];
        }

        if (!empty($fields)) {
            $values[] = $id;
            $sql = "UPDATE enchantments SET " . implode(", ", $fields) . " WHERE id = ?";
            $stmt = $dbh->prepare($sql);
            $stmt->execute($values);
        }

        // Update tiers if provided
        if (isset($data['tiers']) && is_array($data['tiers'])) {
            // Delete existing tiers
            $stmt = $dbh->prepare("DELETE FROM enchantment_tiers WHERE enchantment_id = ?");
            $stmt->execute([$id]);

            // Insert new tiers
            foreach ($data['tiers'] as $tier) {
                if (!isset($tier['tier_level']) || !isset($tier['value'])) {
                    continue;
                }

                if (!is_numeric($tier['tier_level'])) {
                    http_response_code(400);
                    echo json_encode(["error" => "Tier level must be a number"]);
                    exit;
                }

                $stmt = $dbh->prepare("
                    INSERT INTO enchantment_tiers (enchantment_id, tier_level, value)
                    VALUES (?, ?, ?)
                ");
                $stmt->execute([$id, $tier['tier_level'], $tier['value']]);
            }
        }

        echo json_encode(["message" => "Enchantment updated successfully"]);
        break;
    
    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}

?>