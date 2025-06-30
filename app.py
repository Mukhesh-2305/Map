from flask import Flask, request, jsonify
from flask_cors import CORS
import requests
import json

app = Flask(__name__, static_folder='static', static_url_path='')
CORS(app)  # Enable CORS for all routes

@app.route('/')
def index():
    return app.send_static_file('index.html')

@app.route('/count-houses', methods=['POST'])
def count_houses():
    try:
        data = request.get_json()
        coords = data.get('polygon')

        if not coords or len(coords) < 3:
            return jsonify({"error": "Invalid polygon"}), 400

        poly_str = " ".join(f"{lat} {lng}" for lat, lng in coords)
        query = f"""
        [out:json][timeout:25];
        (
          way["building"](poly:"{poly_str}");
          relation["building"](poly:"{poly_str}");
        );
        out count;
        """

        print(f"[DEBUG] Querying Overpass API for count with polygon:\n{poly_str}")
        response = requests.post("https://overpass-api.de/api/interpreter", data={"data": query}, timeout=30)

        if response.status_code != 200:
            print(f"[ERROR] Overpass API status {response.status_code}: {response.text}")
            return jsonify({"error": f"Overpass API error: {response.status_code}"}), 500

        overpass_data = response.json()
        print(f"[DEBUG] Overpass Response (count): {overpass_data}")

        count = 0
        elements = overpass_data.get("elements", [])
        if elements and "tags" not in elements[0]:
            count = elements[0].get("count", 0)

        print(f"[INFO] Building count detected: {count}")
        return jsonify({"total_buildings": count})

    except Exception as e:
        print(f"[EXCEPTION] count_houses: {e}")
        return jsonify({"error": f"Server error: {str(e)}"}), 500


@app.route('/get-buildings', methods=['POST'])
def get_buildings():
    try:
        data = request.get_json()
        coords = data.get('polygon')

        if not coords or len(coords) < 3:
            return jsonify({"error": "Invalid polygon"}), 400

        poly_str = " ".join(f"{lat} {lng}" for lat, lng in coords)
        query = f"""
        [out:json][timeout:25];
        (
          way["building"](poly:"{poly_str}");
        );
        out geom;
        """

        print(f"[DEBUG] Querying Overpass API for geometry with polygon:\n{poly_str}")
        response = requests.post("https://overpass-api.de/api/interpreter", data={"data": query}, timeout=30)

        if response.status_code != 200:
            print(f"[ERROR] Overpass API status {response.status_code}: {response.text}")
            return jsonify({"error": f"Overpass API error: {response.status_code}"}), 500

        overpass_data = response.json()
        print(f"[DEBUG] Overpass Response (geometry): {overpass_data}")

        buildings = []
        for element in overpass_data.get("elements", []):
            if element.get("type") == "way" and "geometry" in element:
                coords = [[geom["lat"], geom["lon"]] for geom in element["geometry"]]
                buildings.append(coords)

        print(f"[INFO] Total buildings with geometry: {len(buildings)}")
        return jsonify({"buildings": buildings})

    except Exception as e:
        print(f"[EXCEPTION] get_buildings: {e}")
        return jsonify({"error": f"Server error: {str(e)}"}), 500


@app.route('/landuse-stats', methods=['POST'])
def landuse_stats():
    try:
        data = request.get_json()
        coords = data.get('polygon')
        if not coords or len(coords) < 3:
            return jsonify({"error": "Invalid polygon"}), 400

        poly_str = " ".join(f"{lat} {lng}" for lat, lng in coords)
        query = f"""
        [out:json][timeout:25];
        (
          way["landuse"](poly:"{poly_str}");
        );
        out tags geom;
        """
        response = requests.post("https://overpass-api.de/api/interpreter", data={"data": query}, timeout=30)
        if response.status_code != 200:
            return jsonify({"error": f"Overpass API error: {response.status_code}"}), 500

        overpass_data = response.json()
        agri = 0
        normal = 0
        for el in overpass_data.get("elements", []):
            landuse = el.get("tags", {}).get("landuse", "")
            if landuse in ["farmland", "farmyard", "orchard", "vineyard", "meadow"]:
                agri += 1
            else:
                normal += 1
        return jsonify({"agricultural": agri, "other_land": normal})
    except Exception as e:
        return jsonify({"error": f"Server error: {str(e)}"}), 500


@app.route('/analyze-place', methods=['POST'])
def analyze_place():
    try:
        data = request.get_json()
        coords = data.get('polygon')
        if not coords or len(coords) < 3:
            return jsonify({"error": "Invalid polygon"}), 400

        poly_str = " ".join(f"{lat} {lng}" for lat, lng in coords)

        # 1. Building count
        count_query = f"""
        [out:json][timeout:25];
        (
          way["building"](poly:"{poly_str}");
          relation["building"](poly:"{poly_str}");
        );
        out count;
        """
        count_resp = requests.post("https://overpass-api.de/api/interpreter", data={"data": count_query}, timeout=30)
        count_data = count_resp.json()
        count = 0
        elements = count_data.get("elements", [])
        if elements and "tags" not in elements[0]:
            count = elements[0].get("count", 0)

        # 2. Building outlines
        outlines_query = f"""
        [out:json][timeout:25];
        (
          way["building"](poly:"{poly_str}");
        );
        out geom;
        """
        outlines_resp = requests.post("https://overpass-api.de/api/interpreter", data={"data": outlines_query}, timeout=30)
        outlines_data = outlines_resp.json()
        buildings = []
        for element in outlines_data.get("elements", []):
            if element.get("type") == "way" and "geometry" in element:
                coords_ = [[geom["lat"], geom["lon"]] for geom in element["geometry"]]
                buildings.append(coords_)

        # Use the actual number of buildings found in geometry query
        count = len(buildings)

        # Calculate total boundary area
        total_area = calculate_polygon_area(coords)

        # 3. Landuse stats with area calculation
        landuse_query = f"""
        [out:json][timeout:25];
        (
          way["landuse"](poly:"{poly_str}");
        );
        out tags geom;
        """
        landuse_resp = requests.post("https://overpass-api.de/api/interpreter", data={"data": landuse_query}, timeout=30)
        landuse_data = landuse_resp.json()
        
        agri_area = 0
        other_area = 0
        
        for el in landuse_data.get("elements", []):
            if el.get("type") == "way" and "geometry" in el:
                # Calculate area of this landuse polygon
                coords_ = [[geom["lat"], geom["lon"]] for geom in el["geometry"]]
                area = calculate_polygon_area(coords_)
                
                landuse = el.get("tags", {}).get("landuse", "")
                if landuse in ["farmland", "farmyard", "orchard", "vineyard", "meadow"]:
                    agri_area += area
                else:
                    other_area += area

        # Calculate empty land (total area minus agricultural and other land)
        empty_area = max(0, total_area - agri_area - other_area)

        # Combine other land and empty land into single "other land" category
        combined_other_area = other_area + empty_area

        return jsonify({
            "total_buildings": count,
            "buildings": buildings,
            "total_area_km2": round(total_area, 4),
            "agricultural_area_km2": round(agri_area, 4),
            "other_land_area_km2": round(combined_other_area, 4)
        })
    except Exception as e:
        return jsonify({"error": f"Server error: {str(e)}"}), 500

def calculate_polygon_area(coords):
    """Calculate area of a polygon in square kilometers using shoelace formula"""
    if len(coords) < 3:
        return 0
    
    # Shoelace formula
    area = 0
    for i in range(len(coords)):
        j = (i + 1) % len(coords)
        area += coords[i][0] * coords[j][1]
        area -= coords[j][0] * coords[i][1]
    area = abs(area) / 2
    
    # Convert to square kilometers (rough approximation)
    # 1 degree ≈ 111 km, so 1 square degree ≈ 12,321 square km
    area_km2 = area * 111 * 111
    return area_km2

if __name__ == '__main__':
    app.run(debug=True, port=5000)
