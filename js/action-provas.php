<?php
require 'sessao-adm-professor.php';
require 'conexao.php';
header('Content-Type: application/json; charset=utf-8');

function out($d,$status=200){ http_response_code($status); echo json_encode($d,JSON_UNESCAPED_UNICODE); exit; }
$login = $_SESSION['login'] ?? '';
$acao  = $_REQUEST['acao'] ?? '';

switch($acao){
  case 'listar':
    $stmt = $pdo->prepare("SELECT id, turma, materia, escola, lista_quest FROM provas_online WHERE login=? ORDER BY id DESC");
    $stmt->execute([$login]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    out(['sucesso'=>true,'provas'=>$rows]);
  case 'buscar':
    $id = (int)($_GET['id']??0); if(!$id) out(['sucesso'=>false,'msg'=>'ID ausente'],422);
    $stmt = $pdo->prepare("SELECT * FROM provas_online WHERE id=? AND login=?");
    $stmt->execute([$id,$login]); $p = $stmt->fetch(PDO::FETCH_ASSOC);
    out($p?['sucesso'=>true,'dado'=>$p]:['sucesso'=>false,'msg'=>'Não encontrado'], $p?200:404);
  case 'criar':
  case 'editar':
    if($_SERVER['REQUEST_METHOD']!=='POST') out(['sucesso'=>false,'msg'=>'Método errado'],405);
    // validações...
    $id   = $_POST['id']??null;
    $turma= $_POST['turma'];
    $escola= $_POST['escola']??'';
    $materia= $_POST['materia'];
    $lista = $_POST['lista_quest']??[];
    if(!$turma||!$materia||!count($lista)) out(['sucesso'=>false,'msg'=>'Dados incompletos'],422);
    if($acao==='criar'){
      $stmt = $pdo->prepare("INSERT INTO provas_online (data, turma, materia, login, escola, lista_quest)
        VALUES (CURDATE(),?,?,?,?,?)");
      $stmt->execute([\$turma,\$materia,\$login,\$escola,implode(',',\$lista)]);
    } else {
      \$stmt = \$pdo->prepare("UPDATE provas_online SET turma=?,materia=?,escola=?,lista_quest=? WHERE id=? AND login=?");
      \$stmt->execute([\$turma,\$materia,\$escola,implode(',',\$lista),\$id,\$login]);
    }
    out(['sucesso'=>true]);
  case 'excluir':
    if($_SERVER['REQUEST_METHOD']!=='POST') out(['sucesso'=>false,'msg'=>'Método errado'],405);
    $id=(int)($_POST['id']??0); if(!$id) out(['sucesso'=>false,'msg'=>'ID ausente'],422);
    $stmt=$pdo->prepare("DELETE FROM provas_online WHERE id=? AND login=?"); $stmt->execute([$id,$login]);
    out(['sucesso'=>true]);
  default:
    out(['sucesso'=>false,'msg'=>'Ação inválida'],400);
}