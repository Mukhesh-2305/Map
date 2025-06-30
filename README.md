# Geo Boundary & Building Detection System

A comprehensive web application for geographic boundary analysis, building detection, and land use classification with user authentication.

## Features

- **User Authentication**: Secure registration and login system
- **Interactive Mapping**: Create boundaries by clicking on the map
- **Building Detection**: Automatically detect and count buildings within boundaries
- **Land Use Analysis**: Classify land as agricultural or other
- **Area Calculations**: Calculate total area, agricultural area, and other land area
- **Data Storage**: Save analysis results to database
- **Modern UI**: Responsive design with beautiful interface

## Setup Instructions

### Prerequisites

1. **XAMPP** (or similar local server with PHP and MySQL)
2. **Python 3.7+** (for the Flask backend)
3. **pip** (Python package manager)

### Database Setup

1. Start XAMPP and ensure Apache and MySQL are running
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Create a new database named `map`
4. Import the `create_users_table.sql` file or run the SQL commands manually:

```sql
CREATE DATABASE IF NOT EXISTS map;
USE map;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS user_boundaries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    boundary_name VARCHAR(100),
    boundary_data JSON NOT NULL,
    total_buildings INT DEFAULT 0,
    total_area_km2 DECIMAL(10, 4),
    agricultural_area_km2 DECIMAL(10, 4),
    other_land_area_km2 DECIMAL(10, 4),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Backend Setup

1. Install Python dependencies:
```bash
pip install -r requirements.txt
```

2. Start the Flask backend:
```bash
python app.py
```

The backend will run on `http://localhost:5000`

### Frontend Setup

1. Place all files in your XAMPP `htdocs` folder
2. Access the application through:
   - Landing page: `http://localhost/Map/landing.html`
   - Login: `http://localhost/Map/login.html`
   - Register: `http://localhost/Map/register.html`
   - Main app: `http://localhost/Map/index.html`

## Usage

### Registration
1. Visit `register.html`
2. Fill in your details (name, email, password, phone, address)
3. Submit the form
4. You'll be redirected to login page

### Login
1. Visit `login.html`
2. Enter your email and password
3. After successful login, you'll be redirected to the main application

### Using the Application
1. **Mark Points**: Click "Enable Add Mode" and click on the map to mark boundary points
2. **Get Boundary**: Click "Get Boundary" to create a polygon from your points
3. **Analyze Place**: Click "Analyze Place" to detect buildings and classify land use
4. **Save Data**: Click "Save to Database" to store your analysis results

## File Structure

```
Map/
├── app.py                 # Flask backend for building detection
├── db.php                 # Database connection
├── login.php              # Login authentication
├── register.php           # User registration
├── check_session.php      # Session verification
├── logout.php             # User logout
├── index.html             # Main application interface
├── login.html             # Login page
├── register.html          # Registration page
├── landing.html           # Landing page
├── create_users_table.sql # Database setup
├── requirements.txt       # Python dependencies
└── README.md             # This file
```

## Security Features

- Password hashing using PHP's `password_hash()`
- Prepared statements to prevent SQL injection
- Session-based authentication
- Input validation and sanitization
- CSRF protection through form validation

## Test Account

A test account is automatically created:
- Email: `test@example.com`
- Password: `test123`

## Troubleshooting

1. **Database Connection Error**: Ensure MySQL is running and the `map` database exists
2. **Flask Backend Error**: Check if Python dependencies are installed and port 5000 is available
3. **Session Issues**: Ensure cookies are enabled in your browser
4. **CORS Errors**: The Flask app has CORS enabled, but ensure both frontend and backend are running

## API Endpoints

- `POST /analyze-place`: Analyze geographic boundaries and detect buildings
- `POST /save-to-database`: Save analysis results to database
- `POST /count-houses`: Count buildings in a polygon
- `POST /get-buildings`: Get building geometries
- `POST /landuse-stats`: Get land use statistics

## Technologies Used

- **Frontend**: HTML5, CSS3, JavaScript, Leaflet.js
- **Backend**: PHP, Python Flask
- **Database**: MySQL
- **APIs**: OpenStreetMap Overpass API
- **Authentication**: PHP Sessions

## Technical Details

### Building Detection

The system uses the Overpass API to query OpenStreetMap data:
- Searches for `way["building"]` and `relation["building"]` within the polygon
- Returns count and geometry data
- Handles timeouts and API errors gracefully

### Area Calculations

- **Area**: Uses shoelace formula for polygon area
- **Perimeter**: Uses Haversine formula for accurate distance calculation
- **Building Density**: Estimates based on building count and average building size

### Spatial Data

- Uses MySQL spatial extensions for efficient polygon storage
- WKT (Well-Known Text) format for geometry
- Spatial indexing for performance

## Development

### Adding New Features

1. **New Analysis Types**: Add endpoints to `app.py`
2. **UI Enhancements**: Modify `index.html` and CSS
3. **Database Changes**: Update schema in `setup_database.sql`

### Testing

- Test with different polygon sizes and shapes
- Verify building detection accuracy in various areas
- Check database storage and retrieval

## License

This project is open source and available under the MIT License.

## Support

For issues and questions:
1. Check the troubleshooting section
2. Verify all prerequisites are installed
3. Check browser console for JavaScript errors
4. Review server logs for backend errors 