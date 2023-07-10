<?php
    include_once("conn.php");

    $method = $_SERVER["REQUEST_METHOD"];

    if($method === "GET"){
        $pedidosQuerry = $conn->query("SELECT * FROM pedidos;");

        $pedidos = $pedidosQuerry->fetchAll();

        $pizzas = [];

        //MONTANDO A PIZZA
        foreach($pedidos as $pedido){
            $pizza = [];

            //DEFINIR UM ARRAY PARA A PIZZA
            $pizza["id"] = $pedido["pizza_id"];

            //RESGATANDO A PIZZA
            $pizzaQuerry = $conn->prepare("SELECT * FROM pizzas WHERE id = :pizza_id;");

            $pizzaQuerry->bindParam(":pizza_id", $pizza["id"]);

            $pizzaQuerry->execute();

            $pizzaData = $pizzaQuerry->fetch(PDO::FETCH_ASSOC);

            //RESGATANDO BORDA
            $bordaQuerry = $conn->prepare("SELECT * FROM bordas WHERE id = :borda_id;");

            $bordaQuerry->bindParam(":borda_id", $pizzaData["borda_id"]);

            $bordaQuerry->execute();

            $borda = $bordaQuerry->fetch(PDO::FETCH_ASSOC);

            $pizza["borda"] = $borda["tipo"];

            //RESGATANDO MASSA
            $massaQuerry = $conn->prepare("SELECT * FROM massas WHERE id = :massa_id;");

            $massaQuerry->bindParam(":massa_id", $pizzaData["massa_id"]);

            $massaQuerry->execute();

            $massa = $massaQuerry->fetch(PDO::FETCH_ASSOC);

            $pizza["massa"] = $massa["tipo"];

            //RESGATANDO OS SABORES
            $saboresQuerry = $conn->prepare("SELECT * FROM pizza_sabor WHERE pizza_id = :pizza_id;");

            $saboresQuerry->bindParam(":pizza_id", $pizza["id"]);

            $saboresQuerry->execute();

            $sabores = $saboresQuerry->fetchAll(PDO::FETCH_ASSOC);

            //RESGATANDO OS SABORES
            $saboresDaPizza = [];

            $saborQuerry = $conn->prepare("SELECT * FROM sabores WHERE id = :sabore_id;");

            foreach($sabores as $sabor){
                $saborQuerry->bindParam(":sabore_id", $sabor["sabore_id"]);
                $saborQuerry->execute();
                $saborPizza = $saborQuerry->fetch(PDO::FETCH_ASSOC);

                array_push($saboresDaPizza, $saborPizza["tipo"]);
            }

            $pizza["sabores"] = $saboresDaPizza;

            //ADICIONANDO SATUS AO PEDIDO
            $pizza["status"] = $pedido["status_id"];

            //ADICIONANDO ARRAY DE PIZZA, AO ARRAY DAS PIZZAS
            array_push($pizzas, $pizza);

        }
        //RESGATANDO OS STATUS
        $statusQuerry = $conn->query("SELECT * FROM status;");

        $status = $statusQuerry->fetchAll();

    }else if($method === "POST"){
        //VERIFICAR O PEDIDO
        $type = $_POST["type"];

        //DELETAR PEDIDO
        if($type === "delete"){
            $pizzaId = $_POST["id"];

            $deleteQuerry = $conn->prepare("DELETE FROM pedidos WHERE pizza_id = :pizza_id;");
            $deleteQuerry->bindParam(":pizza_id", $pizzaId, PDO::PARAM_INT);
            $deleteQuerry->execute();

            $_SESSION["msg"] = "Pedido removido com sucesso!";
            $_SESSION["status"] = "success";

            
        }else if($type === "update"){
            $pizzaId = $_POST["id"];
            $statusId = $_POST["status"];

            $updateQuerry = $conn->prepare("UPDATE pedidos SET status_id = :status_id WHERE pizza_id = :pizza_id");

            $updateQuerry->bindParam(":pizza_id", $pizzaId, PDO::PARAM_INT);
            $updateQuerry->bindParam(":status_id", $statusId, PDO::PARAM_INT);

            $updateQuerry->execute();

            $_SESSION["msg"] = "Pedido atualizado com sucesso!";
            $_SESSION["status"] = "success";
        }
        //RETORNANDO PARA DASHBOARD
        header("Location: ../dashboard.php");
    }
?>