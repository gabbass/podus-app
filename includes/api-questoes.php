<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

/* ─── Sessão / conexões ─────────────────────────────────────────── */
require_once __DIR__.'/../sessao-adm-professor.php';
require_once __DIR__.'/../conexao.php';

/* ─── compat: garante que $pdo exista ─────────────────────────── */
if (!isset($pdo) && isset($conexao)) $pdo = $conexao;

/* ─── helper JSON ─────────────────────────────────────────────── */
if (!function_exists('out')) {
    function out(array $d, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($d, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$perfil      = $_SESSION['perfil'] ?? '';
$idProf      = $_SESSION['id']     ?? 0;
$eAdm        = ($perfil === 'Administrador');

/* ─── rota principal ───────────────────────────────────────────── */
$acao = $_REQUEST['acao'] ?? '';

switch ($acao) {

    /* ================================================================
     * LISTAR
     * ============================================================= */
    case 'listar':
        $pag   = max(1, (int)($_GET['page']  ?? 1));
        $lim   = max(5, (int)($_GET['limit'] ?? 15));
        $off   = ($pag - 1) * $lim;

        $bind  = [];
        $where = [];

        if (!empty($_GET['materia'])) {
            $where[]         = 'materia = :materia';
            $bind[':materia']= trim($_GET['materia']);
        }
        if (!empty($_GET['assunto'])) {
            $where[]         = 'assunto = :assunto';
            $bind[':assunto']= trim($_GET['assunto']);
        }
        if (!empty($_GET['texto'])) {
            $where[]       = '(questao LIKE :txt
                               OR alternativa_A LIKE :txt
                               OR alternativa_B LIKE :txt
                               OR alternativa_C LIKE :txt
                               OR alternativa_D LIKE :txt
                               OR alternativa_E LIKE :txt)';
            $bind[':txt']  = '%'.trim($_GET['texto']).'%';
        }

        if (!$eAdm) {
            $where[] = '(isRestrito IS NULL OR isRestrito = 0 OR id_professor = :prof)';
            $bind[':prof'] = $idProf;
        }

        $sqlWhere = $where ? (' WHERE '.implode(' AND ', $where)) : '';

        $validOrd = ['id','materia','assunto','data'];
        $ordCampo = in_array($_GET['ordenar_por'] ?? '', $validOrd)
                    ? $_GET['ordenar_por'] : 'id';
        $ordDir   = (($_GET['ordem'] ?? 'desc') === 'asc') ? 'ASC' : 'DESC';

        $stmtTot = $pdo->prepare("SELECT COUNT(*) FROM questoes $sqlWhere");
        $stmtTot->execute($bind);
        $total = (int)$stmtTot->fetchColumn();

        $sql = "SELECT id, questao, materia, assunto, grau_escolar,
                       isRestrito, autor, fonte, id_professor
                FROM   questoes
                $sqlWhere
                ORDER BY $ordCampo $ordDir
                LIMIT :lim OFFSET :off";
        $stmt = $pdo->prepare($sql);
        foreach ($bind as $k=>$v) {
            $stmt->bindValue($k,$v);
        }
        $stmt->bindValue(':lim',$lim,PDO::PARAM_INT);
        $stmt->bindValue(':off',$off,PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['pode_editar'] = $eAdm || ($r['id_professor'] == $idProf);
        }

        out([
            'sucesso'=>true,
            'questoes'=>$rows,
            'pagina'=>$pag,
            'paginas'=>ceil($total/$lim)
        ]);
        break;

    /* ================================================================
     * BUSCAR
     * ============================================================= */
    case 'buscar':
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) out(['sucesso' => false, 'msg' => 'ID ausente'], 422);

        $stmt = $pdo->prepare("SELECT * FROM questoes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $dado = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dado) out(['sucesso' => false, 'msg' => 'Não encontrado'], 404);
        if (!$eAdm) {
            $eRestrita = (int)($dado['isRestrito'] ?? 0) === 1;
            $eDono     = (int)$dado['id_professor'] === $idProf;
            if ($eRestrita && !$eDono) {
                out(['sucesso' => false, 'msg' => 'Sem permissão'], 403);
            }
        }

        // $dado já inclui ['justificativa'] por ter usado SELECT *
        out(['sucesso' => true, 'dado' => $dado]);
        break;

    /* ================================================================
     * CRIAR
     * ============================================================= */
    case 'criar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            out(['sucesso'=>false,'msg'=>'Método errado'],405);

        // obrigatórios (justificativa é opcional)
        $obrig = ['questao','materia','assunto','grau_escolar','resposta','status'];
        foreach ($obrig as $c) {
            if (empty($_POST[$c])) {
                out(['sucesso'=>false,'msg'=>"Campo $c é obrigatório"],422);
            }
        }

        $tipo = empty($_POST['alternativa_E']) ? '4 Questões' : '5 Questões';

        $dados = [
            ':questao'       => $_POST['questao'],
            ':materia'       => $_POST['materia'],
            ':assunto'       => $_POST['assunto'],
            ':grau'          => $_POST['grau_escolar'],
            ':altA'          => $_POST['alternativa_A'] ?? '',
            ':altB'          => $_POST['alternativa_B'] ?? '',
            ':altC'          => $_POST['alternativa_C'] ?? '',
            ':altD'          => $_POST['alternativa_D'] ?? '',
            ':altE'          => $_POST['alternativa_E'] ?? '',
            ':resposta'      => strtoupper($_POST['resposta']),
            ':justificativa' => $_POST['justificativa'] ?? '',     // *** novo ***
            ':tipo'          => $tipo,
            ':status'        => $_POST['status'],
            ':data'          => time(),
            ':id_prof'       => $idProf,
            ':isRestrito'    => isset($_POST['isRestrito']) ? 1 : 0,
            ':autor'         => $_POST['autor']   ?? null,
            ':fonte'         => $_POST['fonte']   ?? null,
            ':imagem'        => null,
        ];

        $sql = "INSERT INTO questoes
                (questao,materia,assunto,grau_escolar,
                 alternativa_A,alternativa_B,alternativa_C,
                 alternativa_D,alternativa_E,resposta,
                 justificativa,      -- *** novo ***
                 tipo,status,data,id_professor,
                 isRestrito,autor,fonte,imagem)
                VALUES
                (:questao,:materia,:assunto,:grau,
                 :altA,:altB,:altC,:altD,:altE,:resposta,
                 :justificativa,     -- *** novo ***
                 :tipo,:status,:data,:id_prof,
                 :isRestrito,:autor,:fonte,:imagem)";
        $pdo->prepare($sql)->execute($dados);
        $novoId = $pdo->lastInsertId();

        // upload de imagem (igual ao seu)
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error']===UPLOAD_ERR_OK) {
            $dir = __DIR__.'/../questoes/';
            if (!is_dir($dir)) mkdir($dir,0777,true);
            $ext  = strtolower(pathinfo($_FILES['imagem']['name'],PATHINFO_EXTENSION));
            $dest = $dir."questao_{$novoId}.$ext";
            if (move_uploaded_file($_FILES['imagem']['tmp_name'],$dest)) {
                $rel = 'questoes/'.basename($dest);
                $pdo->prepare("UPDATE questoes SET imagem=:img WHERE id=:id")
                    ->execute([':img'=>$rel, ':id'=>$novoId]);
            }
        }

        out(['sucesso'=>true,'id_novo'=>$novoId]);
        break;

    /* ================================================================
     * EDITAR
     * ============================================================= */
    case 'editar':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            out(['sucesso'=>false,'msg'=>'Método errado'],405);

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) out(['sucesso'=>false,'msg'=>'ID ausente'],422);

        if (!$eAdm) {
            $dono = $pdo->prepare("SELECT id_professor FROM questoes WHERE id=:id");
            $dono->execute([':id'=>$id]);
            if ((int)$dono->fetchColumn() !== $idProf) {
                out(['sucesso'=>false,'msg'=>'Sem permissão'],403);
            }
        }

        // agora inclui justificativa
        $campos = [
            'questao','materia','assunto','grau_escolar','alternativa_A',
            'alternativa_B','alternativa_C','alternativa_D','alternativa_E',
            'resposta','justificativa',   // *** novo ***
            'tipo','status','isRestrito','autor','fonte'
        ];
        $set  = [];
        $bind = [':id'=>$id];
        foreach ($campos as $c) {
            if (isset($_POST[$c])) {
                $set[]        = "$c = :$c";
                $bind[":$c"]  = ($c==='resposta')
                                ? strtoupper($_POST[$c])
                                : $_POST[$c];
            }
        }
        if (!$set) out(['sucesso'=>false,'msg'=>'Nada para alterar'],422);

        $pdo->prepare("UPDATE questoes SET ".implode(',',$set)." WHERE id=:id")
            ->execute($bind);

        // imagem nova?
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error']===UPLOAD_ERR_OK) {
            $dir = __DIR__.'/../questoes/';
            if (!is_dir($dir)) mkdir($dir,0777,true);
            $ext  = strtolower(pathinfo($_FILES['imagem']['name'],PATHINFO_EXTENSION));
            $dest = $dir."questao_{$id}.$ext";
            if (move_uploaded_file($_FILES['imagem']['tmp_name'],$dest)) {
                $rel = 'questoes/'.basename($dest);
                $pdo->prepare("UPDATE questoes SET imagem=:img WHERE id=:id2")
                    ->execute([':img'=>$rel, ':id2'=>$id]);
            }
        }

        out(['sucesso'=>true]);
        break;

    /* ================================================================
     * EXCLUIR
     * ============================================================= */
    case 'excluir':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            out(['sucesso'=>false,'msg'=>'Método errado'],405);

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) out(['sucesso'=>false,'msg'=>'ID ausente'],422);

        if (!$eAdm) {
            $dono = $pdo->prepare("SELECT id_professor FROM questoes WHERE id=:id");
            $dono->execute([':id'=>$id]);
            if ((int)$dono->fetchColumn() !== $idProf) {
                out(['sucesso'=>false,'msg'=>'Sem permissão'],403);
            }
        }

        $pdo->prepare("DELETE FROM questoes WHERE id=:id")->execute([':id'=>$id]);
        array_map('unlink', glob(__DIR__."/../questoes/questao_{$id}.*"));

        out(['sucesso'=>true]);
        break;

    /* ================================================================
     * AÇÃO INVÁLIDA
     * ============================================================= */
    default:
        out(['sucesso'=>false,'msg'=>'Ação inválida'],400);
}
