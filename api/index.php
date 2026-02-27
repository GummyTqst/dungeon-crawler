<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once("db.php");

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base_path = "/dungeon-crawler/api/";
$path = trim(str_replace($base_path, "", $request_uri), "/");
$path_parts = array_filter(explode("/", $path));

$resource = $path_parts[0] ?? "";
$id = $path_parts[1] ?? null;

$allowed_resources = [
    'equipment',
    'players',
    'heroes',
    'weapons',
    'armor',
    'enchantments'
];

// Route handler
if (empty($resource)) {
    // Default/welcome route - show all data
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
            armor.defense,

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
                if ($row['armor_id']) {
                    $armor_id = $row['armor_id'];

                    $armorIndex = null;
                    foreach ($players[$player_id]['heroes'][$hero_id]['armor'] as $index => $existingArmor) {
                        if ($existingArmor['id'] == $armor_id) {
                            $armorIndex = $index;
                            break;
                        }
                    }

                    if ($armorIndex === null) {
                        $players[$player_id]['heroes'][$hero_id]['armor'][] = [
                            'id' => $armor_id,
                            'name' => $row['armor_name'],
                            'rarity' => $row['armor_rarity'],
                            'defense' => $row['defense'],
                            'properties' => []
                        ];
                        $armorIndex = count($players[$player_id]['heroes'][$hero_id]['armor']) - 1;
                    }

                    if ($row['property_id']) {
                        $propertyExists = false;
                        foreach ($players[$player_id]['heroes'][$hero_id]['armor'][$armorIndex]['properties'] as $property) {
                            if ($property['id'] == $row['property_id']) {
                                $propertyExists = true;
                                break;
                            }
                        }

                        if (!$propertyExists) {
                            $players[$player_id]['heroes'][$hero_id]['armor'][$armorIndex]['properties'][] = [
                                'id' => $row['property_id'],
                                'property_name' => $row['property_name'],
                                'value' => $row['value']
                            ];
                        }
                    }
                }
            }
        }

    // Convert associative arrays to indexed arrays for JSON
    foreach ($players as &$player) {
        $player['heroes'] = array_values($player['heroes']);
        foreach ($player['heroes'] as &$hero) {
            $hero['weapons'] = array_values($hero['weapons']);
            $hero['armor'] = array_values($hero['armor']);
        }
    }

    echo json_encode(array_values($players));
    
} elseif (in_array($resource, $allowed_resources)) {
    $file = __DIR__ . "/{$resource}.php";
    
    if (file_exists($file)) {
        require($file);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Resource handler not found"]);
    }
} else {
    http_response_code(404);
    echo json_encode(["error" => "Invalid resource", "resource" => $resource]);
}