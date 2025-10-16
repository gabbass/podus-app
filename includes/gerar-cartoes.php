<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/fpdf/fpdf.php';

require_once __DIR__ . '/fpdf/fpdf.php';

class PDF extends FPDF
{
    function Ellipse($x, $y, $rx, $ry, $style='D') {
        $lx = 4/3*(M_SQRT2-1)*$rx;
        $ly = 4/3*(M_SQRT2-1)*$ry;
        $k = $this->k;
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F m', ($x+$rx)*$k, ($h-$y)*$k));
        $this->_Arc($x+$rx, $y-$ly, $x+$lx, $y-$ry, $x, $y-$ry, $x-$lx, $y-$ry, $x-$rx, $y-$ly);
        $this->_Arc($x-$rx, $y-$ly, $x-$lx, $y, $x-$rx, $y+$ly, $x-$lx, $y+$ry, $x, $y+$ry);
        $this->_Arc($x, $y+$ry, $x+$lx, $y+$ry, $x+$rx, $y+$ly, $x+$lx, $y, $x+$rx, $y-$ly);
        if($style=='F') $op='f';
        elseif($style=='FD' || $style=='DF') $op='B';
        else $op='S';
        $this->_out($op);
    }
    function _Arc($x1, $y1, $x2, $y2, $x3, $y3) {
        $k = $this->k;
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
            $x1*$k, ($h-$y1)*$k,
            $x2*$k, ($h-$y2)*$k,
            $x3*$k, ($h-$y3)*$k
        ));
    }
}


// --- DADOS EXEMPLO (troque por busca do banco, se quiser) ---
$professor = 'Everton Souza';
$turma     = '3º B';
$materia   = 'Matemática';
$idProva   = 20258123;
$totalQuestoes = 10; // Defina quantas questões

$pdf = new PDF('P', 'mm', 'A4');
$pdf->SetAutoPageBreak(true, 20);

// ───────────── CARTÃO DO PROFESSOR ─────────────
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 18);
$pdf->Cell(0, 15, 'CARTÃO DO PROFESSOR', 0, 1, 'C');
$pdf->Ln(4);

$pdf->SetFont('Arial', '', 14);
$pdf->Cell(0, 10, "Nome: $professor", 0, 1);
$pdf->Cell(0, 10, "Turma: $turma", 0, 1);
$pdf->Cell(0, 10, "Matéria: $materia", 0, 1);
$pdf->Cell(0, 10, "ID da Prova: $idProva", 0, 1);
$pdf->Ln(10);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Instrucao:', 0, 1);

$pdf->SetFont('Arial', '', 12);
$pdf->MultiCell(0, 8, "Para cada questão, perfure o círculo correspondente à resposta correta usando uma caneta BIC ou similar. Não utilize lápis nem caneta colorida.");

$pdf->Ln(5);
// Desenho simples da caneta e círculo (tudo preto e branco)
$pdf->SetDrawColor(0);
$pdf->Ellipse(30, $pdf->GetY(), 8, 8);
$pdf->Line(40, $pdf->GetY()+4, 65, $pdf->GetY()+4); // haste da caneta
$pdf->Line(65, $pdf->GetY()+2, 65, $pdf->GetY()+6); // ponta

$pdf->Ln(16);
$pdf->SetFont('Arial', '', 10);
$pdf->MultiCell(0, 6, "Preencha ou perfure completamente o círculo correspondente à alternativa correta em cada questão. Em caso de erro, solicite um novo cartão.");

// ───────────── CARTÕES DE ALUNO (um cartão por página) ─────────────
for ($i = 1; $i <= $totalQuestoes; $i++) {
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 18);
    $pdf->Cell(0, 15, "CARTÃO DO ALUNO", 0, 1, 'C');

    $pdf->SetFont('Arial', '', 14);
    $pdf->Cell(0, 10, "Questão: $i", 0, 1, 'C');
    $pdf->Ln(10);

    // Desenha bolinhas A B C D E para marcar
    $x = 40;
    $y = $pdf->GetY();
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Marque apenas UMA alternativa:', 0, 1, 'C');

    $alternativas = ['A','B','C','D','E'];
    foreach ($alternativas as $idx => $alt) {
        $pdf->SetXY($x + ($idx*25), $y + 20);
        $pdf->Cell(10, 10, $alt, 0, 0, 'C');
        // Bolinha para marcar
        $pdf->Ellipse($x + ($idx*25) + 5, $y + 27, 7, 7);
    }

    $pdf->Ln(35);
    $pdf->SetFont('Arial', '', 10);
    $pdf->MultiCell(0, 6, "Preencha o círculo correspondente à alternativa correta utilizando caneta PRETA. Não utilize lápis.\nEm caso de erro, solicite outro cartão ao professor.");
}

// ───────────── SAÍDA PDF ─────────────
$pdf->Output("I", "cartoes_prova_$idProva.pdf");
exit;

// Função extra do FPDF (adicionada ao final do arquivo OU em fpdf.php)
// Se usar FPDF 1.8+, você pode colar isto no fim do seu script:
if (!function_exists('Ellipse')) {
    function Ellipse($pdf, $x, $y, $rx, $ry, $style='D') {
        $k = $pdf->k;
        $h = $pdf->h;
        $pdf->_out(sprintf('%.2F %.2F %.2F %.2F re', ($x-$rx)*$k, ($h-$y-$ry)*$k, $rx*2*$k, $ry*2*$k));
        if($style=='F') $op='f';
        elseif($style=='FD' || $style=='DF') $op='B';
        else $op='S';
        $pdf->_out($op);
    }
    // Adiciona método na classe FPDF dinamicamente
    FPDF::addMethod('Ellipse', function($self, $x, $y, $rx, $ry, $style='D') {
        Ellipse($self, $x, $y, $rx, $ry, $style);
    });
}
?>
