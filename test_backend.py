import requests
import json

def test_backend():
    # Test data
    test_polygon = [
        [11.1271, 78.6569],
        [11.1271, 78.6579],
        [11.1281, 78.6579],
        [11.1281, 78.6569]
    ]
    
    try:
        print("Testing Flask backend...")
        
        # Test count-houses endpoint
        print("1. Testing /count-houses...")
        response = requests.post(
            "http://localhost:5000/count-houses",
            json={"polygon": test_polygon},
            timeout=30
        )
        print(f"   Status: {response.status_code}")
        print(f"   Response: {response.text}")
        
        # Test get-buildings endpoint
        print("2. Testing /get-buildings...")
        response2 = requests.post(
            "http://localhost:5000/get-buildings",
            json={"polygon": test_polygon},
            timeout=30
        )
        print(f"   Status: {response2.status_code}")
        print(f"   Response: {response2.text}")
        
        if response.status_code == 200 and response2.status_code == 200:
            print("✅ Backend is working correctly!")
        else:
            print("❌ Backend has issues")
            
    except requests.exceptions.ConnectionError:
        print("❌ Cannot connect to Flask backend. Make sure it's running on port 5000")
    except Exception as e:
        print(f"❌ Error: {e}")

if __name__ == "__main__":
    test_backend() 