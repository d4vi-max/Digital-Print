<?php
include('../protect.php'); // Inclui a função de proteção ao acesso da página
require_once('../conexao.php');
$conexao = novaConexao();
$erro = false;

unset($_SESSION['origem']);

$sql_listaFunc = "SELECT * FROM funcionarios";
$stmt = $conexao->prepare($sql_listaFunc);
$stmt->execute();
$registroFunc = $stmt->fetchAll(PDO::FETCH_ASSOC);

try {
    $sql = "SELECT * FROM agenda";

    // Verifica se o formulário foi enviado para filtro de status
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filtraFunc'])) {
        $id = (int) $_POST['filtraFunc']; // Captura o valor do botão de filtro

        $sql = "SELECT * FROM agenda WHERE cod_func = $id";
        $stmt = $conexao->prepare($sql); // Passa o parâmetro corretamente
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos os registros
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filtraSTT'])) {
        $statusFiltro = $_POST['filtraSTT']; // Captura o valor do botão de filtro

        // Adiciona filtros baseados no botão clicado
        if ($statusFiltro === 'pendente') {

            $sql = "SELECT * FROM agenda WHERE status = 'pendente'";
            $stmt = $conexao->prepare($sql);
            $stmt->execute();
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else if ($statusFiltro === 'concluído') {

            $sql = "SELECT * FROM agenda WHERE status = 'concluído'";
            $stmt = $conexao->prepare($sql);
            $stmt->execute();
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    // Verifica se o formulário foi enviado para filtro de data
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filtraDT'])) {
        $dataFiltro = $_POST['filtraDT']; // Captura o valor do botão de filtro

        // Adiciona ordenação baseada no botão clicado
        if ($dataFiltro === 'menorPrazo') {

            $sql = "SELECT * FROM agenda ORDER BY dataPrazo ASC";
            $stmt = $conexao->prepare($sql);
            $stmt->execute();
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else if ($dataFiltro === 'maiorPrazo') {

            $sql = "SELECT * FROM agenda ORDER BY dataPrazo DESC";
            $stmt = $conexao->prepare($sql);
            $stmt->execute();
            $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }


    // Prepara a consulta SQL
    $stmt = $conexao->prepare($sql);

    // Executa a consulta
    $stmt->execute();
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos os registros com valor total

} catch (PDOException $e) {
    $erro = true; // Configura erro se houver uma exceção
    echo "Erro: " . $e->getMessage();
}

if (isset($_POST['delete'])) {
    $id = $_POST['codAgend'];

    // SQL para excluir a linha com base no ID
    $sql = "DELETE FROM agenda WHERE codAgend = :id";

    // Prepara a declaração SQL
    $stmt = $conexao->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo "Linha excluída com sucesso!";
        // Redireciona para evitar reenviar o formulário
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Erro ao excluir linha: ";
    }
}

if (isset($_POST['edit'])) {
    $_SESSION['codAgend'] = [
        $_POST['codAgend']
    ];
    header("Location: editAgend.php");
    exit;
}

if (isset($_POST['concluidaAgend'])) {
    $id = $_POST['codAgend'];

    $sql = "UPDATE agenda SET status = 'concluído' WHERE codAgend = :id";
    $stmt = $conexao->prepare($sql);
    $stmt->bindValue(':id', $id);
    $stmt->execute();

    header('location: consAge.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $searchTermAgen = $_POST['search'];

    if (is_numeric($searchTermAgen)) {
        $intervaloDiasAgend = intval($searchTermAgen);

        if ($intervaloDiasAgend == 0) {
            $intervaloDiasAgend = 31;
        }

        $sqlAgenda = "SELECT * FROM agenda WHERE dataPrazo BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :intervaloDiasAgend DAY) ORDER BY dataPrazo ASC LIMIT 0, 5"; //filtra registros por data mais próxima
        $stmt = $conexao->prepare($sqlAgenda);
        $stmt->bindValue(':intervaloDiasAgend', $intervaloDiasAgend, PDO::PARAM_INT);
        $stmt->execute();
        $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Agenda</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css?v=1.4">
</head>

<body>

    <div class="container-fluid cabecalho"> <!-- CABECALHO -->
        <nav class="navbar navbar-light navbar-expand-md" style="background-color: #FFFF;">
            <a class="nav justify-content-start m-2" href="../admInicial.php">
                <img src="../img/back.png">
            </a>

            <button class="navbar-toggler hamburguer" data-bs-toggle="collapse" data-bs-target="#navegacao">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-center" id="navegacao">

                <ul class="nav nav-pills justify-content-center listas"> <!-- LISTAS DO MENU CABECALHO-->


                    <li class="nav-item dropdown"> <!-- LINK BOOTSTRAP DORPDOWN MENU-->
                        <a class="nav-link dropdown-toggle cor_fonte" href="#" id="navbarDropdownMenuLink"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Pedidos
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="../pedidos/cadPed.php">Cadastro</a>
                            <a class="dropdown-item" href="../pedidos/consPed.php">Consulta</a>
                        </div>
                    </li> <!-- FECHA O DROPDOWN MENU-->

                    <li class="nav-item dropdown"> <!-- LINK BOOTSTRAP DORPDOWN MENU-->
                        <a class="nav-link dropdown-toggle cor_fonte" href="#" id="navbarDropdownMenuLink"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Agenda
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="insAge.php">Inserir</a>
                            <a class="dropdown-item" href="#">Consultar</a>
                        </div>
                    </li> <!-- FECHA O DROPDOWN MENU-->

                    <li class="nav-item dropdown"> <!-- LINK BOOTSTRAP DORPDOWN MENU-->
                        <a class="nav-link dropdown-toggle cor_fonte" href="#" id="navbarDropdownMenuLink"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Produtos
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="../produto/cadProd.php">Cadastro</a>
                            <a class="dropdown-item" href="../produto/editProd.php">Edição</a>
                            <a class="dropdown-item" href="../produto/categoria.php">Categoria</a>
                        </div>
                    </li> <!-- FECHA O DROPDOWN MENU-->

                    <li class="nav-item dropdown"> <!-- LINK BOOTSTRAP DORPDOWN MENU-->
                        <a class="nav-link dropdown-toggle cor_fonte" href="#" id="navbarDropdownMenuLink"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Funcionários
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="../funcionarios/cadFunc.php">Cadastro</a>
                            <a class="dropdown-item" href="../funcionarios/listaFunc.php">Listar</a>
                        </div>
                    </li> <!-- FECHA O DROPDOWN MENU-->

                </ul> <!-- FECHA LISTAS MENU CABECALHO -->
            </div>
            <a href="../logout.php" class="nav-link justify-content-end" style="color: red;">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor"
                    class="bi bi-box-arrow-right" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                        d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z" />
                    <path fill-rule="evenodd"
                        d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z" />
                </svg>
            </a>
        </nav> <!-- FECHA CABECALHO -->
    </div> <!-- FECHA CONTAINER DO CABECALHO -->

    <h3 class="text-center mb-5">Consulta da Agenda</h3>

    <div class="container mt-5 mb-5">
        <div class="row justify-content-center text-center">
            <h5 class="mb-3">FILTRAR POR:</h5>

            <div class="dropdown">
                <form method="POST">
                    <div class="row justify-content-center text-center mb-3">
                        <div class="col-4">
                            <input type="text" class="form-control" name="search"
                                placeholder="Digite o intervalo de dias">
                        </div>
                        <div class="col-1">
                            <button type="submit" class="btn btn-primary">Pesquisar</button>
                        </div>
                    </div>
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Funcionário
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <?php foreach ($registroFunc as $registro): ?>
                            <li>
                                <!-- Botão de envio que envia o cod_func como valor -->
                                <button type="submit" class="dropdown-item btnFiltro" name="filtraFunc"
                                    value="<?php echo htmlspecialchars($registro['cod_func']); ?>">
                                    <?php echo htmlspecialchars($registro['nome']); ?>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Status
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <!-- Botão de filtro para pedidos "Pendente" -->
                        <li>
                            <button type="submit" class="dropdown-item btnFiltro" name="filtraSTT"
                                value="pendente">Pendentes</button>
                        </li>
                        <!-- Botão de filtro para pedidos "Concluído" -->
                        <li>
                            <button type="submit" class="dropdown-item btnFiltro" name="filtraSTT"
                                value="concluído">Concluídos</button>
                        </li>
                        <li>
                            <button type="submit" class="dropdown-item btnFiltro" name="filtraSTT"
                                value="todos">Todos</button>
                        </li>
                    </ul>

                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        Data
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <li>
                            <button type="submit" class="dropdown-item btnFiltro" name="filtraDT"
                                value="menorPrazo">Menor
                                Prazo</button>
                        </li>
                        <li>
                            <button type="submit" class="dropdown-item btnFiltro" name="filtraDT"
                                value="maiorPrazo">Maior
                                Prazo</button>
                        </li>
                        <li>
                            <button type="submit" class="dropdown-item btnFiltro" name="filtraDT"
                                value="todos">Todos</button>
                        </li>
                    </ul>

                    <button type="submit" class="btn btn-outline-danger" name="limpar" value="pendente">limpar</button>

                </form>
            </div>
        </div>
    </div>
    <?php if ($erro): ?>
        <div class="alert alert-danger" role="alert">
            Não foi possível carregar os dados.
        </div>
    <?php else: ?>
        <?php foreach ($registros as $registro): ?>
            <div class="mt-5 container consContainer" style="width: 900px">
                <div class="row row-custom">
                    <div class="form-group mb-3">
                        <p><strong>ID:</strong> <?php echo htmlspecialchars($registro['codAgend']); ?></p>
                    </div>
                    <hr>
                    <div class="form-group mb-3">
                        <p><strong>Responsável:</strong> <?php echo htmlspecialchars($registro['responsavel']); ?></p>
                    </div>
                    <div class="form-group mb-3">
                        <p><strong>Título:</strong> <?php echo htmlspecialchars($registro['titulo']); ?></p>
                    </div>
                    <div class="form-group mb-3">
                        <p><strong>Data de Registro:</strong> <?php echo (date('d/m/Y', strtotime($registro['dataRegistro']))); ?></p>
                    </div>
                    <div class="form-group mb-3">
                        <p><strong>Data de Prazo:</strong> <?php echo (date('d/m/Y', strtotime($registro['dataPrazo']))); ?></p>
                    </div>
                    <div class="form-group mb-3">
                        <p><strong>Detalhes:</strong> <?php echo htmlspecialchars($registro['informacao']); ?></p>
                    </div>
                    <hr>
                    <div class="form-group mb-3">
                        <p><strong>Status:</strong> <?php echo htmlspecialchars($registro['status']); ?></p>
                    </div>
                </div>
                <form method="POST">
                    <div class="row">

                        <div class="col-auto">
                            <input type="hidden" name="codAgend" value="<?php echo $registro['codAgend']; ?>">
                            <button type="submit" name="delete" class="btn btn-outline-danger">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    fill="currentColor" class="bi bi-trash-fill" viewBox="0 0 16 16">
                                    <path
                                        d="M2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1H3v9a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V4h.5a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H10a1 1 0 0 0-1-1H7a1 1 0 0 0-1 1zm3 4a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 .5-.5M8 5a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-1 0v-7A.5.5 0 0 1 8 5m3 .5v7a.5.5 0 0 1-1 0v-7a.5.5 0 0 1 1 0" />
                                </svg>
                            </button>
                        </div>
                        <div class="col-auto">
                            <input type="hidden" name="codAgend" value="<?php echo $registro['codAgend']; ?>">
                            <button type="submit" name="edit" class="btn btn-outline-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                    <path
                                        d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                    <path fill-rule="evenodd"
                                        d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z" />
                                </svg>
                            </button>
                        </div>
                        <div class="col-auto">
                            <input type="hidden" name="codAgend" value="<?php echo $registro['codAgend']; ?>">
                            <button type="submit" name="concluidaAgend" class="btn btn-outline-success">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                                    <path
                                        d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                                </svg>
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>