<?php
require_once 'vendor/autoload.php'; // ajuste o path se necessário

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['html'])) {
    http_response_code(400);
    exit('Nada para exportar');
}

$html = $_POST['html'] ?? '';
$html = trim($html);

$phpWord = new PhpWord();
$section = $phpWord->addSection();

// Importar HTML (tem limitações, mas suporta tabelas, títulos, parágrafos etc)
\PhpOffice\PhpWord\Shared\Html::addHtml($section, $html);

// Gerar o DOCX
$tmpFile = tempnam(sys_get_temp_dir(), 'docx');
$writer = IOFactory::createWriter($phpWord, 'Word2007');
$writer->save($tmpFile);

// Forçar download
header('Content-Description: File Transfer');
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename=planejamento_mensal_' . date('Ymd_His') . '.docx');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($tmpFile));
readfile($tmpFile);
unlink($tmpFile);
exit;
?>