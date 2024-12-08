<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); // Permitir el acceso desde cualquier origen (ajusta esto si necesitas restringirlo)
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
include_once 'conexion.php';



$data = json_decode(file_get_contents("php://input"), true);


$fecha=date('Y-m-d');
// funcion crear usuarios
function crearCurso($data) {
    global $pdo;
    $fecha=date('Y-m-d');
  
    try {
        // Preparar la consulta de inserción
        $sql = "INSERT INTO cursos (nombre,tipo,periodo,docente,horario,descripcion, fechaAlta) 
                VALUES (:nombre, :tipo, :periodo, :docente, :horario, :descripcion,:fechaAlta)";
        $stmt = $pdo->prepare($sql);

        // Vincular los parámetros con los valores del array $data
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':tipo', $data['tipo']);
        $stmt->bindParam(':periodo', $data['periodo']);
        $stmt->bindParam(':docente', $data['docente']);
        $stmt->bindParam(':horario', $data['horario']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':fechaAlta', $fecha);

        // Ejecutar la consulta
        $stmt->execute();

        // Obtener el último ID insertado
        $lastId = $pdo->lastInsertId();

        // Si se inserta correctamente, devolver el ID como respuesta JSON
        echo json_encode([
            'success' => true,
            'message' => 'curso creado exitosamente',
            'lastId' => $lastId
        ]);

    } catch (PDOException $e) {
        // Si ocurre un error, devolver el mensaje de error como JSON
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al crear curso: ' . $e->getMessage()
        ]);
    }


}

// Function obtener user
function obtener() {

    global $pdo;

    $sql = "SELECT 
                c.*, 
                p.nombre AS nombreProfe, 
                p.apellido AS apellidoProfe, 
                mc.nombreTipo
            FROM cursos AS c
            INNER JOIN modalidadcurso AS mc ON mc.id = c.tipo
            LEFT JOIN cursoprofe AS cp ON cp.idCurso = c.id
            LEFT JOIN profesores AS p ON p.id = cp.idProfes";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
   
    return json_encode($users);
    

    
}

// Function actualizar 
function actualizarCurso($data) {
    global $pdo;
    $fecha = date('Y-m-d');
    $sql = 'UPDATE cursos SET nombre = :nombre, tipo = :tipo, periodo = :periodo, docente = :docente, horario = :horario, descripcion = :descripcion, fechaModificacion = :fechaModificacion WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $data['id']);
    $stmt->bindParam(':nombre', $data['nombre']);
    $stmt->bindParam(':tipo', $data['tipo']);
    $stmt->bindParam(':periodo', $data['periodo']);
    $stmt->bindParam(':docente', $data['docente']);
    $stmt->bindParam(':horario', $data['horario']);
    $stmt->bindParam(':descripcion', $data['descripcion']);
    $stmt->bindParam(':fechaModificacion', $fecha);
    $stmt->execute();
    echo json_encode([
        'success' => true,
        'message' => 'Actualizado correctamente!'
    ]);
}




// Function to delete a course
function eliminarCurso($id) {
    global $pdo;

    try {
        // Primero elimina las dependencias en la tabla cursoprofe
        $sqlDeleteCursoprofe = "DELETE FROM cursoprofe WHERE idCurso = :id";
        $stmt = $pdo->prepare($sqlDeleteCursoprofe);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Luego elimina el curso
        $sqlDeleteCurso = "DELETE FROM cursos WHERE id = :id";
        $stmt = $pdo->prepare($sqlDeleteCurso);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Respuesta en JSON
        echo json_encode([
            'success' => true,
            'message' => 'Curso eliminado correctamente!'
        ]);
    } catch (PDOException $e) {
        // Manejo de errores
        echo json_encode([
            'success' => false,
            'message' => 'Error al eliminar curso: ' . $e->getMessage()
        ]);
    }
}


if (isset($data['action'])) {
    $action = $data['action'];
    
    switch ($action) {
        case 'crear':
            echo crearCurso($data);
            break;
        case 'obtener':
            echo obtener();
            break;
        case 'editar':
            echo actualizarCurso($data);
            break;
        case 'borrar':
            $id = $data['id'];
            echo eliminarCurso($id);
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








