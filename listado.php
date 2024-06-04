<?php
const TIENDA_CENTRAL_ID = 1;

require_once 'PDOSingleton.php';
require_once 'borrar.php';



$tienda_borrada = false;
$cod = null;

if (isset($_POST["id"], $_POST["borrar"])) {
    $cod = $_POST["id"];
    $tienda_borrada = eliminar_tienda($cod);
}

$tiendas_array = obtener_tiendas();



/**
 * Summary of obtener_tiendas
 *  Consulta la tabla tiendas para obtener las columnas id, nombre y tlf ordenadas por nombre
 * @return array array con tantos registros como tuplas haya y por cada registro un array con 3 claves (una por columna)
 */
function obtener_tiendas(): array
{

    $tiendas_array = null;
    try {
        $conProyecto = PDOSingleton::getInstance();
        $consulta = "select id, nombre, tlf from tiendas order by nombre";
        $stmt = $conProyecto->prepare($consulta);

        $stmt->execute();
        $tiendas_array = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $ex) {
        die("Error al recuperar las tiendas " . $ex->getMessage());
    }


    return $tiendas_array;
}



/**
 * Crea una fila html mostrando en cada celda de datos id,nombre y tlf. Además añade un botón en un formulario para permitir su eliminación si el id de la tienda es diferente del de la constante TIENDA_CENTRAL_ID .
 * @param array $tiendas_array un array asociativo con 3 claves  id, nombre y tlf
 * @return void
 */
function mostrar_tiendas(array $tiendas_array)
{
    foreach ($tiendas_array as $fila) {
        echo "<tr class='text-center'><th scope='row'>";

        echo "<td>{$fila['id']}</td>";
        echo "<td>{$fila['nombre']}</td>";
        echo "<td>{$fila['tlf']}</td>";
        echo "<td>";
        if ($fila['id'] !== TIENDA_CENTRAL_ID) {
            echo "<form   method='POST'
style='display:inline'>";

            echo "<input type='hidden' name='id' value='{$fila['id']}'>"; //enviamos el código de la tienda

            echo "<input type='submit' name='borrar' onclick=\"return confirm('¿Borrar Tienda?')\"class='btn btn-danger' value='Borrar'>";
            echo "</form>";
        }
        echo "</td>";
        echo "</tr>";
    }
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-
scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <!-- css para usar Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <title>Recuperación UD4</title>
</head>

<body>
    <h3 class="text-center mt-2 font-weight-bold">Listado de tiendas</h3>
    <div class="container mt-3">
        
        <table class="table table-striped table-light">
            <thead>
                <tr class="text-center">
                    <th scope="col"></th>
                    <th scope="col">Id</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Teléfono</th>
                    <th></th>

                </tr>
            </thead>
            <tbody>
                <?php
                mostrar_tiendas($tiendas_array);

                ?>
            </tbody>
        </table>

        <?php
        if (isset($_POST["id"], $_POST["borrar"])) {
            if ($tienda_borrada) {
                echo '<div class="alert alert-success" role="alert">   Tienda con id: ' . $cod . ' eliminada correctamente.</div>';
            } else {
                echo '<div class="alert alert-danger" role="alert">   No se ha podido eliminar la tienda con el id:' . $cod . '  </div>';
            }
        }

        ?>
    </div>
</body>

</html>