<?php
$servername = "localhost";
$username = "root";
$password = "";
$DBname = "zencart";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$DBname", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    //get all categories and subcategories that have inactive products
    $sql = "SELECT c.categories_id 
            FROM TABLE_CATEGORIES c 
            INNER JOIN TABLE_PRODUCTS_TO_CATEGORIES ptc ON c.categories_id  = ptc.categories_id 
            INNER JOIN TABLE_PRODUCTS p ON p.products_id = ptc.products_id 
            WHERE p.products_status  = 0";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $categories_withinactive_products[] = $stmt->fetchAll();

    $categories_ids = [];

    foreach ($categories_withinactive_products[0] as $records) {
        array_push($categories_ids, intval($records['categories_id']));
    }

    //get all categories that have no products
    $sql = "SELECT c.categories_id FROM TABLE_CATEGORIES c WHERE c.categories_id NOT IN (SELECT ptc.categories_id FROM TABLE_PRODUCTS_TO_CATEGORIES ptc)";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $categories_without_products[] = $stmt->fetchAll();

    foreach ($categories_without_products[0] as $records) {
        array_push($categories_ids, intval($records['categories_id']));
    }
    $categories_ids = implode(',', $categories_ids);

    //set the categories to inactive
    $sql = "UPDATE categories SET categories_status=0 WHERE categories_id IN ($categories_ids)";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    //echo a message to say the UPDATE succeeded
    echo $stmt->rowCount() . " records UPDATED successfully";
} catch (PDOException $e) {

    echo "Connection failed: " . $e->getMessage();
}//end of script
