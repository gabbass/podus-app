<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

ob_start();

// Caminho absoluto, 100 % fiel ao que o FileZilla mostrou
require_once __DIR__ . '/../phpword/src/PhpWord/Autoloader.php';
\PhpOffice\PhpWord\Autoloader::register();
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;          // import para clareza

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['html'])) {
    http_response_code(400);
    exit('Nada para exportar');
}

$html = trim($_POST['html']);

/* --- SANITIZAÇÃO --- */
// remove TODAS as <div …> e </div>
$html = preg_replace('/<\/?div[^>]*>/', '', $html);
// remove class e id de qualquer tag
$html = preg_replace('/\s(class|id)="[^"]*"/i', '', $html);
// opcional: converte &nbsp; etc.
$html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

// envelopa em estrutura <html><body> para o parser
$html = '<html><body>'.$html.'</body></html>';

$phpWord = new PhpWord();
$section = $phpWord->addSection([
    'orientation' => \PhpOffice\PhpWord\Style\Section::ORIENTATION_LANDSCAPE,
]);

// modo NÃO estrito (3º param) e sem preservar espaços (4º)
Html::addHtml($section, $html, false, false);

$tmpFile = tempnam(sys_get_temp_dir(), 'docx');
$writer  = IOFactory::createWriter($phpWord, 'Word2007');
$writer->save($tmpFile);
clearstatcache(true, $tmpFile);

ob_end_clean();
header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename=planejamento_'.date('Ymd_His').'.docx');
header('Content-Length: '.filesize($tmpFile));
readfile($tmpFile);
unlink($tmpFile);
exit;
