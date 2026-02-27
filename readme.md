# 🏰 Dungeon Crawler REST API

A mini RESTful API for minecraft dungeons (School project).  
Manage players, Weapons, Armors and enchantments.

---

## 📖 Overview

The **Dungeon Crawler API** provides endpoints to:

- Create and manage players
- Create and manage heroes
- See diffrent kind of weapons
- See diffrent kind of armor
- See diffrent enchantments

This API is designed for integration with web, mobile, or desktop frontends.

---

## 🚀 Features

- Player creation and stat management  
- Inventory & equipment system  

---

## 🛠️ Tech Stack

- **Backend:** PhP
- **Database:** MySQL
- **Documentation:** OpenAPI

---

## 📦 Installation

```bash
git clone https://github.com/yourusername/dungeon-crawler-api.git
cd dungeon-crawler
npm install
```

---

## ▶️ Running the Server

```bash
npm run dev
```

Server runs at:

```
http://localhost:3000
```

---

# 🧭 API Endpoints

---

## 🧙 Players

| Method | Endpoint | Description |
|--------|----------|------------|
| GET | `/players` | List all players |
| GET | `/players/:id` | Get player details |
| POST | `/players` | Create new player |
| DELETE | `/players/:id` | Delete player |

### Post Players exemple

```json
{
  "username": "Name here"
}
```

---

## Heroes

| Method | Endpoint | Description |
|--------|----------|------------|
| GET | `/heroes` | List all players |
| GET | `/heroes/:id` | Get player details |
| POST | `/heroes` | Create new player |
| DELETE | `/heroes/:id` | Delete player |

### Post Heroes exemple

```json
{
    "player_id": 2,
    "name": "Alex",
    "level": 35
}
```

---

## Weapons

| Method | Endpoint | Description |
|--------|----------|------------|
| GET | `/weapons` | List all weapons |
| GET | `/weapons/:id` | Get weapon details |
| POST | `/weapons` | Create new weapon |
| PATCH | `/weapons/:id` | Update weapon |
| DELETE | `/weapons/:id` | Delete weapon |

### Post Weapon exemple

```json
{
    "name": "Soul Knife",
    "rarity": "Epic",
    "power": 50
}
```

---

## Armor

| Method | Endpoint | Description |
|--------|----------|------------|
| GET | `/armor` | List all armor |
| GET | `/armor/:id` | Get armor details |
| POST | `/armor` | Create new armor |
| PATCH | `/armor/:id` | Update armor |
| DELETE | `/armor/:id` | Delete armor |

### Post Armor exemple

```json
{
    "name": "name of Armor",
    "rarity": "common, rare, epic or unique",
    "defense": 50,
    "properties": [
        {
            "property_name": "chance to teleport away when hit",
            "value": "5%"
        },
        {
            "property_name": "artifact cooldown",
            "value": "25%"
        },
        {
            "property_name": "arrows per bundle",
            "value": "+10"
        }
    ]
}
```

---

## 📊 HTTP Status Codes

| Code | Meaning |
|------|--------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 404 | Not Found |
| 500 | Internal Server Error |

---

## 👑 Author

Tobias Neumann  
GitHub: https://github.com/yourusername  
Email: your@email.com  