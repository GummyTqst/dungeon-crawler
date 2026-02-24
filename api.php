<?php

header("Content-Type: application/json");

require_once ("./db.php");

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Determine the type of entity to create
    $type = $data['type'] ?? 'player';

    switch($type) {
        case 'player':
            if (!isset ($data['username'])) {
                echo json_encode(["error" => "Username is required"]);
                exit;
            }
        
            $stmt = $dbh->prepare("INSERT INTO players (username, created_at) VALUES (?, CURDATE())");
            $stmt->execute([$data['username']]);
        
            echo json_encode([
                "message" => "Player created successfully",
                "id" => $dbh->lastInsertId()
            ]);
            break;
        case 'hero':
            if (!isset ($data['player_id']) || !isset($data['name'])) {
                echo json_encode(["error" => "player_id and name are required"]);
                exit;
            }

            $level = $data['level'] ?? 1;
            $stmt = $dbh->prepare("INSERT INTO heroes (player_id, name, level) VALUES (?, ?, ?)");
            $stmt->execute([$data['player_id'], $data['name'], $level]);
            
            echo json_encode([
                "message" => "Hero created successfully",
                "id" => $dbh->lastInsertId()
            ]);
            break;
        case 'weapon':
            if (!isset($data['name'])) {
                echo json_encode(["error" => "name is required"]);
                exit;
            }
    
            $rarity = $data['rarity'] ?? 'common';
            $power = $data['power'] ?? 10;
                
            $stmt = $dbh->prepare("INSERT INTO weapons (name, rarity, power) VALUES (?, ?, ?)");
            $stmt->execute([$data['name'], $rarity, $power]);
    
            echo json_encode([
                "message" => "Weapon created successfully",
                "id" => $dbh->lastInsertId()
            ]);
            break;
    
        case 'armor':
            if (!isset($data['name'])) {
                echo json_encode(["error" => "name is required"]);
                exit;
            }
    
            $armor_type = $data['armor_type'] ?? 'leather';
            $defense = $data['defense'] ?? 5;
                
            $stmt = $dbh->prepare("INSERT INTO armor (name, type, defense) VALUES (?, ?, ?)");
            $stmt->execute([$data['name'], $armor_type, $defense]);
    
            echo json_encode([
                "message" => "Armor created successfully",
                "id" => $dbh->lastInsertId()
            ]);
            break;
    
        default:
            echo json_encode(["error" => "Invalid type. Use: player, hero, weapon, or armor"]);
            exit;
    }
}


$sql = "
SELECT 
    players.id AS player_id,
    players.username,
    heroes.id AS hero_id,
    heroes.name AS hero_name,
    heroes.level,

    weapons.id AS weapon_id,
    weapons.name AS weapon_name,
    weapons.rarity AS weapon_rarity,
    weapons.power,

    armor.id AS armor_id,
    armor.name AS armor_name,
    armor.rarity AS armor_rarity,
    armor.defense,

    artifacts.id AS artifact_id,
    artifacts.name AS artifact_name,
    artifacts.cooldown,

    enchantments.id AS enchantment_id,
    enchantments.name AS enchantment_name

FROM players

LEFT JOIN heroes 
    ON heroes.player_id = players.id

LEFT JOIN weapons 
    ON weapons.hero_id = heroes.id

LEFT JOIN armor 
    ON armor.hero_id = heroes.id

LEFT JOIN artifacts 
    ON artifacts.hero_id = heroes.id

LEFT JOIN weapon_enchantments 
    ON weapon_enchantments.weapon_id = weapons.id

LEFT JOIN enchantments 
    ON enchantments.id = weapon_enchantments.enchantment_id
";

$stmt = $dbh->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$data = [];

foreach ($rows as $row) {

    $playerId = $row['player_id'];
    $heroId = $row['hero_id'];
    $weaponId = $row['weapon_id'];
    $armorId = $row['armor_id'];
    $artifactId = $row['artifact_id'];
    $enchantmentId = $row['enchantment_id'];

    // PLAYER
    if (!isset($data[$playerId])) {
        $data[$playerId] = [
            "id" => $playerId,
            "username" => $row['username'],
            "heroes" => []
        ];
    }

    // HERO
    if ($heroId && !isset($data[$playerId]['heroes'][$heroId])) {
        $data[$playerId]['heroes'][$heroId] = [
            "id" => $heroId,
            "name" => $row['hero_name'],
            "level" => $row['level'],
            "weapons" => [],
            "armor" => [],
            "artifacts" => []
        ];
    }

    // WEAPON
    if ($weaponId && !isset($data[$playerId]['heroes'][$heroId]['weapons'][$weaponId])) {
        $data[$playerId]['heroes'][$heroId]['weapons'][$weaponId] = [
            "id" => $weaponId,
            "name" => $row['weapon_name'],
            "rarity" => $row['weapon_rarity'],
            "power" => $row['power'],
            "enchantments" => []
        ];
    }

    // ENCHANTMENTS
    if ($weaponId && $enchantmentId) {
        $data[$playerId]['heroes'][$heroId]['weapons'][$weaponId]['enchantments'][] = [
            "id" => $enchantmentId,
            "name" => $row['enchantment_name']
        ];
    }

    // ARMOR
    if ($armorId && !isset($data[$playerId]['heroes'][$heroId]['armor'][$armorId])) {
        $data[$playerId]['heroes'][$heroId]['armor'][$armorId] = [
            "id" => $armorId,
            "name" => $row['armor_name'],
            "rarity" => $row['armor_rarity'],
            "defense" => $row['defense']
        ];
    }

    // ARTIFACTS
    if ($artifactId && !isset($data[$playerId]['heroes'][$heroId]['artifacts'][$artifactId])) {
        $data[$playerId]['heroes'][$heroId]['artifacts'][$artifactId] = [
            "id" => $artifactId,
            "name" => $row['artifact_name'],
            "cooldown" => $row['cooldown']
        ];
    }
}

// Clean up indexes
foreach ($data as &$player) {
    foreach ($player['heroes'] as &$hero) {
        $hero['weapons'] = array_values($hero['weapons']);
        $hero['armor'] = array_values($hero['armor']);
        $hero['artifacts'] = array_values($hero['artifacts']);
    }
    $player['heroes'] = array_values($player['heroes']);
}

echo json_encode(array_values($data), JSON_PRETTY_PRINT);
