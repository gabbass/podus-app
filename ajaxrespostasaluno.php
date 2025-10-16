<?php
require('sessao-professor.php');
require('conexao.php');

$matricula = $_GET['matricula'] ?? '';
$idprova = $_GET['idprova'] ?? '';

header('Content-Type: application/json; charset=utf-8');
if(!$matricula || !$idprova){
    echo json_encode(['sucesso'=>false, 'msg'=>'Dados incompletos']);
    exit;
}
try {
    // Busca lista questão IDs para esta prova_online
    $stmt_lista = $conexao->prepare("SELECT lista_quest FROM provas_online WHERE id=?");
    $stmt_lista->execute([$idprova]);
    $linha = $stmt_lista->fetch(PDO::FETCH_ASSOC);
    if(!$linha || !$linha['lista_quest']){
        echo json_encode(['sucesso'=>true, 'respostas'=>[]]);
        exit;
    }
    $id_questoes = array_filter(array_map('trim', explode(',',$linha['lista_quest'])));
    if(!count($id_questoes)){
        echo json_encode(['sucesso'=>true, 'respostas'=>[]]);
        exit;
    }

    // Busca respostas do aluno
    $stmt_resp = $conexao->prepare(
        "SELECT * FROM respostas_alunos WHERE id_provas_online=? AND id_matricula=?"
    );
    $stmt_resp->execute([$idprova, $matricula]);
    $resps_dadas = [];
    while($r = $stmt_resp->fetch(PDO::FETCH_ASSOC)){
        $resps_dadas[$r['id_questao']] = $r['resposta'];
    }

    // Para cada questão, montar
    $respostas = [];
    foreach($id_questoes as $idq){
        $q = $conexao->prepare("SELECT * FROM questoes WHERE id=?");
        $q->execute([$idq]);
        if(!$qq = $q->fetch(PDO::FETCH_ASSOC)) continue;

        $resp = isset($resps_dadas[$idq]) ? strtoupper($resps_dadas[$idq]) : null;
        $gabarito = strtoupper($qq['resposta']);
        $correta = $resp && ($resp === $gabarito);

        $respostas[] = [
            'enunciado' => mb_substr($qq['questao']??$qq['pergunta'], 0, 250),
            'resposta' => $resp,
            'gabarito' => $gabarito,
            'correta' => $correta
        ];
    }

    echo json_encode(['sucesso'=>true,'respostas'=>$respostas]);
} catch(Exception $e){
    echo json_encode(['sucesso'=>false, 'msg'=>'Erro: '.$e->getMessage()]);
}
