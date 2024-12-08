<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); // Permitir el acceso desde cualquier origen (ajusta esto si necesitas restringirlo)
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
include_once 'conexion.php';



$data = json_decode(file_get_contents("php://input"), true);



$fecha = date('Y-m-d');
// funcion crear usuarios
function crearProfesor($data)
{
    global $pdo;
    $fecha = date('Y-m-d');

    try {
        // Preparar la consulta de inserción
        $sql = "INSERT INTO profesores (nombre, apellido, email,fechaAlta) 
                VALUES (:nombre, :apellido, :email,:fechaAlta)";
        $stmt = $pdo->prepare($sql);

        // Vincular los parámetros con los valores del array $data
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':apellido', $data['apellido']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':fechaAlta', $fecha);

        // Ejecutar la consulta
        $stmt->execute();





        // Respuesta de éxito
        echo json_encode([
            'success' => true,
            'message' => 'Profesor creado exitosamente',

        ]);
    } catch (PDOException $e) {
        // Si ocurre un error, devolver el mensaje de error como JSON
        echo json_encode([
            'success' => false,
            'message' => 'Error al crear profesor: ' . $e->getMessage()
        ]);
    }
}

// Function obtener user
function obtener()
{
    global $pdo;
    $sql = "SELECT * FROM profesores";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return json_encode($users);
}

// Function actualizar 
function actualizarProfes($data)
{
    global $pdo;
    $fecha = date('Y-m-d');
    $sql = 'UPDATE profesores SET nombre = :nombre, apellido=:apellido, email = :email, fechaModificacion=:fechaModificacion  WHERE id = :id';
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


// Function to delete a professor
function eliminarProfes($id) {
    global $pdo;

    try {
        // Primero elimina las dependencias en la tabla cursoprofe
        $sqlDeleteCursoprofe = "DELETE FROM cursoprofe WHERE idProfes = :id";
        $stmt = $pdo->prepare($sqlDeleteCursoprofe);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Luego elimina el profesor
        $sqlDeleteProfesor = "DELETE FROM profesores WHERE id = :id";
        $stmt = $pdo->prepare($sqlDeleteProfesor);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Respuesta en JSON
        echo json_encode([
            'success' => true,
            'message' => 'Profesor eliminado correctamente!'
        ]);
    } catch (PDOException $e) {
        // Manejo de errores
        echo json_encode([
            'success' => false,
            'message' => 'Error al eliminar profesor: ' . $e->getMessage()
        ]);
    }
}

function ProfeCurso($idCurso, $idProfe)
{
    global $pdo;

    // Puedes usar $lastInsertId en otra consulta:
    $sql = "INSERT INTO cursoprofe (idCurso, idProfes) VALUES (:idCurso, :idProfes)";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':idCurso', $idCurso);  // Aseguramos que es un entero
    $stmt->bindParam(':idProfes', $idProfe);
    

    if($stmt->execute()){
        $query='UPDATE cursos SET docente= :docente where id=:id';
        $stmt=$pdo->prepare($query);
        $stmt->bindParam(':docente',$idProfes);
        $stmt->bindParam(':id',$idCurso);
        $stmt->execute();
    }

    echo json_encode([
        'success' => true,
        'message' => 'se asocio correctamente el curso!'
    ]);
}

if (isset($data['action'])) {
    $action = $data['action'];

    switch ($action) {
        case 'crear':
            echo crearProfesor($data);
            break;
        case 'obtener':
            echo obtener();
            break;
        case 'editar':
            echo actualizarProfes($data);
            break;
        case 'borrar':
            $id = $data['id'];
            echo eliminarProfes($id);
            break;
        case 'asignar':
           
            $idCurso = $data['formulario']['curso'];
            $idProfe=$data['formulario']['profesor'];

            echo ProfeCurso($idCurso,$idProfe);
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
