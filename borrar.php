<?php


/**
 * Elimina en una transacción una tienda de la tabla tiendas. Antes de eliminar la tienda, incorpora el stock existente a la tienda cuyo id es TIENDA_CENTRAL_ID.
 * Si la tienda TIENDA_CENTRAL_ID ya tenía ese producto, se suman las unidades existentes en la tienda a eliminar.
 * Si la tienda TIENDA_CENTRAL_ID no tenía ese producto, se inserta un nuevo registro para la tienda TIENDA_CENTRAL_ID con el producto y las unidades de la tienda a eliminar
 * @param int $id id de la tienda a eliminar
 * @return bool true en caso de éxito, false en caso contrario
 */
function eliminar_tienda(int $id): bool
{
    $exito = false;


    try {
        $conProyecto = PDOSingleton::getInstance();
        //Tenemos que modificar la misma tabla varias veces
        //Comenzamos la tx
        $conProyecto->beginTransaction();


        $select_all_stocks_by_tienda_id = "select * from stocks where tienda = ?";

        $stmt_select_all = $conProyecto->prepare($select_all_stocks_by_tienda_id);

        //Ejecutamos y reemplazamos los parámetros con un array
        $exito = $stmt_select_all->execute([$id]);
        //Obtenemos todos los registros
        $registros_tienda = $stmt_select_all->fetchAll(PDO::FETCH_ASSOC);

        foreach ($registros_tienda as $registro) {
            //obtenemos el producto de la tienda que será eliminada
            $producto_id = $registro["producto"];
            //obtenemos las unidades de producto de la tienda que será eliminada
            $unidades = $registro["unidades"];

            if (existeProducto($producto_id, TIENDA_CENTRAL_ID)) {
                updateStock($producto_id, TIENDA_CENTRAL_ID, $unidades);

            } else {
                insertStock($producto_id, TIENDA_CENTRAL_ID, $unidades);
            }
        }

        //Finalmente estamos listos para eliminar todos los registros de una tienda.
        $delete = "delete from tiendas where id= ?";
        $delete_stmt = $conProyecto->prepare($delete);
        $delete_stmt->execute([$id]);


        $exito = $conProyecto->commit();
        return $exito;

    } catch (Exception $ex) {
        $conProyecto->rollBack();
        $exito = false;
        echo "Ocurrió un error al eliminar la tienda con $id, mensaje: " . $ex->getMessage();
    }

    //Devolvemos el resultado de la operación
    return $exito;
}

/**
 * Comprueba si existe en la tabla stocks un producto en una tienda dada
 * @param int $producto_id id del producto
 * @param int $tienda_id id de la tienda
 * @return bool true si existe, false en caso contrario
 */
function existeProducto(int $producto_id, int $tienda_id)
{
    $conProyecto = PDOSingleton::getInstance();
    $select = "select 1 AS resultado from stocks where producto = ? and tienda = ?";
    $stmt = $conProyecto->prepare($select);
    $stmt->bindParam(1, $producto_id);
    $stmt->bindParam(2, $tienda_id);

    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return ($result !== false);


}
/**
 * Actualiza las unidades de stock de un producto en una tienda. 
 * @param int $producto_id id del producto
 * @param int $tienda_id id de la tienda
 * @param int $unidades unidades en las que se incrementa el stock actual.
 * @return bool true si se ha actualizado correctamente, false en caso contrario
 */
function updateStock(int $producto_id, int $tienda_id, int $unidades)
{
    $conProyecto = PDOSingleton::getInstance();
    $update = "update stocks set unidades = (unidades + ?) where producto = ? and tienda = ?";
    $stmt = $conProyecto->prepare($update);
    $stmt->bindParam(1, $unidades);
    $stmt->bindParam(2, $producto_id);
    $stmt->bindParam(3, $tienda_id);

    return $stmt->execute();



}
/**
 * Inserta un registro en tabla stocks
 * @param int $producto_id id del producto
 * @param int $tienda_id id de la tienda
 * @param int $unidades unidades de stock
 * @return bool true si se ha insertado correctamente, false en caso contrario
 */
function insertStock(int $producto_id, int $tienda_id, int $unidades)
{
    $conProyecto = PDOSingleton::getInstance();
    $update = "insert into stocks(producto, tienda, unidades) values (?, ?, ?)";
    $stmt = $conProyecto->prepare($update);
    $stmt->bindParam(1, $producto_id);
    $stmt->bindParam(2, $tienda_id);
    $stmt->bindParam(3, $unidades);

    return $stmt->execute();



}
