<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); // Permitir el acceso desde cualquier origen (ajusta esto si necesitas restringirlo)
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
include_once 'conexion.php';



$data = json_decode(file_get_contents("php://input"), true);


$fecha=date('Y-m-d');
// funcion crear usuarios
function crearUsuario($data) {
    global $pdo;
    $fecha=date('Y-m-d');
  
    try {
        // Preparar la consulta de inserción
        $sql = "INSERT INTO usuarios (nombre, apellido, email, password, idRol, fechaAlta) 
                VALUES (:nombre, :apellido, :email, :password, :rol, :fechaAlta)";
        $stmt = $pdo->prepare($sql);

        // Vincular los parámetros con los valores del array $data
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':apellido', $data['apellido']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':password', $data['password']);
        $stmt->bindParam(':rol', $data['rol']);
        $stmt->bindParam(':fechaAlta', $fecha);

        // Ejecutar la consulta
        $stmt->execute();

        // Obtener el último ID insertado
        $lastId = $pdo->lastInsertId();

        // Si se inserta correctamente, devolver el ID como respuesta JSON
        echo json_encode([
            'success' => true,
            'message' => 'Usuario creado exitosamente',
            'lastId' => $lastId
        ]);

    } catch (PDOException $e) {
        // Si ocurre un error, devolver el mensaje de error como JSON
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al crear usuario: ' . $e->getMessage()
        ]);
    }


}

// Function obtener user
function obtener() {
    global $pdo;
    $sql = "SELECT * FROM usuarios";
    $stmt=$pdo->prepare($sql);
    $stmt->execute();

    $users=$stmt->fetchAll(PDO::FETCH_ASSOC);
   
    return json_encode($users);
}

// Function actualizar 
function actualizarUser($data) {
    global $pdo;
    $fecha=date('Y-m-d');
    $sql = 'UPDATE usuarios SET nombre = :nombre, apellido=:apellido,email = :email, fechaModificacion=:fechaModificacion  WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $data['id']);
    $stmt->bindParam(':nombre', $data['nombre']);
    $stmt->bindParam(':apellido', $data['apellido']);
    $stmt->bindParam(':email', $data['email']);
    $stmt->bindParam(':fechaModificacion', $fecha);
    $stmt->execute();
    echo json_encode([
        'success' => true,
        'message' => 'Actualizadoo correctamente!'
    ]);
}

// Function to delete a user
function eliminarUser($id) {
    global $pdo;
    $sql = "DELETE FROM usuarios WHERE id = $id";
    $stmt=$pdo->prepare($sql);
    $stmt->execute();
    echo json_encode([
        'success' => true,
        'message' => 'eliminado correctamente!'
    ]);
}


if (isset($data['action'])) {
    $action = $data['action'];
    
    switch ($action) {
        case 'crear':
            echo crearUsuario($data);
            break;
        case 'obtener':
            echo obtener();
            break;
        case 'editar':
            echo actualizarUser($data);
            break;
        case 'borrar':
            $id = $data['id'];
            echo eliminarUser($id);
            break;
        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Acción inválida!'
            ]);
            break;
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No se envió ninguna acción!'
    ]);
}


?>








