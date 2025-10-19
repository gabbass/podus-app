<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../conexao.php'; // ajuste conforme necessário

if (!isset($pdo) && isset($conexao)) {
  $pdo = $conexao;
}

require_once __DIR__ . '/../phpword/src/PhpWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register();

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;
if (!isset($_POST['ids'])) {
    die('Nenhuma questão recebida');
}

$ids = json_decode($_POST['ids'], true);

if (!is_array($ids) || count($ids) < 2) {
    die('Selecione pelo menos 2 questões');
}

// 1. Consultar banco
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$sql = "SELECT * FROM questoes WHERE id IN ($placeholders)";
$stmt = $pdo->prepare($sql);
$stmt->execute($ids);
$questoes = $stmt->fetchAll(PDO::FETCH_ASSOC);


$phpWord = new PhpWord();
$section = $phpWord->addSection();

foreach ($questoes as $i => $q) {
    $section->addText(($i+1) . '. ' . strip_tags($q['questao']));
    $section->addText('A) ' . $q['alternativa_A']);
    $section->addText('B) ' . $q['alternativa_B']);
    $section->addText('C) ' . $q['alternativa_C']);
    $section->addText('D) ' . $q['alternativa_D']);
    if (!empty($q['alternativa_E'])) {
        $section->addText('E) ' . $q['alternativa_E']);
    }
    $section->addTextBreak();
}

// 3. Salvar e forçar download
header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
header("Content-Disposition: attachment; filename=prova_" . date('Ymd_His') . ".docx");

$objWriter = IOFactory::createWriter($phpWord, 'Word2007');
$objWriter->save("php://output");
exit;
