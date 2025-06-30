from ultralytics import YOLO

# Load the YOLOv8 model (can be yolov8n.pt, yolov8s.pt, or your custom-trained model)
model = YOLO("models/yolov8n.pt")  # Make sure the model file exists in 'models/' folder

def detect_from_image(image_path):
    """
    Detects buildings in a given image using YOLOv8.
    
    Args:
        image_path (str): Path to the input image.
        
    Returns:
        List of dictionaries containing bounding boxes and confidence scores.
    """
    results = model(image_path)
    detections = []

    for result in results:
        for box in result.boxes.data:
            x1, y1, x2, y2, confidence, _ = box.tolist()
            if confidence > 0.5:
                detections.append({
                    "bbox": [x1, y1, x2, y2],
                    "confidence": round(confidence, 2)
                })

    return detections


# Example usage (for testing only)
if __name__ == "__main__":
    import sys
    image_path = sys.argv[1] if len(sys.argv) > 1 else "test.jpg"
    result = detect_from_image(image_path)
    print("Detected buildings:", result)
