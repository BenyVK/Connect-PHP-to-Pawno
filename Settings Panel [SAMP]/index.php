<?php
require_once 'config.php';

$message = '';
$newValue = 0;

// Update gWeather value --
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_gWeather'])) {
    $newValue = (int)($_POST['gWeather'] ?? 0);
    
    if ($newValue >= 0 && $newValue <= 20) {
        try {
            $conn = getDBConnection();
            
            // First check if table exists --
            $tableCheck = $conn->query("SHOW TABLES LIKE 'others'");
            if ($tableCheck && $tableCheck->num_rows > 0) {
                // Check if record exists --
                $checkRecord = $conn->query("SELECT * FROM others WHERE section = 'others' LIMIT 1");
                
                if ($checkRecord && $checkRecord->num_rows > 0) {
                    // Update existing record --
                    $sql = "UPDATE others SET gWeather = ? WHERE section = 'others'";
                } else {
                    // Insert new record --
                    $sql = "INSERT INTO others (gWeather, section) VALUES (?, 'others')";
                }
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $newValue);
                
                if ($stmt->execute()) {
                    $message = "✅ Weather value successfully changed to <b>$newValue</b>";
                    
                    // Show current values --
                    $result = $conn->query("SELECT * FROM others WHERE section = 'others' LIMIT 5");
                    if ($result && $result->num_rows > 0) {
                        $message .= "<br><br><strong>Current values:</strong><br>";
                        while($row = $result->fetch_assoc()) {
                            $message .= "ID: " . ($row['id'] ?? 'N/A') . " | gWeather: " . ($row['gWeather'] ?? 'N/A') . " | Section: " . ($row['section'] ?? 'N/A') . "<br>";
                        }
                    }
                } else {
                    $message = "❌ Error executing query: " . $conn->error;
                }
                
                $stmt->close();
            } else {
                // Create table if it doesn't exist --
                $createTable = "CREATE TABLE IF NOT EXISTS others (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    gWeather INT DEFAULT 10,
                    section VARCHAR(50) DEFAULT 'others',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                
                if ($conn->query($createTable)) {
                    // Insert initial record --
                    $insertSQL = "INSERT INTO others (gWeather, section) VALUES (?, 'others')";
                    $stmt = $conn->prepare($insertSQL);
                    $stmt->bind_param("i", $newValue);
                    
                    if ($stmt->execute()) {
                        $message = "✅ Table created and weather value set to <b>$newValue</b>";
                    }
                    $stmt->close();
                } else {
                    $message = "❌ Error creating table: " . $conn->error;
                }
            }
            
            $conn->close();
            
        } catch (Exception $e) {
            $message = "❌ Error: " . $e->getMessage();
        }
    } else {
        $message = "❌ Value must be between 0 and 20";
    }
}

// Get current gWeather value for display --
$currentValue = 10;
try {
    $conn = getDBConnection();
    $result = $conn->query("SELECT gWeather FROM others WHERE section = 'others' LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $currentValue = $row['gWeather'] ?? 10;
    }
    $conn->close();
} catch (Exception $e) { 
    // Ignore error for display --
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Weather Control Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Server Weather Control Panel</h1>
        </div>
        
        <div class="content">
            <div class="message warning">
                <h3>⚠️ Important Notice</h3>
                <p>Weather changes will be applied to the server after 60 seconds (1 minute) for security and performance reasons.</p>
            </div>
            
            <div class="current-value">
                <strong>Current Weather ID:</strong> <span id="currentValue"><?php echo $currentValue; ?></span>
            </div>
            
            <?php if ($message): ?>
                <div class="message <?php echo strpos($message, '✅') !== false ? 'success' : 'error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="update_gWeather" value="1">
                
                <div class="form-group">
                    <label for="gWeather">🎯 New Weather ID (0-20)</label>
                    <div class="slider-container">
                        <div class="slider-value" id="sliderValue"><?php echo $currentValue; ?></div>
                        <input type="range" 
                               class="slider" 
                               id="gWeatherSlider" 
                               min="0" 
                               max="20" 
                               value="<?php echo $currentValue; ?>"
                               oninput="document.getElementById('sliderValue').textContent = this.value;
                                        document.getElementById('gWeatherInput').value = this.value;">
                    </div>
                    <input type="number" 
                           id="gWeatherInput" 
                           name="gWeather" 
                           min="0" 
                           max="20" 
                           value="<?php echo $currentValue; ?>"
                           required
                           onchange="document.getElementById('gWeatherSlider').value = this.value;
                                    document.getElementById('sliderValue').textContent = this.value;">
                    <small style="color:#666; display:block; margin-top:5px;">
                        0-20: Different weather types | 20: Random weather
                    </small>
                </div>
                
                <button type="submit" class="btn">
                    💾 Save Changes
                </button>
            </form>
            
            <div class="info-box">
                <h4>About Creator</h4>
                <p align="center"><strong>Developer:</strong> Benyamin-Gharri</p>
                <p align="center"><strong>Scripter:</strong> Benyamin-Gharri</p>
                <p align="center" style="margin-top: 10px; color: #666; font-size: 12px;">
                    Database: <?php echo DB_NAME; ?> | Host: <?php echo DB_HOST; ?>:<?php echo DB_PORT; ?>
                </p>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('gWeatherInput').addEventListener('input', function() {
            document.getElementById('gWeatherSlider').value = this.value;
            document.getElementById('sliderValue').textContent = this.value;
        });
        
        // Update current value display
        document.getElementById('gWeatherSlider').addEventListener('input', function() {
            document.getElementById('currentValue').textContent = this.value;
        });
        
        document.getElementById('gWeatherInput').addEventListener('input', function() {
            document.getElementById('currentValue').textContent = this.value;
        });
    </script>
</body>
</html>