<?php
// Direct database connection instead of requiring a file
$host = 'localhost';
$dbname = 'kds_store';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'pc_builds'");
    $tableExists = $result->rowCount() > 0;

    if (!$tableExists) {
        // Create table
        $conn->exec("
            CREATE TABLE pc_builds (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                purpose ENUM('gaming', 'office', 'content_creation', 'programming', 'student') NOT NULL,
                budget_range ENUM('budget', 'mid_range', 'high_end') NOT NULL,
                processor VARCHAR(100) NOT NULL,
                motherboard VARCHAR(100) NOT NULL,
                ram VARCHAR(100) NOT NULL,
                storage VARCHAR(100) NOT NULL,
                gpu VARCHAR(100) NOT NULL,
                psu VARCHAR(100) NOT NULL,
                case_type VARCHAR(100) NOT NULL,
                total_price DECIMAL(10, 2) NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Insert sample data
        $conn->exec("
            INSERT INTO pc_builds (name, purpose, budget_range, processor, motherboard, ram, storage, gpu, psu, case_type, total_price, description) VALUES
            ('Budget Gaming Build', 'gaming', 'budget', 'AMD Ryzen 5 5600G', 'MSI B550M PRO', '16GB DDR4 3200MHz', '500GB NVMe SSD', 'GTX 1660 Super', '550W 80+ Bronze', 'Micro ATX Tower', 85000.00, 'Perfect for 1080p gaming on a budget'),
            ('Mid-Range Gaming PC', 'gaming', 'mid_range', 'AMD Ryzen 7 5800X', 'ASUS ROG B550-F', '32GB DDR4 3600MHz', '1TB NVMe SSD + 2TB HDD', 'RTX 3070', '750W 80+ Gold', 'ATX Mid Tower', 175000.00, 'Excellent 1440p gaming performance'),
            ('High-End Gaming Rig', 'gaming', 'high_end', 'Intel i9-12900K', 'ASUS ROG Z690', '64GB DDR5 5200MHz', '2TB NVMe SSD + 4TB HDD', 'RTX 3080 Ti', '1000W 80+ Platinum', 'Full Tower', 350000.00, 'Ultimate 4K gaming experience'),
            ('Student Budget PC', 'student', 'budget', 'Intel i3-12100', 'ASRock H610M', '8GB DDR4 3200MHz', '256GB SSD', 'Intel UHD Graphics', '450W 80+ Bronze', 'Mini Tower', 45000.00, 'Perfect for students and basic computing'),
            ('Content Creator Build', 'content_creation', 'high_end', 'AMD Ryzen 9 5950X', 'ASUS X570 Pro', '64GB DDR4 3600MHz', '2TB NVMe SSD + 8TB HDD', 'RTX 3090', '1200W 80+ Platinum', 'Full Tower', 450000.00, 'Professional content creation and rendering'),
            ('Office Workstation', 'office', 'mid_range', 'Intel i5-12400', 'ASUS B660M', '16GB DDR4 3200MHz', '512GB NVMe SSD', 'Intel UHD Graphics', '550W 80+ Bronze', 'Micro ATX Tower', 65000.00, 'Reliable office workstation for productivity')
        ");

        echo "PC Builds table created and populated successfully!";
    } else {
        echo "PC Builds table already exists!";
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>