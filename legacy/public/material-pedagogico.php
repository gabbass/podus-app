<?php
require('sessao-professor.php');
$tituloPagina = "Material Pedagógico - Universo do Saber";
include dirname(__DIR__) . '/includes/head.php';
?>
<body>
    <?php include dirname(__DIR__) . '/includes/menu.php'; ?>
    <div class="main-content" id="main-content">
        <?php include dirname(__DIR__) . '/includes/cabecalho.php'; ?>
        <div class="content-container" id="content-container">
            <div class="container">

                <div class="page-header">
                    <div class="page-title">
                        <h1>Material Pedagógico</h1>
                        <p>Envie materiais para suas turmas e disciplinas</p>
                    </div>
                    <button type="button" class="btn btn-primary" id="btnNovoMaterial">
                        <i class="fas fa-plus"></i> Novo material
                    </a>
                </div>

                <!-- Formulário de envio -->
                <div class="segundo-container oculto destaque" id="formNovoMaterial">
                    <?php include dirname(__DIR__) . '/includes/crud-material.php'; ?>
                </div>

                <!-- Filtros -->
                <div class="filtros-container" >
                    <?php include dirname(__DIR__) . '/includes/filtro-material.php'; ?>
                </div>

                <!-- Lista de materiais enviados -->
                <div class="table-container" id="lista-materiais">
                    <?php include dirname(__DIR__) . '/includes/listar-materiais.php'; ?>
                </div>

            </div>
        </div>
    </div>

    <?php include dirname(__DIR__) . '/includes/rodape.php'; ?>
    <?php include dirname(__DIR__) . '/includes/modal-geral.php'; ?>
    <script src="js/material.js"></script>
</body>
<?php include dirname(__DIR__) . '/includes/foot.php'; ?>
