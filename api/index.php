<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once("db.php");

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base_path = "/dungeon-crawler/api/";

// This removes the base_path and then TRIMS any leading/trailing slashes
$path = trim(str_replace($base_path, "", $request_uri), "/");
$path_parts = explode("/", $path);

$resource = $path_parts[0] ?? ""; // Should be "players"
$id = $path_parts[1] ?? null;    // Should be "1"

switch ($resource) {
    case "equipment":
        require("equipment.php");
        break;
    case "players":
        require("players.php");
        break;
    case "heroes":
        require("heroes.php");
        break;
    case "weapons":
        require("weapons.php");
        break;
    case "armor":
        require("armor.php");
        break;
    case "":
        // echo json_encode(["message" => "Welcome to the Dungeon Crawler API!"]);
        $sql = "
        SELECT
            players.id AS player_id,
            players.username,
            players.created_at,

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
            armor.defense

            armor_properties.id AS property_id,
            armor_properties.property_name,
            armor_properties.value

        FROM players

        LEFT JOIN heroes
            ON heroes.player_id = players.id

        LEFT JOIN hero_weapon
            ON hero_weapon.hero_id = heroes.id AND hero_weapon.is_equipped = 1
        LEFT JOIN weapons
            ON weapons.id = hero_weapon.weapon_id

        LEFT JOIN hero_armor
            ON hero_armor.hero_id = heroes.id AND hero_armor.is_equipped = 1
        LEFT JOIN armor
            ON armor.id = hero_armor.armor_id

        LEFT JOIN armor_properties
            ON armor_properties.armor_id = armor.id

        ORDER BY players.id, heroes.id, armor.id
        ";
        
        $stmt = $dbh->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $players = [];

        foreach ($results as $row) {
            // Group by player
            $player_id = $row['player_id'];

            if (!isset($players[$player_id])) {
                $players[$player_id] = [
                    "id" => $player_id,
                    "username" => $row['username'],
                    "created_at" => $row['created_at'],
                    "heroes" => []
                ];
            }

            // Heroes and their equipment
            if ($row['hero_id']) {
                $hero_id = $row['hero_id'];

                if (!isset($players[$player_id]['heroes'][$hero_id])) {
                    $players[$player_id]['heroes'][$hero_id] = [
                        'id' => $row['hero_id'],
                        'name' => $row['hero_name'],
                        'level' => $row['level'],
                        'weapons' => [],
                        'armor' => []
                    ];
                }

                // Weapons
                if ($row['weapon_id'] && !in_array($row['weapon_id'], array_column($players[$player_id]['heroes'][$hero_id]['weapons'], 'id'))) {
                    $players[$player_id]['heroes'][$hero_id]['weapons'][] = [
                        'id' => $row['weapon_id'],
                        'name' => $row['weapon_name'],
                        'rarity' => $row['weapon_rarity'],
                        'power' => $row['power']
                    ];
                }
                
                // Armor
                if ($row['armor_id'] && !in_array($row['armor_id'], array_column($players[$player_id]['heroes'][$hero_id]['armor'], 'id'))) {
                    $players[$player_id]['heroes'][$hero_id]['armor'][] = [
                        "id" => $row['armor_id'],
                        "name" => $row['armor_name'],
                        "rarity" => $row['armor_rarity'],
                        "defense" => $row['defense']
                    ];
                }
            }
        }

        foreach ($players as &$player) {
            $player['heroes'] = array_values($player['heroes']);
        }

        echo json_encode(array_values($players), JSON_PRETTY_PRINT);
        break;
    default:
        http_response_code(404);
        echo json_encode(["error" => "Resource not found"]);
        break;
}