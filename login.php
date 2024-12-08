<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

try {
    // Consultar si el usuario existe con el email y password
    $sql = 'SELECT * FROM usuarios WHERE email = :email AND password = :password';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':password', $data['password']);
    
    if ($stmt->execute()) {
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Verificar si se encontraron resultados
        if (!empty($result)) {
            $authStatus = 'logged_in';
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
                'idUser' => $userData['id'],
                'idRol' => $userData['idRol'],
                'auth_status' => 'logged_in' // Puedes devolver el estado también
            ]);
        } else {
            // Si no se encontraron resultados, devolver un mensaje de error
            echo json_encode([
                'success' => false,
                'message' => 'Credenciales incorrectas. Verifica tu email o contraseña.'
            ]);
        }
    }
} catch (PDOException $e) {
    // Si ocurre un error, devolver el mensaje de error como JSON
    echo json_encode([
        'success' => false,
        'message' => 'Error en el login: ' . $e->getMessage()
    ]);
}
?>
