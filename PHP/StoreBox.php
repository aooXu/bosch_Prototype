<?php
header("Content-type:text/html;charset=utf-8");
/**
 * Created by PhpStorm.
 * User: aoo
 * Date: 10/7/2018
 * Time: 1:16 PM
 */

/*Import and Declaration*/
include_once 'DBHandler.php';
header('Access-Control-Allow-Origin: *');

/*Initiate the parameters*/
$boxID = $_REQUEST["boxID"];
$warehouseID = $_REQUEST["warehouseID"];
$storageArea = $_REQUEST["storageArea"];

/*Check If parameters are all satisfied*/
if(empty($boxID)||empty($warehouseID)||empty($storageArea)){
    outputResult("401","NULL PARAMETERS");
    return;
}

/*Check if the box has already used for storing*/
$dbForWarehouse = new DBHandler(DBHandler::DATABASE_WAREHOUSES);
try {
    $dbForWarehouse->startQuery();
    $existResult = $dbForWarehouse->exeQuery(
        "SELECT boxID FROM wh$warehouseID 
         WHERE boxID=?",
        array($boxID)
    );
    $dbForWarehouse->commitQuery();

    if ($existResult->rowCount()!=0){
        outputResult("403","BOX HAS ALREADY USED");
        return;
    }
} catch (PDOException $e) {
    outputResult("402","ERROR IN QUERY".$e->getMessage());
    $dbForWarehouse->abortQuery();
    return;
}

$dbForBasic = new DBHandler(DBHandler::DATABASE_BASIC);
try{
    $dbForBasic->startQuery();
    $dbForWarehouse->startQuery();

    $dbForWarehouse->exeQuery(
        "INSERT INTO wh$warehouseID(boxID, stateCode, areaCode, groupCode) 
         VALUE(?, ?, ?, ?)",
        array($boxID,"STORE", $storageArea, 0)
    );

    $dbForBasic->exeQuery(
        "UPDATE boxestable SET stateCode='STORE' AND warehouseID=? WHERE boxID=?",
        array($warehouseID, $boxID)
    );

    $dbForBasic->commitQuery();
    $dbForWarehouse->commitQuery();

}catch(PDOException $e){
    $dbForBasic->abortQuery();
    $dbForWarehouse->abortQuery();
    outputResult("404","ERROR IN UPDATE ".$e->getMessage());
    return;
}

outputResult("100","SUCCESS");

function outputResult($code, $result){
    echo(json_encode(array("code"=>$code, "result"=>$result)));
}



