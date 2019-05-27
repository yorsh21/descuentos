<?php
    include "../config.php";
    include "../utils.php";
    $db =  connect($db);

    /*
      listar todos los productos o solo uno
     */
    if ($_SERVER['REQUEST_METHOD'] == 'GET')
    {
        if (isset($_GET['id']))
        {
          //Mostrar un post
          $sql = $db->prepare("SELECT * FROM products where id=:id");
          $sql->bindValue(':id', $_GET['id']);
          $sql->execute();
          header("HTTP/1.1 200 OK");
          echo json_encode($sql->fetch(PDO::FETCH_ASSOC));
          exit();
        }
        else 
        {
            //Cantidad total de elementos
            $sql = $db->prepare("SELECT COUNT(*) as count FROM products");
            $sql->execute();
            $sql->setFetchMode(PDO::FETCH_ASSOC);
            $total = $sql->fetchAll()[0]["count"];

            $adds = "";

            if (isset($_GET['discount'])) {
                $adds .= " where discount >= " . $_GET['discount'];
            }

            if (isset($_GET['order'])) {
                $adds .= " ORDER BY " . $_GET['order'];

                if (isset($_GET['by'])) {
                    $adds .= " " . $_GET['by'];
                }
            }


            if (isset($_GET['limit'])) {
                $adds .= " LIMIT " . $_GET['limit'];

                if (isset($_GET['offset'])) {
                    $adds .= " OFFSET " . $_GET['offset'];
                }
            }

            //Mostrar lista de productos
            $sql = $db->prepare("SELECT * FROM products" . $adds);
            $sql->execute();
            $sql->setFetchMode(PDO::FETCH_ASSOC);
            header("HTTP/1.1 200 OK");
            $result = $sql->fetchAll();

            $response = [
                "total" => $total,
                "count" => count($result),
                "products" => $result
            ];

            echo json_encode($response);
            exit();
      }
    }

    // Crear un nuevo producto
    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $input = $_REQUEST;

        //Verifica si esta el producto
        $sql = $db->prepare("SELECT COUNT(*) as count FROM products WHERE code=" . $input["code"]);
        $sql->execute();
        $sql->setFetchMode(PDO::FETCH_ASSOC);

        if($sql->fetchAll()[0]["count"] == 0) 
        {
            $input['created_at'] = $input['datetime'];
            $input['updated_at'] = $input['datetime'];
            unset($input['datetime']);

            $sql = "INSERT INTO products
               (code, name, url, image, discount, previous_price, offer_price, created_at, updated_at)
               VALUES
               (:code, :name, :url, :image, :discount, :previous_price, :offer_price, :created_at, :updated_at)";
            $statement = $db->prepare($sql);
            bindAllValues($statement, $input);
            $statement->execute();
            $postId = $db->lastInsertId();

            if($postId)
            {
             $input['id'] = $postId;
             header("HTTP/1.1 200 OK");
             echo json_encode($input);
             exit();
            }
        }
        else 
        {
            $input['updated_at'] = $input['datetime'];
            unset($input[array_search('datetime',$input)]);

            $fields = getParams($input);
            $sql = "
                UPDATE products
                SET $fields
                WHERE code='" . $input["code"] . "'";
            $statement = $db->prepare($sql);
            bindAllValues($statement, $input);
            $statement->execute();
        }
    }

    //Borrar producto
    if ($_SERVER['REQUEST_METHOD'] == 'DELETE')
    {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            $statement = $db->prepare("DELETE FROM products where id=:id");
            $statement->bindValue(':id', $id);
            $statement->execute();
            header("HTTP/1.1 200 OK");
            exit();
        }
        else if(isset($_GET['deleteallproducts'])) {
            $statement = $db->prepare("DELETE FROM products; ALTER TABLE products AUTO_INCREMENT = 1;");
            $statement->execute();
            header("HTTP/1.1 200 OK");
            exit();
        }
    }

    //Actualizar producto
    if ($_SERVER['REQUEST_METHOD'] == 'PUT')
    {
        $input = $_GET;
        $postId = $input['id'];
        $fields = getParams($input);
        $sql = "
            UPDATE products
            SET $fields
            WHERE id='$postId'
        ";
        $statement = $db->prepare($sql);
        bindAllValues($statement, $input);
        $statement->execute();
        header("HTTP/1.1 200 OK");
        exit();
    }

    //En caso de que ninguna de las opciones anteriores se haya ejecutado
    header("HTTP/1.1 400 Bad Request");
?>
