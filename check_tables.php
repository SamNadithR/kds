<?php
require_once 'config.php';

try {
    // Get list of tables
    $stmt = $conn->query('SHOW TABLES');
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Existing tables in database: " . DB_NAME . "\n";
    print_r($tables);
    
    // Check table structures
    foreach (['users', 'products', 'qa'] as $table) {
        if (in_array($table, $tables)) {
            echo "\nStructure of table $table:\n";
            $stmt = $conn->query("DESCRIBE $table");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo $row['Field'] . " " . $row['Type'] . "\n";
            }
        }
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
