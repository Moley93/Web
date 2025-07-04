<?php
// api/search.php - Product search API
require_once '../config.php';

header('Content-Type: application/json');

$query = sanitize_input($_GET['q'] ?? '');
$limit = min((int)($_GET['limit'] ?? 10), 20); // Max 20 results
$category = sanitize_input($_GET['category'] ?? '');

$response = ['success' => false, 'results' => [], 'total' => 0];

if (empty($query) || strlen($query) < 2) {
    $response['message'] = 'Search query must be at least 2 characters';
    echo json_encode($response);
    exit;
}

try {
    // Build search query
    $search_conditions = [];
    $params = [];
    
    // Always search active products
    $search_conditions[] = "is_active = 1";
    
    // Text search in name and description
    $search_conditions[] = "(name LIKE ? OR description LIKE ?)";
    $search_param = "%$query%";
    $params[] = $search_param;
    $params[] = $search_param;
    
    // Category filter
    if (!empty($category)) {
        $search_conditions[] = "category = ?";
        $params[] = $category;
    }
    
    $where_clause = implode(" AND ", $search_conditions);
    
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM products WHERE $where_clause";
    $count_stmt = $pdo->prepare($count_sql);
    $count_stmt->execute($params);
    $total_results = $count_stmt->fetch()['total'];
    
    // Get search results with relevance scoring
    $sql = "
        SELECT 
            id,
            name,
            description,
            price,
            stock_quantity,
            category,
            image_url,
            (
                CASE 
                    WHEN name LIKE ? THEN 10
                    ELSE 0
                END +
                CASE 
                    WHEN name LIKE ? THEN 5
                    ELSE 0
                END +
                CASE 
                    WHEN description LIKE ? THEN 1
                    ELSE 0
                END
            ) as relevance_score
        FROM products 
        WHERE $where_clause
        ORDER BY relevance_score DESC, name ASC
        LIMIT ?
    ";
    
    // Add relevance parameters
    $relevance_params = [
        "$query%",      // Exact start match (highest score)
        "%$query%",     // Contains match (medium score)
        "%$query%",     // Description match (lowest score)
    ];
    
    $all_params = array_merge($relevance_params, $params, [$limit]);
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($all_params);
    $results = $stmt->fetchAll();
    
    // Format results
    $formatted_results = [];
    foreach ($results as $product) {
        $formatted_results[] = [
            'id' => (int)$product['id'],
            'name' => $product['name'],
            'description' => $product['description'],
            'price' => (float)$product['price'],
            'formatted_price' => format_price($product['price']),
            'stock_quantity' => (int)$product['stock_quantity'],
            'category' => $product['category'],
            'image_url' => $product['image_url'],
            'in_stock' => $product['stock_quantity'] > 0,
            'url' => '/hardware.php?search=' . urlencode($product['name']),
            'relevance_score' => (int)$product['relevance_score']
        ];
    }
    
    $response['success'] = true;
    $response['results'] = $formatted_results;
    $response['total'] = $total_results;
    $response['query'] = $query;
    $response['limit'] = $limit;
    
    // Add search suggestions if no results found
    if (empty($formatted_results)) {
        $response['suggestions'] = getSearchSuggestions($query, $pdo);
    }
    
} catch (PDOException $e) {
    error_log("Search API Error: " . $e->getMessage());
    $response['message'] = 'Search error occurred';
} catch (Exception $e) {
    error_log("Search API Error: " . $e->getMessage());
    $response['message'] = 'An error occurred';
}

echo json_encode($response);

/**
 * Get search suggestions when no results are found
 */
function getSearchSuggestions($query, $pdo) {
    $suggestions = [];
    
    try {
        // Get similar products by category keywords
        $category_keywords = [
            'arduino' => 'Hardware',
            'esp32' => 'Hardware', 
            'esp8266' => 'Hardware',
            'sensor' => 'Hardware',
            'development' => 'Hardware',
            'board' => 'Hardware',
            'module' => 'Hardware'
        ];
        
        $query_lower = strtolower($query);
        foreach ($category_keywords as $keyword => $category) {
            if (strpos($query_lower, $keyword) !== false) {
                $stmt = $pdo->prepare("
                    SELECT name FROM products 
                    WHERE category = ? AND is_active = 1 
                    ORDER BY RAND() LIMIT 3
                ");
                $stmt->execute([$category]);
                $products = $stmt->fetchAll();
                
                foreach ($products as $product) {
                    $suggestions[] = $product['name'];
                }
                break;
            }
        }
        
        // If no category matches, get popular products
        if (empty($suggestions)) {
            $stmt = $pdo->prepare("
                SELECT name FROM products 
                WHERE is_active = 1 
                ORDER BY RAND() LIMIT 5
            ");
            $stmt->execute();
            $products = $stmt->fetchAll();
            
            foreach ($products as $product) {
                $suggestions[] = $product['name'];
            }
        }
        
    } catch (PDOException $e) {
        // Return empty suggestions on error
    }
    
    return array_slice($suggestions, 0, 5);
}
?>