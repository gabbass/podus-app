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