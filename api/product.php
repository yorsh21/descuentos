<?php
    include "../config.php";
    include "../utils.php";
    $db =  connect($db);

    /*
      listar todos los posts o solo uno
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
          echo json_encode(  $sql->fetch(PDO::FETCH_ASSOC)  );
          exit();
        }
        else {
            $adds = "";

            if (isset($_GET['discount'])) {
                $adds .= " where productPercentage >= " . $_GET['discount'];
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


            //Mostrar lista de post
            $sql = $db->prepare("SELECT * FROM products" . $adds);
            $sql->execute();
            $sql->setFetchMode(PDO::FETCH_ASSOC);
            header("HTTP/1.1 200 OK");
            echo json_encode( $sql->fetchAll()  );
            exit();
      }
    }

    // Crear un nuevo post
    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $input = $_REQUEST;
        $input['datetime'] = date('Y-m-d H:i:s');

        $sql = "INSERT INTO products
              (productName, productLink, productImage, productPercentage, previousPrice, offerPrice, datetime)
              VALUES
              (:productName, :productLink, :productImage, :productPercentage, :previousPrice, :offerPrice, :datetime)";
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

    //Borrar
    if ($_SERVER['REQUEST_METHOD'] == 'DELETE')
    {
      $id = $_GET['id'];
      $statement = $db->prepare("DELETE FROM products where id=:id");
      $statement->bindValue(':id', $id);
      $statement->execute();
      header("HTTP/1.1 200 OK");
      exit();
    }

    //Actualizar
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