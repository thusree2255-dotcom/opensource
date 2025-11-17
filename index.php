<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
// Database connection details
$serverName = "localhost";
$database = "testapi";
$username = "root";
$password = "";

// Connect to database
$conn = new mysqli($serverName, $username, $password, $database);
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Set header
header('Content-Type: application/json');

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

// Get input data
$input = json_decode(file_get_contents("php://input"), true);

// Route based on method
switch($method) {

    // ------------------------------
    // GET - Retrieve all users
    // ------------------------------
    case 'GET':
        $sql = "SELECT * FROM testing";
        $result = $conn->query($sql);
        $data = [];
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        echo json_encode($data);
        break;

    // ------------------------------
    // POST - Insert new user
    // ------------------------------
    case 'POST':
        if(isset($input['Name']) && isset($input['age'])) {
            $name = $conn->real_escape_string($input['Name']);
            $age = $conn->real_escape_string($input['age']);
            $sql = "INSERT INTO testing (Name, age) VALUES ('$name', '$age')";
            if($conn->query($sql)) {
                echo json_encode(["success" => true, "id" => $conn->insert_id]);
            } else {
                echo json_encode(["success" => false, "error" => $conn->error]);
            }
        } else {
            echo json_encode(["success" => false, "error" => "Name and age required"]);
        }
        break;

    // ------------------------------
    // PUT / PATCH - Update user
    // ------------------------------
    case 'PUT':
    case 'PATCH':
        if(isset($input['ID'])) {
            $id = (int)$input['ID'];
            $fields = [];
            if(isset($input['Name'])) $fields[] = "Name='" . $conn->real_escape_string($input['Name']) . "'";
            if(isset($input['age'])) $fields[] = "age='" . $conn->real_escape_string($input['age']) . "'";
            if(!empty($fields)) {
                $sql = "UPDATE testing SET " . implode(",", $fields) . " WHERE ID=$id";
                if($conn->query($sql)) {
                    echo json_encode(["success" => true, "updated_id" => $id]);
                } else {
                    echo json_encode(["success" => false, "error" => $conn->error]);
                }
            } else {
                echo json_encode(["success" => false, "error" => "No fields to update"]);
            }
        } else {
            echo json_encode(["success" => false, "error" => "ID required"]);
        }
        break;

    // ------------------------------
    // DELETE - Remove user
    // ------------------------------
    case 'DELETE':
        if(isset($_GET['ID'])) {
            $id = (int)$_GET['ID'];
            $sql = "DELETE FROM testing WHERE ID=$id";
            if($conn->query($sql)) {
                echo json_encode(["success" => true, "deleted_id" => $id]);
            } else {
                echo json_encode(["success" => false, "error" => $conn->error]);
            }
        } else {
            echo json_encode(["success" => false, "error" => "ID required"]);
        }
        break;

    default:
        echo json_encode(["success" => false, "error" => "Unsupported method"]);
        break;
}

// Close connection
$conn->close();
?>
