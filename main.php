<?php
 const HOST = 'mysql:host=localhost;dbname=inventory_management';
const DBNAME = 'inventory_management';
const USER = 'root';
const PASS = 'ridanridan';

try {
 $db = new PDO(HOST, USER, PASS);
 $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch (Exception $e) {
 die("Не удалось подключиться: " . $e->getMessage());
}


if (isset($_SERVER['argv'][1])) {

    array_shift($_SERVER['argv']);
    $orderId = implode(',', $_SERVER['argv']);
    echo("Страница сборки заказов $orderId \n\n");

} else {
    die ("id заказов не были переданы.");
}



$stmt = $db->prepare("SELECT
    s.name AS shelve,
    p.name AS product,
    p.id AS product_Id,
    o.id AS order_id,
    op.count AS product_count,
    (
        SELECT GROUP_CONCAT(s_additional.name)
        FROM shelves s_additional
        JOIN shelves_products sp_additional ON s_additional.id = sp_additional.id_shelve
        WHERE sp_additional.id_product = p.id AND sp_additional.is_main = 0
    ) AS additional_shelves
    FROM
        orders o
    JOIN
        order_products op ON o.id = op.id_order
    JOIN
        products p ON op.id_product = p.id
    JOIN
        shelves_products sp ON op.id_product = sp.id_product
    JOIN
        shelves s ON sp.id_shelve = s.id AND sp.is_main = 1
    WHERE o.id IN ($orderId)
    ORDER BY
s.name, o.id");

$stmt->execute();


while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $data[$row['shelve']][] = $row;
}

foreach ($data AS $key => $value) {
    echo ('===Стеллаж '.$key."\n");
    foreach ($value AS $key => $value) {
        echo($value['product'].' (id='.$value['product_Id'].")\n");
        echo('заказ '. $value['order_id'].', '.$value['product_count']." шт\n");
        if ($value['additional_shelves'] ) {
            echo('доп стеллаж: '.$value['additional_shelves']."\n");
        }
        echo"\n\n";
    }
}







?>


