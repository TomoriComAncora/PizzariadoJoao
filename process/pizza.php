<?php
    include_once("conn.php");

    $method = $_SERVER["REQUEST_METHOD"];

    //RESGATAR DADOS, MONTAGEM DOS PEDIDOS
    if($method === "GET"){

        $bordasQuerry = $conn->query("SELECT * FROM bordas;");
        $bordas = $bordasQuerry->fetchAll();

        $massasQuerry = $conn->query("SELECT * FROM massas;");
        $massas = $massasQuerry->fetchAll();

        $saboresQuerry = $conn->query("SELECT * FROM sabores;");
        $sabores = $saboresQuerry->fetchAll();

    //CRIAÇÃO DO PEDIDO    
    }else if($method === "POST"){
        $data = $_POST;
        $borda = $data["borda"];
        $massa = $data["massa"];
        $sabores = $data["sabores"];

        //VALIR O MÁXIMO DE SABORES
        if(count($sabores) > 3){
            $_SESSION["msg"] = "Selecione no máximo 3 sabores!";
            $_SESSION["status"] = "warning";

        }else{
            //SALVANDO BORDA E MASSA NA PIZZA
            $stmt = $conn->prepare("INSERT INTO pizzas (borda_id, massa_id) VALUES (:borda, :massa);");

            //FILTRANDO INPUTS
            $stmt->bindParam(":borda", $borda, PDO::PARAM_INT);
            $stmt->bindParam(":massa", $massa, PDO::PARAM_INT);

            $stmt->execute();

            //RESGATE DO ÚLTIMO ID DA ÚLTIMA PIZZA
            $pizzaId = $conn->lastInsertId();

            $stmt = $conn->prepare("INSERT INTO pizza_sabor (pizza_id, sabore_id) VALUES (:pizza, :sabor);");

            //REPETIÇAÕ ATÉ TERMIINAR DE SALVQAR TODOS OS SABORES
            foreach($sabores as $sabor){
                $stmt->bindParam(":pizza", $pizzaId, PDO::PARAM_INT);
                $stmt->bindParam(":sabor", $sabor, PDO::PARAM_INT);

                $stmt->execute();
            }

            //CRIAR PEDIDO
            $stmt = $conn->prepare("INSERT INTO pedidos (pizza_id, status_id) VALUES (:pizza, :status);");

            //STATUS SEMPRE COMEÇA COM 1
            $statusId = 1;

            //FILTRO DE INPUTS
            $stmt->bindParam(":pizza", $pizzaId);
            $stmt->bindParam(":status", $statusId);

            $stmt->execute();

            //RETORNAR MENSAGEM DE SUCESSO PARA USUÁRIO
            $_SESSION["msg"] = "Pedido realizado com sucesso!";
            $_SESSION["status"] = "success";


        }

        //RETORNA PARA PÁGINA INICIAL
        header("Location: ..");
    }
?>