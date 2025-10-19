<?php
require('conexao.php');
require('sessao-professor.php');
require_once('tcpdf/tcpdf.php');

// Verifica se foi passado um ID válido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: planejador.php');
    exit;
}

$id_planejamento = $_GET['id'];

// Busca o planejamento no banco de dados
$sql = "SELECT * FROM planejador WHERE id = :id";
$stmt = $conexao->prepare($sql);
$stmt->bindParam(':id', $id_planejamento, PDO::PARAM_INT);
$stmt->execute();
$planejamento = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifica se encontrou o planejamento
if (!$planejamento) {
    header('Location: planejador.php');
    exit;
}

// Desserializa os arrays principais
$planejamento['unidade_tematica'] = unserialize($planejamento['unidade_tematica']);
$planejamento['objetos_conhecimento'] = unserialize($planejamento['objetos_conhecimento']);
$planejamento['conteudos'] = unserialize($planejamento['conteudos']);
$planejamento['habilidades'] = unserialize($planejamento['habilidades']);
$planejamento['metodologia'] = unserialize($planejamento['metodologia']);
$planejamento['projetos_integradores'] = unserialize($planejamento['projetos_integradores']);

// Desserialização robusta dos grupos internos
$grupos_internos = [];
if (!empty($planejamento['grupos_internos'])) {
    // Tenta unserialize primeiro
    $grupos_internos = @unserialize($planejamento['grupos_internos']);
    
    // Se falhar, tenta json_decode
    if ($grupos_internos === false) {
        $grupos_internos = json_decode($planejamento['grupos_internos'], true);
    }
    
    // Garante que temos um array
    if (!is_array($grupos_internos)) {
        $grupos_internos = [];
    }
}

// Estrutura os grupos internos no formato esperado
$planejamento['grupos_internos'] = [];
if (!empty($grupos_internos)) {
    foreach ($grupos_internos as $periodo => $grupos) {
        if (is_array($grupos)) {
            $planejamento['grupos_internos'][$periodo] = $grupos;
        }
    }
}

// Função para gerar PDF
function gerarPDF($dados, $id_planejamento = null) {
    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    if ($id_planejamento === null) {
        $id_planejamento = 'TEMP-' . date('YmdHis');
    } else {
        $id_planejamento = str_pad($id_planejamento, 5, '0', STR_PAD_LEFT);
    }

    $pdf->SetCreator('Universo do Saber');
    $pdf->SetAuthor('Universo do Saber');
    $pdf->SetTitle('Planejamento Anual');
    $pdf->SetSubject('Planejamento Anual');
    
    $pdf->SetMargins(15, 10, 15);
    $pdf->SetHeaderMargin(0);
    $pdf->SetFooterMargin(0);
    $pdf->SetAutoPageBreak(true, 15);

    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    $pdf->AddPage();
    
    $logoPath = 'img/logo.png';
    if (file_exists($logoPath)) {
        $pdf->Image($logoPath, 10, 15, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
    }

    $pdf->SetY(35);
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, 'PLANEJAMENTO ANUAL - ' . $id_planejamento, 0, 1, 'C');
    
    // Informações básicas
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Informações Básicas', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    
    $info = array(
        'Professor:' => $dados['professor'],
        'Escola:' => $dados['escola'],
        'Curso:' => $dados['curso'],
        'Componente Curricular:' => $dados['componente_curricular'],
        'Ano Escolar:' => $dados['ano_escolar'],
        'Aulas Semanais:' => $dados['aulas_semanais'],
        'Período:' => $dados['periodo'],
        'Tipo:' => ($dados['tipo'] == 'bimestre' ? 'Bimestre' : 'Trimestre')
    );
    
    foreach ($info as $label => $value) {
        $pdf->Cell(50, 6, $label, 0, 0, 'L');
        $pdf->Cell(0, 6, $value, 0, 1, 'L');
    }
    
    $pdf->Ln(2);
    $pdf->Line(15, $pdf->GetY(), $pdf->GetPageWidth()-15, $pdf->GetY());
    $pdf->Ln(5);
    
    // Objetivos
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Objetivos', 0, 1, 'L');
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'Objetivo Geral:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(0, 6, $dados['objetivo_geral'], 0, 'L');
    $pdf->Ln(2);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'Objetivos Específicos:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(0, 6, $dados['objetivo_especifico'], 0, 'L');
    
    $pdf->Ln(2);
    $pdf->Line(15, $pdf->GetY(), $pdf->GetPageWidth()-15, $pdf->GetY());
    $pdf->Ln(5);
    
    // Períodos (bimestres/trimestres)
    if (!empty($dados['unidade_tematica'])) {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, ($dados['tipo'] == 'bimestre' ? 'BIMESTRES' : 'TRIMESTRES'), 0, 1, 'L');
        
        foreach ($dados['unidade_tematica'] as $index => $unidade) {
            $num = $index + 1;
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(0, 8, ($dados['tipo'] == 'bimestre' ? $num.'º BIMESTRE' : $num.'º TRIMESTRE'), 0, 1, 'L');
            
            // Conteúdo principal do período
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 6, 'Projetos Integradores:', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 10);
            $pdf->MultiCell(0, 6, $dados['projetos_integradores'][$index], 0, 'L');
            
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 6, 'Unidade Temática:', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 10);
            $pdf->MultiCell(0, 6, $unidade, 0, 'L');
            
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 6, 'Objetos de Conhecimento:', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 10);
            $pdf->MultiCell(0, 6, $dados['objetos_conhecimento'][$index], 0, 'L');
            
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 6, 'Conteúdos:', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 10);
            $pdf->MultiCell(0, 6, $dados['conteudos'][$index], 0, 'L');
            
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 6, 'Habilidades:', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 10);
            $pdf->MultiCell(0, 6, $dados['habilidades'][$index], 0, 'L');
            
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 6, 'Metodologia:', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 10);
            $pdf->MultiCell(0, 6, $dados['metodologia'][$index], 0, 'L');
            
            // Grupos internos
            if (!empty($dados['grupos_internos'][$index])) {
                foreach ($dados['grupos_internos'][$index] as $grupoIndex => $grupo) {
                    $pdf->Ln(3);
                    $pdf->Line(15, $pdf->GetY(), $pdf->GetPageWidth()-15, $pdf->GetY());
                    $pdf->Ln(3);
                    
                    $pdf->SetFont('helvetica', 'B', 10);
                    $pdf->Cell(0, 6, 'GRUPO ' . ($grupoIndex + 1) . ':', 0, 1, 'L');
                    $pdf->SetFont('helvetica', '', 10);
                    
                    $pdf->SetFont('helvetica', 'B', 10);
                    $pdf->Cell(0, 6, 'Projetos Integradores:', 0, 1, 'L');
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->MultiCell(0, 6, $grupo['projetos_integradores'], 0, 'L');
                    
                    $pdf->SetFont('helvetica', 'B', 10);
                    $pdf->Cell(0, 6, 'Unidade Temática:', 0, 1, 'L');
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->MultiCell(0, 6, $grupo['unidade_tematica'], 0, 'L');
                    
                    $pdf->SetFont('helvetica', 'B', 10);
                    $pdf->Cell(0, 6, 'Objetos de Conhecimento:', 0, 1, 'L');
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->MultiCell(0, 6, $grupo['objetos_conhecimento'], 0, 'L');
                    
                    $pdf->SetFont('helvetica', 'B', 10);
                    $pdf->Cell(0, 6, 'Conteúdos:', 0, 1, 'L');
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->MultiCell(0, 6, $grupo['conteudos'], 0, 'L');
                    
                    $pdf->SetFont('helvetica', 'B', 10);
                    $pdf->Cell(0, 6, 'Habilidades:', 0, 1, 'L');
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->MultiCell(0, 6, $grupo['habilidades'], 0, 'L');
                    
                    $pdf->SetFont('helvetica', 'B', 10);
                    $pdf->Cell(0, 6, 'Metodologia:', 0, 1, 'L');
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->MultiCell(0, 6, $grupo['metodologia'], 0, 'L');
                }
            }
            
            $pdf->Ln(3);
            $pdf->Line(15, $pdf->GetY(), $pdf->GetPageWidth()-15, $pdf->GetY());
            $pdf->Ln(5);
        }
    }
    
    // Outras informações
    $pdf->Ln(5);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, 'Outras Informações', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'Observações:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(0, 6, $dados['observacao'], 0, 'L');
    $pdf->Ln(2);
    
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'DIAGNÓSTICO/PERFIL DE TURMA(S):', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(0, 6, $dados['diagnostico'], 0, 'L');
    $pdf->Ln(2);
    
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 6, 'REFERÊNCIAS:', 0, 1, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(0, 6, $dados['referencias'], 0, 'L');
    
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'I', 9);
    $dataHora = date('d/m/Y H:i:s');
    $textoRodape = 'Planejador desenvolvido pelo Portal Universo do Saber - https://portaluniversodosaber.com.br/portal - Gerado em: ' . $dataHora;
    $pdf->Cell(0, 6, $textoRodape, 0, 0, 'C');

    $pdf->Output('planejamento_anual_'.date('YmdHis').'.pdf', 'D');
    exit;
}

if (isset($_POST['gerar_pdf'])) {
    gerarPDF($planejamento, $id_planejamento);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Planejamento - Universo do Saber</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-blue: #0057b7;
            --primary-orange: #ffa500;
            --dark-blue: #003d7a;
            --dark-orange: #cc8400;
            --light-gray: #f5f7fa;
            --medium-gray: #e1e5eb;
            --dark-gray: #6c757d;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--light-gray);
            min-height: 100vh;
        }
        
        .main-content {
            transition: all 0.3s;
        }
        
        .content {
            padding: 30px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .titulo-principal {
            font-size: 2rem;
            color: var(--primary-blue);
            font-weight: 600;
            margin-bottom: 10px;
            text-align: center;
            width: 100%;
        }
        
        .id-planejamento {
            font-size: 0.8em;
            color: #666;
        }

        .page-title h1 {
            font-size: 1.8rem;
            color: var(--dark-blue);
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .page-title p {
            color: var(--dark-gray);
            font-size: 0.9rem;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--dark-blue);
        }
        
        .btn-secondary {
            background-color: var(--medium-gray);
            color: var(--dark-gray);
        }
        
        .btn-secondary:hover {
            background-color: #d1d7e0;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-blue);
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
            font-size: 1rem;
            background-color: #f8f9fa;
            color: #495057;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .grupo-campos {
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
            background-color: #f8f9fa;
        }
        
        .grupo-campos[data-index="0"] {
            background-color: #e8f4fc;
        }
        
        .grupo-campos[data-index="1"] {
            background-color: #fcf3e8;
        }
        
        .grupo-campos[data-index="2"] {
            background-color: #f0e8fc;
        }
        
        .grupo-campos[data-index="3"] {
            background-color: #e8fce8;
        }
        
        .grupo-titulo {
            font-weight: 600;
            color: var(--primary-blue);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            text-transform: uppercase;
        }
        
        .grupo-titulo i {
            margin-right: 10px;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .grupo-interno {
            border: 1px dashed var(--medium-gray);
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #fff;
            position: relative;
        }
        
        .grupo-interno:not(:last-child)::after {
            content: '';
            display: block;
            height: 1px;
            background-color: var(--medium-gray);
            margin: 15px -15px;
        }
        
        .grupo-interno-titulo {
            font-weight: 500;
            color: var(--dark-blue);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .form-group strong {
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            color: var(--dark-blue);
        }
        
        @media (max-width: 768px) {
            .content {
                padding: 15px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .btn, .btn-group {
                width: 100%;
                flex-direction: column;
            }
            
            .btn {
                justify-content: center;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="content">
            <div class="page-header">
                <div class="titulo-principal">PLANEJAMENTO ANUAL - <?= str_pad($id_planejamento, 5, '0', STR_PAD_LEFT) ?></div>
                <div class="page-title">
                    <h1>Visualizar Planejamento</h1>
                    <p>Detalhes do planejamento cadastrado</p>
                </div>
                <a href="planejador.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
            
            <div class="card">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="professor">Professor</label>
                        <input type="text" id="professor" value="<?= htmlspecialchars($planejamento['professor']) ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="escola">Escola</label>
                        <input type="text" id="escola" value="<?= htmlspecialchars($planejamento['escola']) ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="curso">Curso</label>
                        <input type="text" id="curso" value="<?= htmlspecialchars($planejamento['curso']) ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="componente_curricular">Componente Curricular</label>
                        <input type="text" id="componente_curricular" value="<?= htmlspecialchars($planejamento['componente_curricular']) ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="ano_escolar">Ano Escolar</label>
                        <input type="text" id="ano_escolar" value="<?= htmlspecialchars($planejamento['ano_escolar']) ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="aulas_semanais">Número de Aulas Semanais</label>
                        <input type="number" id="aulas_semanais" value="<?= htmlspecialchars($planejamento['aulas_semanais']) ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="tipo">Tipo</label>
                        <input type="text" id="tipo" value="<?= $planejamento['tipo'] === 'bimestre' ? 'Bimestre' : 'Trimestre' ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="periodo">Período</label>
                        <input type="text" id="periodo" value="<?= htmlspecialchars($planejamento['periodo']) ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="carga_horaria">Carga Horária</label>
                        <input type="number" id="carga_horaria" value="<?= htmlspecialchars($planejamento['carga_horaria']) ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="objetivo_geral">Objetivo Geral</label>
                        <textarea id="objetivo_geral" readonly><?= htmlspecialchars($planejamento['objetivo_geral']) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="objetivo_especifico">Objetivos Específicos</label>
                        <textarea id="objetivo_especifico" readonly><?= htmlspecialchars($planejamento['objetivo_especifico']) ?></textarea>
                    </div>
                    
                    <div id="periodos-container">
                        <?php if (!empty($planejamento['unidade_tematica'])): ?>
                            <?php foreach ($planejamento['unidade_tematica'] as $index => $unidade): ?>
                                <div class="grupo-campos" data-index="<?= $index ?>">
                                    <div class="grupo-titulo">
                                        <i class="fas fa-calendar-alt"></i>
                                        <?= ($planejamento['tipo'] === 'bimestre' ? ($index + 1).'º BIMESTRE' : ($index + 1).'º TRIMESTRE') ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <strong>Projetos Integradores</strong>
                                        <textarea readonly><?= htmlspecialchars($planejamento['projetos_integradores'][$index] ?? '') ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <strong>Unidade Temática</strong>
                                        <textarea readonly><?= htmlspecialchars($unidade) ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <strong>Objetos de Conhecimento</strong>
                                        <textarea readonly><?= htmlspecialchars($planejamento['objetos_conhecimento'][$index] ?? '') ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <strong>Conteúdos</strong>
                                        <textarea readonly><?= htmlspecialchars($planejamento['conteudos'][$index] ?? '') ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <strong>Habilidades</strong>
                                        <textarea readonly><?= htmlspecialchars($planejamento['habilidades'][$index] ?? '') ?></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <strong>Metodologia</strong>
                                        <textarea readonly><?= htmlspecialchars($planejamento['metodologia'][$index] ?? '') ?></textarea>
                                    </div>
                                    
                                    <?php if (!empty($planejamento['grupos_internos'][$index])): ?>
                                        <div id="grupos-internos-<?= $index ?>">
                                            <?php foreach ($planejamento['grupos_internos'][$index] as $grupoIndex => $grupo): ?>
                                                <?php if (is_array($grupo)): ?>
                                                    <div class="grupo-interno">
                                                        <div class="grupo-interno-titulo">
                                                            <span>GRUPO <?= $grupoIndex + 1 ?></span>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <strong>Projetos Integradores</strong>
                                                            <textarea readonly><?= htmlspecialchars($grupo['projetos_integradores'] ?? '') ?></textarea>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <strong>Unidade Temática</strong>
                                                            <textarea readonly><?= htmlspecialchars($grupo['unidade_tematica'] ?? '') ?></textarea>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <strong>Objetos de Conhecimento</strong>
                                                            <textarea readonly><?= htmlspecialchars($grupo['objetos_conhecimento'] ?? '') ?></textarea>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <strong>Conteúdos</strong>
                                                            <textarea readonly><?= htmlspecialchars($grupo['conteudos'] ?? '') ?></textarea>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <strong>Habilidades</strong>
                                                            <textarea readonly><?= htmlspecialchars($grupo['habilidades'] ?? '') ?></textarea>
                                                        </div>
                                                        
                                                        <div class="form-group">
                                                            <strong>Metodologia</strong>
                                                            <textarea readonly><?= htmlspecialchars($grupo['metodologia'] ?? '') ?></textarea>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="observacao">Observações</label>
                        <textarea id="observacao" readonly><?= htmlspecialchars($planejamento['observacao']) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="diagnostico">DIAGNÓSTICO/PERFIL DE TURMA(S)</label>
                        <textarea id="diagnostico" readonly><?= htmlspecialchars($planejamento['diagnostico']) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="referencias">REFERÊNCIAS</label>
                        <textarea id="referencias" readonly><?= htmlspecialchars($planejamento['referencias']) ?></textarea>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" name="gerar_pdf" class="btn btn-primary">
                            <i class="fas fa-file-pdf"></i> Gerar PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>