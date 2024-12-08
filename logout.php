<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

try {
    // Consultar si el usuario existe con el email y password
    $sql = 'SELECT * FROM usuarios WHERE id=:id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $data['id']);
   
    
    if ($stmt->execute()) {
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Verificar si se encontraron resultados
        if (!empty($result)) {
            $authStatus = 'logged_out';
            // Acceder al primer resultado (asumiendo que solo hay un resultado)
            $userData = $result[0];

            // Actualizar el campo auth_status a 'logged_in'
            $updateSql = 'UPDATE usuarios SET auth_status = :auth_status WHERE id = :id';
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->bindParam(':auth_status',$authStatus );
            $updateStmt->bindParam(':id', $userData['id']);
            $updateStmt->execute();

            // Devolver la respuesta con el ID y rol del usuario
            echo json_encode([
                'success' => true,
                'auth_status' => 'logged_out' // Puedes devolver el estado también
            ]);
        } 
    }
} catch (PDOException $e) {
    // Si ocurre un error, devolver el mensaje de error como JSON
    echo json_encode([
        'success' => false,
        'message' => 'Error en el logout: ' . $e->getMessage()
    ]);
}
?>
