<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *"); // Permitir el acceso desde cualquier origen (ajusta esto si necesitas restringirlo)
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
include_once 'conexion.php';


$data = json_decode(file_get_contents("php://input"), true);

function inscripcionCurso($data)
{
    global $pdo;
    $fecha = date('Y-m-d');
    $trueInscripcion = 1;
    try {
        $sql = "INSERT INTO inscripciones (idCurso,idEstudiante,fecha_inscripcion,inscripto)
        VALUES  (:idCurso,:idEstudiante,:fecha,:inscripto)";
        $stmt = $pdo->prepare($sql);

        $stmt->bindParam(':idCurso', $data['idCurso']);
        $stmt->bindParam(':idEstudiante', $data['idUser']);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':inscripto', $trueInscripcion);

        $stmt->execute();

        echo json_encode([
            'success' => true,
            'menssage' => 'incripcion exitosa'
        ]);
    } catch (PDOException $e) {
        // Si ocurre un error, devolver el mensaje de error como JSON
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al inscribirse: ' . $e->getMessage()
        ]);
    }
}

function bajaCurso($data)
{
    global $pdo;

    try {
        // Eliminar el registro de la inscripción en la tabla inscripciones
        $sql = "DELETE FROM inscripciones WHERE idCurso = :idCurso AND idEstudiante = :idEstudiante";
        $stmt = $pdo->prepare($sql);

        // Vinculamos los parámetros
        $stmt->bindParam(':idCurso', $data['idCurso']);
        $stmt->bindParam(':idEstudiante', $data['idUser']);

        // Ejecutamos la consulta
        $stmt->execute();

        // Respuesta JSON en caso de éxito
        echo json_encode([
            'success' => true,
            'message' => 'El curso fue cancelado y el registro eliminado correctamente.'
        ]);
    } catch (PDOException $e) {
        // Manejo de errores
        echo json_encode([
            'success' => false,
            'message' => 'Error al cancelar el curso: ' . $e->getMessage()
        ]);
    }
}


function mostrarMisCursos($data)
{

    global $pdo;
    $inscripto = 1;
    $sql = "SELECT *, 
       c.nombre AS nombreCurso, 
       p.nombre AS nombreProfe, 
       p.apellido AS apelliProfe
    FROM inscripciones AS i
    INNER JOIN cursos AS c ON i.idCurso = c.id
    INNER JOIN modalidadcurso AS mc ON mc.id = c.tipo
    LEFT JOIN cursoprofe AS cp ON cp.idCurso = c.id
    LEFT JOIN profesores AS p ON p.id = cp.idProfes
    where i.idEstudiante = :idSeleccionado and i.inscripto = :inscripto";

    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':idSeleccionado', $data['idUser']);
    $stmt->bindParam(':inscripto', $inscripto);

    $stmt->execute();

    $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return json_encode($response);
}

if (isset($data['action'])) {
    $action = $data['action'];

    switch ($action) {
        case 'incribirse':
            echo inscripcionCurso($data);
            break;
        case 'baja':
            echo bajaCurso($data);
            break;
        case 'misCursos':
            echo mostrarMisCursos($data);
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
