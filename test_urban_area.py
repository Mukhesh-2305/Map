import requests
import json

def test_urban_area():
    # Test with a known urban area (New York City)
    test_polygon = [
        [40.7589, -73.9851],  # Times Square area
        [40.7589, -73.9841],
        [40.7599, -73.9841],
        [40.7599, -73.9851]
    ]
    
    try:
        print("Testing urban area (Times Square, NYC)...")
        
        response = requests.post(
            "http://localhost:5000/analyze-place",
            json={"polygon": test_polygon},
            timeout=30
        )
        
        print(f"Status: {response.status_code}")
        data = response.json()
        print(f"Response: {json.dumps(data, indent=2)}")
        
        if data.get("total_buildings", 0) > 0:
            print("✅ Found buildings in urban area!")
        else:
            print("❌ No buildings found even in urban area")
            
        print(f"Agricultural land area: {data.get('agricultural_area_km2', 0)} km²")
        print(f"Other land area: {data.get('other_land_area_km2', 0)} km²")
        
    except Exception as e:
        print(f"❌ Error: {e}")

if __name__ == "__main__":
    test_urban_area() 