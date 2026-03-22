#!/usr/bin/env python3
"""
PhytoCommerce — Climate Data Generator
Generates india_climate_zones.json and india_pin_prefix_zone_map.json
for the phyto_climate_zone PrestaShop module.

15 granular India climate zones (PCC-IN codes) covering all ~900 PIN prefixes.
Run: python3 generate_climate_data.py
Output: india_climate_zones.json, india_pin_prefix_zone_map.json
"""

import json

# ---------------------------------------------------------------------------
# 15 PCC-IN (PhytoCommerce Climate Code — India) zones
# Each has monthly avg temp (°C) and humidity (%) — Jan..Dec
# ---------------------------------------------------------------------------
ZONES = {
    "PCC-IN-01": {
        "label": "Humid Tropical Coast — South",
        "description": "Coastal Tamil Nadu, Andhra Pradesh, Kerala south. High humidity year-round, no frost.",
        "monthly_temp":     [24, 25, 27, 29, 30, 28, 27, 27, 27, 27, 25, 24],
        "monthly_humidity": [75, 72, 70, 72, 76, 85, 88, 88, 86, 85, 80, 76],
        "annual_min_temp": 18,
        "annual_max_temp": 38,
        "frost_risk": False,
        "monsoon_months": [6, 7, 8, 9, 10],   # 1-indexed
        "example_cities": ["Chennai", "Thiruvananthapuram", "Visakhapatnam"]
    },
    "PCC-IN-02": {
        "label": "Humid Tropical — Kerala & Konkan",
        "description": "Kerala, coastal Karnataka, Konkan coast. Very high monsoon rainfall, humid through winter.",
        "monthly_temp":     [26, 27, 29, 30, 30, 27, 26, 26, 27, 27, 26, 25],
        "monthly_humidity": [78, 76, 74, 76, 80, 92, 94, 93, 90, 88, 84, 80],
        "annual_min_temp": 19,
        "annual_max_temp": 36,
        "frost_risk": False,
        "monsoon_months": [6, 7, 8, 9, 10],
        "example_cities": ["Kochi", "Mangalore", "Goa", "Mumbai"]
    },
    "PCC-IN-03": {
        "label": "Tropical Wet-Dry — Deccan Plateau North",
        "description": "Interior Maharashtra, Karnataka plateau. Distinct dry winter, moderate monsoon.",
        "monthly_temp":     [22, 25, 29, 33, 35, 31, 28, 27, 28, 28, 25, 22],
        "monthly_humidity": [50, 45, 42, 40, 46, 68, 78, 80, 74, 62, 54, 50],
        "annual_min_temp": 12,
        "annual_max_temp": 42,
        "frost_risk": False,
        "monsoon_months": [6, 7, 8, 9],
        "example_cities": ["Pune", "Nashik", "Solapur", "Bijapur"]
    },
    "PCC-IN-04": {
        "label": "Tropical Dry — Telangana & Rayalaseema",
        "description": "Interior AP, Telangana. Very hot summers, low humidity, moderate monsoon.",
        "monthly_temp":     [22, 25, 29, 33, 36, 32, 29, 28, 29, 28, 24, 22],
        "monthly_humidity": [52, 48, 44, 42, 46, 65, 75, 78, 72, 64, 56, 53],
        "annual_min_temp": 14,
        "annual_max_temp": 44,
        "frost_risk": False,
        "monsoon_months": [6, 7, 8, 9],
        "example_cities": ["Hyderabad", "Vijayawada", "Tirupati", "Kurnool"]
    },
    "PCC-IN-05": {
        "label": "Subtropical — Indo-Gangetic Plains West",
        "description": "Delhi, western UP, Haryana, Punjab. Hot dry summer, cool winter with mild frost risk.",
        "monthly_temp":     [14, 17, 23, 30, 36, 38, 34, 33, 32, 28, 21, 15],
        "monthly_humidity": [68, 60, 46, 32, 28, 44, 68, 74, 60, 44, 50, 64],
        "annual_min_temp": 2,
        "annual_max_temp": 46,
        "frost_risk": True,
        "monsoon_months": [7, 8, 9],
        "example_cities": ["Delhi", "Lucknow", "Agra", "Chandigarh", "Jaipur"]
    },
    "PCC-IN-06": {
        "label": "Subtropical — Indo-Gangetic Plains East",
        "description": "Eastern UP, Bihar, Jharkhand. High humidity monsoon, cool foggy winter.",
        "monthly_temp":     [15, 18, 24, 30, 34, 34, 30, 29, 30, 28, 22, 16],
        "monthly_humidity": [72, 66, 56, 46, 50, 70, 82, 84, 76, 66, 62, 68],
        "annual_min_temp": 5,
        "annual_max_temp": 44,
        "frost_risk": True,
        "monsoon_months": [6, 7, 8, 9],
        "example_cities": ["Varanasi", "Patna", "Ranchi", "Allahabad"]
    },
    "PCC-IN-07": {
        "label": "Hot Arid — Rajasthan Desert",
        "description": "Western Rajasthan, Kutch. Extreme heat, very low humidity, minimal rain, cold winter nights.",
        "monthly_temp":     [14, 18, 24, 31, 37, 38, 34, 32, 32, 28, 21, 15],
        "monthly_humidity": [48, 40, 32, 26, 22, 34, 52, 58, 44, 32, 36, 46],
        "annual_min_temp": 2,
        "annual_max_temp": 48,
        "frost_risk": True,
        "monsoon_months": [7, 8],
        "example_cities": ["Jodhpur", "Jaisalmer", "Bikaner", "Barmer", "Bhuj"]
    },
    "PCC-IN-08": {
        "label": "Tropical Monsoon — Central India",
        "description": "MP, Chhattisgarh. Heavy monsoon, hot summer, mild dry winter.",
        "monthly_temp":     [18, 21, 27, 33, 37, 35, 29, 28, 29, 28, 23, 19],
        "monthly_humidity": [58, 50, 42, 38, 42, 68, 82, 84, 74, 60, 52, 55],
        "annual_min_temp": 8,
        "annual_max_temp": 44,
        "frost_risk": False,
        "monsoon_months": [6, 7, 8, 9],
        "example_cities": ["Bhopal", "Indore", "Raipur", "Jabalpur", "Nagpur"]
    },
    "PCC-IN-09": {
        "label": "Humid Subtropical — West Bengal & Odisha",
        "description": "West Bengal, Odisha. High humidity, heavy monsoon, warm winter.",
        "monthly_temp":     [19, 22, 28, 32, 34, 33, 30, 30, 30, 29, 24, 19],
        "monthly_humidity": [70, 64, 60, 64, 72, 82, 88, 88, 84, 78, 72, 68],
        "annual_min_temp": 10,
        "annual_max_temp": 42,
        "frost_risk": False,
        "monsoon_months": [6, 7, 8, 9],
        "example_cities": ["Kolkata", "Bhubaneswar", "Cuttack", "Durgapur"]
    },
    "PCC-IN-10": {
        "label": "Humid Subtropical — Northeast India",
        "description": "Assam, Meghalaya, Tripura. Very high rainfall, warm humid year-round.",
        "monthly_temp":     [16, 18, 23, 27, 28, 29, 29, 29, 28, 26, 21, 16],
        "monthly_humidity": [78, 76, 78, 80, 84, 88, 90, 90, 86, 82, 78, 76],
        "annual_min_temp": 8,
        "annual_max_temp": 36,
        "frost_risk": False,
        "monsoon_months": [5, 6, 7, 8, 9, 10],
        "example_cities": ["Guwahati", "Shillong", "Agartala", "Silchar"]
    },
    "PCC-IN-11": {
        "label": "Highland Subtropical — Western Ghats",
        "description": "Nilgiris, Coorg, Palani hills (600–1500 m). Cool year-round, heavy orographic rain.",
        "monthly_temp":     [14, 15, 18, 20, 20, 18, 17, 17, 17, 17, 15, 13],
        "monthly_humidity": [76, 72, 70, 76, 82, 90, 92, 92, 88, 86, 82, 78],
        "annual_min_temp": 5,
        "annual_max_temp": 28,
        "frost_risk": False,
        "monsoon_months": [6, 7, 8, 9, 10, 11],
        "example_cities": ["Ooty", "Munnar", "Coorg", "Kodaikanal"]
    },
    "PCC-IN-12": {
        "label": "Highland Temperate — Lower Himalayas",
        "description": "Uttarakhand, HP foothills, Darjeeling (800–2000 m). Cool summer, cold winter, frost possible.",
        "monthly_temp":     [8, 10, 15, 20, 24, 24, 22, 22, 20, 16, 11, 8],
        "monthly_humidity": [72, 68, 62, 56, 62, 80, 88, 86, 80, 72, 68, 70],
        "annual_min_temp": -2,
        "annual_max_temp": 32,
        "frost_risk": True,
        "monsoon_months": [6, 7, 8, 9],
        "example_cities": ["Dehradun", "Shimla", "Mussoorie", "Darjeeling", "Nainital"]
    },
    "PCC-IN-13": {
        "label": "Alpine — Upper Himalayas",
        "description": "High Himalaya, Ladakh, Kashmir valley (>2000 m). Short cool summer, harsh winter.",
        "monthly_temp":     [-2, 0, 5, 12, 17, 21, 24, 23, 18, 10, 4, -1],
        "monthly_humidity": [58, 54, 48, 42, 46, 58, 70, 72, 64, 52, 50, 56],
        "annual_min_temp": -15,
        "annual_max_temp": 30,
        "frost_risk": True,
        "monsoon_months": [7, 8],
        "example_cities": ["Srinagar", "Leh", "Manali", "Kargil"]
    },
    "PCC-IN-14": {
        "label": "Island Tropical — Andaman & Nicobar",
        "description": "Andaman & Nicobar Islands. Equatorial, very high humidity, year-round warmth.",
        "monthly_temp":     [26, 27, 28, 29, 29, 28, 28, 28, 28, 28, 27, 26],
        "monthly_humidity": [80, 78, 78, 80, 84, 88, 90, 90, 88, 86, 84, 82],
        "annual_min_temp": 22,
        "annual_max_temp": 34,
        "frost_risk": False,
        "monsoon_months": [5, 6, 7, 8, 9, 10, 11],
        "example_cities": ["Port Blair", "Car Nicobar"]
    },
    "PCC-IN-15": {
        "label": "Island Tropical — Lakshadweep",
        "description": "Lakshadweep Islands. True maritime tropical, constant warmth and humidity.",
        "monthly_temp":     [27, 27, 28, 29, 29, 28, 27, 27, 27, 27, 27, 27],
        "monthly_humidity": [78, 76, 76, 78, 82, 88, 90, 90, 87, 84, 82, 80],
        "annual_min_temp": 23,
        "annual_max_temp": 34,
        "frost_risk": False,
        "monsoon_months": [5, 6, 7, 8, 9, 10],
        "example_cities": ["Kavaratti", "Minicoy"]
    },
}

# ---------------------------------------------------------------------------
# Full India PIN prefix (3-digit) → PCC-IN zone mapping
# India PIN codes: 100–855 (not all in use, gaps exist)
# Sources: India Post PIN directory, IMD climate regionalization
# ---------------------------------------------------------------------------
def build_pin_map():
    pin_map = {}

    def assign(start, end, zone):
        for p in range(start, end + 1):
            pin_map[str(p)] = zone

    # ---- Circle 1: Delhi (110–110) → subtropical west ----
    assign(110, 110, "PCC-IN-05")

    # ---- Circle 2: UP (200–285) ----
    assign(200, 232, "PCC-IN-05")  # Western UP (Agra, Mathura, Meerut)
    assign(233, 285, "PCC-IN-06")  # Eastern UP (Allahabad, Varanasi, Gorakhpur)

    # ---- Circle 3: Uttarakhand (244–263) — carved from UP ----
    assign(244, 263, "PCC-IN-12")

    # ---- Circle 4: Haryana (119–136) ----
    assign(119, 136, "PCC-IN-05")

    # ---- Circle 5: Punjab (140–160) ----
    assign(140, 160, "PCC-IN-05")

    # ---- Circle 6: HP (170–177) ----
    assign(170, 177, "PCC-IN-12")

    # ---- Circle 7: J&K + Ladakh (180–194) ----
    assign(180, 185, "PCC-IN-12")  # Jammu division — lower himalayan
    assign(190, 194, "PCC-IN-13")  # Kashmir valley + Ladakh — alpine

    # ---- Circle 8: Rajasthan (301–344) ----
    assign(301, 306, "PCC-IN-05")  # Jaipur — not full desert
    assign(307, 344, "PCC-IN-07")  # Western & central Rajasthan

    # ---- Circle 9: Gujarat (360–396) ----
    assign(360, 363, "PCC-IN-07")  # Kutch — arid
    assign(364, 396, "PCC-IN-03")  # Saurashtra, mainland Gujarat

    # ---- Circle 10: Maharashtra (400–445) ----
    assign(400, 421, "PCC-IN-02")  # Mumbai + Konkan coast
    assign(422, 445, "PCC-IN-03")  # Pune, Nashik, interior

    # ---- Circle 11: Goa (403) — inside Maharashtra range, override ----
    assign(403, 403, "PCC-IN-02")

    # ---- Circle 12: MP & Chhattisgarh (450–497) ----
    assign(450, 497, "PCC-IN-08")

    # ---- Circle 13: Karnataka (560–591) ----
    assign(560, 562, "PCC-IN-03")  # Bangalore — plateau
    assign(563, 577, "PCC-IN-04")  # Interior Karnataka / Gulbarga
    assign(578, 581, "PCC-IN-11")  # Coorg / Western Ghats highland
    assign(582, 591, "PCC-IN-02")  # Coastal Karnataka (Mangalore)

    # ---- Circle 14: Kerala (670–695) ----
    assign(670, 695, "PCC-IN-02")
    assign(643, 643, "PCC-IN-11")  # Munnar area — highland

    # ---- Circle 15: Tamil Nadu (600–643) ----
    assign(600, 620, "PCC-IN-01")  # Chennai + coastal TN
    assign(621, 635, "PCC-IN-04")  # Interior TN (Salem, Madurai area)
    assign(636, 642, "PCC-IN-11")  # Nilgiris / Ooty
    assign(644, 670, "PCC-IN-01")  # Southern coastal TN

    # ---- Circle 16: Andhra Pradesh (500–535) ----
    assign(500, 509, "PCC-IN-04")  # Hyderabad + Telangana plateau
    assign(510, 535, "PCC-IN-04")  # Rayalaseema, interior AP

    # ---- Circle 17: Coastal AP (530–535, 530–533) ----
    assign(530, 533, "PCC-IN-01")  # Vijayawada + coastal AP

    # ---- Circle 18: Telangana (500–509 already assigned above) ----

    # ---- Circle 19: Odisha (751–770) ----
    assign(751, 770, "PCC-IN-09")

    # ---- Circle 20: West Bengal (700–743) ----
    assign(700, 743, "PCC-IN-09")
    assign(734, 734, "PCC-IN-12")  # Darjeeling — hill station

    # ---- Circle 21: Sikkim (737) ----
    assign(737, 737, "PCC-IN-12")

    # ---- Circle 22: Bihar (800–855) ----
    assign(800, 855, "PCC-IN-06")

    # ---- Circle 23: Jharkhand (814–835) ----
    assign(814, 835, "PCC-IN-06")

    # ---- Circle 24: Assam (781–788) ----
    assign(781, 788, "PCC-IN-10")

    # ---- Circle 25: Northeast states (790–799) ----
    assign(790, 799, "PCC-IN-10")  # Meghalaya, Nagaland, Mizoram, Manipur, Tripura, Arunachal

    # ---- Circle 26: Andaman & Nicobar (744) ----
    assign(744, 744, "PCC-IN-14")

    # ---- Circle 27: Lakshadweep (682) — inside Kerala range, override ----
    assign(682, 682, "PCC-IN-15")

    # ---- Fill remaining undefined prefixes with nearest logical zone ----
    # Covers sparse assignments and edge cases
    defaults = [
        (100, 118, "PCC-IN-05"),   # NCR fringe
        (137, 139, "PCC-IN-05"),   # Haryana fringe
        (161, 169, "PCC-IN-05"),   # Punjab fringe
        (178, 179, "PCC-IN-12"),   # HP fringe
        (195, 199, "PCC-IN-13"),   # J&K fringe
        (286, 300, "PCC-IN-06"),   # UP/Bihar fringe
        (345, 359, "PCC-IN-07"),   # Rajasthan fringe
        (397, 399, "PCC-IN-03"),   # Gujarat fringe
        (446, 449, "PCC-IN-08"),   # MP fringe
        (498, 499, "PCC-IN-08"),   # CG fringe
        (536, 559, "PCC-IN-04"),   # AP/Telangana east
        (592, 599, "PCC-IN-02"),   # Karnataka/Goa coast fringe
        (696, 699, "PCC-IN-02"),   # Kerala fringe
        (744, 750, "PCC-IN-09"),   # West Bengal fringe
        (771, 780, "PCC-IN-09"),   # Odisha fringe
        (789, 789, "PCC-IN-10"),   # NE fringe
        (856, 900, "PCC-IN-10"),   # Far NE / Arunachal fringe
    ]
    for start, end, zone in defaults:
        for p in range(start, end + 1):
            if str(p) not in pin_map:
                pin_map[str(p)] = zone

    return pin_map


def main():
    pin_map = build_pin_map()

    # Sort by prefix numerically
    pin_map_sorted = dict(sorted(pin_map.items(), key=lambda x: int(x[0])))

    # Write climate zones JSON
    zones_out = {
        "version": "1.0.0",
        "generated": "2026-03-22",
        "description": "PhytoCommerce Climate Code India (PCC-IN) — 15 granular zones",
        "months": ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],
        "zones": ZONES
    }
    with open("india_climate_zones.json", "w") as f:
        json.dump(zones_out, f, indent=2, ensure_ascii=False)
    print(f"✅ india_climate_zones.json — {len(ZONES)} zones written")

    # Write PIN prefix map JSON
    pin_out = {
        "version": "1.0.0",
        "generated": "2026-03-22",
        "description": "India 3-digit PIN prefix → PCC-IN zone code",
        "total_prefixes": len(pin_map_sorted),
        "map": pin_map_sorted
    }
    with open("india_pin_prefix_zone_map.json", "w") as f:
        json.dump(pin_out, f, indent=2, ensure_ascii=False)
    print(f"✅ india_pin_prefix_zone_map.json — {len(pin_map_sorted)} prefixes written")

    # Print summary
    from collections import Counter
    counts = Counter(pin_map_sorted.values())
    print("\n📊 Zone distribution:")
    for zone, count in sorted(counts.items()):
        label = ZONES[zone]["label"]
        print(f"   {zone}: {count:3d} prefixes — {label}")


if __name__ == "__main__":
    main()
