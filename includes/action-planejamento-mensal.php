<?php
// action-planejamento-mensal.php
// Gerencia todas as ações de CRUD do Planejamento Mensal
// Suporta perfil Professor (filtra por login) e Administrador (acesso total)

file_put_contents(__DIR__ . '/log_post_debug.txt', print_r($_POST, true) . "\n", FILE_APPEND);
ob_start();

ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../sessao-adm-professor.php';
require_once __DIR__ . '/../conexao.php';       // $conexao
require_once __DIR__ . '/../conexao-bncc.php';  // $conexao_bncc

header('Content-Type: application/json; charset=utf-8');

function sendJson(array $payload): void
{
    $garbage = ob_get_clean();
    if ($garbage) {
        error_log('LIXO NA SAÍDA: ' . $garbage);
    }
    echo json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );
    exit;
}

function mapLinhaBDToFrontend(array $linhaBD): array
{
    return [
        'id'                    => $linhaBD['id'],
        'etapa'                 => $linhaBD['etapa'],
        'ano'                   => $linhaBD['ano'],
        'area'                  => $linhaBD['areaConhecimento'],
        'componenteCurricular'  => $linhaBD['componenteCurricular'],
        'unidadeTematicas'      => $linhaBD['unidadeTematicas'],
        'objetosConhecimento'   => $linhaBD['objetosConhecimento'],
        'habilidades'           => ($linhaBD['habilidades'] !== '' ? explode(',', $linhaBD['habilidades']) : []),
        'conteudos'             => $linhaBD['conteudos'],
        'metodologias'          => $linhaBD['metodologias'],
        'grupo'                 => $linhaBD['grupo'],
    ];
}

$acao    = $_REQUEST['acao'] ?? '';
$userId  = $_SESSION['id'];
$isAdmin = $_SESSION['perfil'] === 'Administrador';

try {
    // 1. BUSCAR TODOS
    if ($acao === 'buscar_todos') {
        $termo = $_GET['pesquisa'] ?? '';
        if ($isAdmin) {
            $sql = "SELECT * FROM planejamento
                    WHERE nome LIKE :pesquisa OR periodo LIKE :pesquisa
                    ORDER BY created_date DESC";
            $stmt = $conexao->prepare($sql);
            $stmt->bindValue(':pesquisa', "%{$termo}%", PDO::PARAM_STR);
        } else {
            $sql = "SELECT * FROM planejamento
                    WHERE (nome LIKE :pesquisa OR periodo LIKE :pesquisa)
                      AND login = :login
                    ORDER BY created_date DESC";
            $stmt = $conexao->prepare($sql);
            $stmt->bindValue(':pesquisa', "%{$termo}%", PDO::PARAM_STR);
            $stmt->bindValue(':login', $userId, PDO::PARAM_INT);
        }
        $stmt->execute();
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        sendJson(['sucesso' => true, 'data' => $dados]);
    }

    // 2. BUSCAR UM (cabeçalho + linhas)
    elseif ($acao === 'buscar' && !empty($_GET['id'])) {
        $id = (int) $_GET['id'];
        // Cabeçalho
        if ($isAdmin) {
            $sqlCab = "SELECT * FROM planejamento WHERE id = :id";
            $stmt = $conexao->prepare($sqlCab);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $sqlCab = "SELECT * FROM planejamento WHERE id = :id AND login = :login";
            $stmt = $conexao->prepare($sqlCab);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->bindValue(':login', $userId, PDO::PARAM_INT);
            $stmt->execute();
        }
        $cabecalho = $stmt->fetch(PDO::FETCH_ASSOC);

        // Linhas
        $sqlLin = "SELECT * FROM planejamento_linhas WHERE planejamento = :id ORDER BY id";
        $stmtLin = $conexao->prepare($sqlLin);
        $stmtLin->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtLin->execute();
        $linhasBD = $stmtLin->fetchAll(PDO::FETCH_ASSOC);
        $linhas   = array_map('mapLinhaBDToFrontend', $linhasBD);

        // Mapeia cabeçalho para frontend
        $cabF = [
            'id-planejamento-mensal' => $cabecalho['id'] ?? '',
            'nome-plano-mensal'      => $cabecalho['nome'] ?? '',
            'materia'                => $cabecalho['materia'] ?? '',
            'escola'                 => $cabecalho['escola'] ?? '',
            'professor'              => $cabecalho['professor'] ?? '',
            'curso'                  => $cabecalho['curso'] ?? '',
            'ano'                    => $cabecalho['ano'] ?? '',
            'anos_plano'             => $cabecalho['anosDoPlano'] ?? '',
            'periodo_realizacao'     => $cabecalho['periodo'] ?? '',
            'componente_curricular'  => $cabecalho['componenteCurricular'] ?? '',
            'numero_aulas_semanais'  => $cabecalho['numeroDeAulas'] ?? '',
            'objetivo_geral'         => $cabecalho['objetivoGeral'] ?? '',
            'objetivo_especifico'    => $cabecalho['objetivoEspecifico'] ?? '',
            'tipo'                   => $cabecalho['tipo'] ?? '',
            'sequencial'             => $cabecalho['sequencial'] ?? '',
            'projetos_integrador'    => $cabecalho['projetosIntegrador'] ?? '',
            'unidade_tematica'       => $cabecalho['unidadeTematica'] ?? '',
            'objeto_do_conhecimento' => $cabecalho['objetoDoConhecimento'] ?? '',
            'grupo'                  => $cabecalho['grupo'] ?? '',
            'conteudos'              => $cabecalho['conteudos'] ?? '',
            'habilidades'            => $cabecalho['habilidades'] ?? '',
            'metodologias'           => $cabecalho['metodologias'] ?? '',
            'diagnostico'            => $cabecalho['diagnostico'] ?? '',
            'referencias'            => $cabecalho['referencias'] ?? '',
            'created_date'           => $cabecalho['created_date'] ?? '',
            'updated_date'           => $cabecalho['updated_date'] ?? '',
            'login'                  => $cabecalho['login'] ?? '',
            'tempo'                  => $cabecalho['tempo'] ?? '',
        ];

        if ($cabecalho) {
            sendJson(['sucesso' => true, 'cabecalho' => $cabF, 'linhas' => $linhas]);
        } else {
            sendJson(['sucesso' => false, 'mensagem' => 'Planejamento não encontrado.']);
        }
    }
    // 3. CRIAR CABEÇALHO + LINHAS
    elseif ($acao === 'criar') {
        file_put_contents(__DIR__ . '/log_post.txt', print_r($_POST, true), FILE_APPEND);
        // 3.1 Cabeçalho
        $nome      = trim($_POST['nome-plano-mensal'] ?? '');
        $materia   = (int) ($_POST['materia'] ?? 0);
        $periodo   = trim($_POST['periodo_realizacao'] ?? '');
        $numAulas  = (int) ($_POST['numero_aulas_semanais'] ?? 0);
        $anosPlano = isset($_POST['anos_plano']) ? implode(',', (array)$_POST['anos_plano']) : '';
        $objGeral  = trim($_POST['objetivo_geral'] ?? '');
        $objEsp    = trim($_POST['objetivo_especifico'] ?? '');
        $tempo     = (int) ($_POST['tempo'] ?? 1);

        // validações mínimas
        $erros = [];
        if ($nome === '')    $erros[] = 'O campo Nome é obrigatório.';
        if ($materia <= 0)   $erros[] = 'O campo Matéria é obrigatório.';
        if ($numAulas <= 0)  $erros[] = 'Número de aulas deve ser ≥1.';

        if ($erros) {
            sendJson([
                'sucesso'  => false,
                'mensagem' => implode(' ', $erros),
            ]);
        }

        $pdo = $conexao;
        $pdo->beginTransaction();

        $sql = "INSERT INTO planejamento
            (nome, materia, escola, periodo, numeroDeAulas, anosDoPlano,
             objetivoGeral, objetivoEspecifico, login, created_date, updated_date, tempo)
         VALUES
            (:nome, :materia, :escola, :periodo, :numAulas, :anosPlano,
             :objGeral, :objEsp, :login, NOW(), NOW(), :tempo)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome'      => $nome,
            ':materia'   => $materia,
            ':escola'    => isset($_POST['escola']) && $_POST['escola'] !== ''
                                ? trim($_POST['escola'])
                                : null,
            ':periodo'   => $periodo,
            ':numAulas'  => $numAulas,
            ':anosPlano' => $anosPlano,
            ':objGeral'  => $objGeral,
            ':objEsp'    => $objEsp,
            ':login'     => $userId,
            ':tempo'     => $tempo,
        ]);
        $idPai = $pdo->lastInsertId();

        // 3.2 Linhas
        $linhasJson = $_POST['linhas_serializadas'] ?? '[]';
        $linhasArr  = json_decode($linhasJson, true);
        $ins = $pdo->prepare(
            "INSERT INTO planejamento_linhas
             (planejamento, etapa, ano, areaConhecimento,
              componenteCurricular, unidadeTematicas, objetosConhecimento,
              habilidades, conteudos, metodologias, created_date, updated_date, grupo)
             VALUES
             (:pai, :etapa, :ano, :area, :comp, :uni, :obj, :hab, :cont, :met, NOW(), NOW(), :grupo)"
        );
        foreach ($linhasArr as $l) {
            if (empty($l['etapa'])) {
                continue;
            }
            $ins->execute([
                ':pai'  => $idPai,
                ':etapa'=> $l['etapa'],
                ':ano'  => $l['ano'] ?? null,
                ':area' => $l['area'] ?? null,
                ':comp' => $l['componenteCurricular'] ?? null,
                ':uni'  => $l['unidadeTematicas'] ?? null,
                ':obj'  => $l['objetosConhecimento'] ?? null,
                ':hab'  => is_array($l['habilidades'])
                          ? implode(',', $l['habilidades'])
                          : ($l['habilidades'] ?? ''),
                ':cont' => $l['conteudos'] ?? '',
                ':met'  => $l['metodologias'] ?? '',
                ':grupo'=> $l['grupo'] ?? '',
            ]);
        }

        $pdo->commit();
        sendJson([
            'sucesso'  => true,
            'mensagem' => 'Planejamento cadastrado com sucesso!'
        ]);
    }

    // 4. EDITAR
    elseif ($acao === 'editar') {
        $id        = (int) ($_POST['id-planejamento-mensal'] ?? 0);
        $nome      = trim($_POST['nome-plano-mensal'] ?? '');
        $materia   = (int) ($_POST['materia'] ?? 0);
        $periodo   = trim($_POST['periodo_realizacao'] ?? '');
        $numAulas  = (int) ($_POST['numero_aulas_semanais'] ?? 0);
        $anosPlano = isset($_POST['anos_plano']) ? implode(',', (array)$_POST['anos_plano']) : '';
        $objGeral  = trim($_POST['objetivo_geral'] ?? '');
        $objEsp    = trim($_POST['objetivo_especifico'] ?? '');
        $tempo     = (int) ($_POST['tempo'] ?? 1);

        $pdo = $conexao;
        $pdo->beginTransaction();

        $updCab = $pdo->prepare("
            UPDATE planejamento SET
                nome               = :nome,
                materia            = :materia,
                escola             = :escola,
                periodo            = :periodo,
                numeroDeAulas      = :numAulas,
                anosDoPlano        = :anosPlano,
                objetivoGeral      = :objGeral,
                objetivoEspecifico = :objEsp,
                tempo              = :tempo,
                updated_date       = NOW()
            WHERE id = :id " . ($isAdmin ? '' : 'AND login = :login')
        );
        $bind = [
            ':nome'      => $nome,
            ':materia'   => $materia,
            ':escola'    => $_POST['escola'] ?? null,
            ':periodo'   => $periodo,
            ':numAulas'  => $numAulas,
            ':anosPlano' => $anosPlano,
            ':objGeral'  => $objGeral,
            ':objEsp'    => $objEsp,
            ':tempo'     => $tempo,
            ':id'        => $id,
        ];
        if (!$isAdmin) $bind[':login'] = $userId;
        $updCab->execute($bind);

        // Linhas serializadas
        $linhasArr = json_decode($_POST['linhas_serializadas'] ?? '[]', true);

        $updLin = $pdo->prepare("
            UPDATE planejamento_linhas SET
                etapa                = :etapa,
                ano                  = :ano,
                areaConhecimento     = :area,
                componenteCurricular = :comp,
                unidadeTematicas     = :uni,
                objetosConhecimento  = :obj,
                habilidades          = :hab,
                conteudos            = :cont,
                metodologias         = :met,
                grupo                = :grupo,
                updated_date         = NOW()
            WHERE id = :id AND planejamento = :pai
        ");

        $insLin = $pdo->prepare("
            INSERT INTO planejamento_linhas
                (planejamento, etapa, ano, areaConhecimento,
                 componenteCurricular, unidadeTematicas, objetosConhecimento,
                 habilidades, conteudos, metodologias, created_date, updated_date, grupo)
            VALUES
                (:pai, :etapa, :ano, :area, :comp, :uni, :obj,
                 :hab, :cont, :met, NOW(), NOW(), :grupo)
        ");

        foreach ($linhasArr as $l) {
            if (empty($l['etapa'])) continue;
            $csvHab = is_array($l['habilidades'])
                        ? implode(',', $l['habilidades'])
                        : ($l['habilidades'] ?? '');
            $bind = [
                ':etapa' => $l['etapa'],
                ':ano'   => $l['ano'] ?? null,
                ':area'  => $l['area'] ?? null,
                ':comp'  => $l['componenteCurricular'] ?? null,
                ':uni'   => $l['unidadeTematicas'] ?? null,
                ':obj'   => $l['objetosConhecimento'] ?? null,
                ':hab'   => $csvHab,
                ':cont'  => $l['conteudos'] ?? '',
                ':met'   => $l['metodologias'] ?? '',
                ':grupo' => $l['grupo'] ?? 1,
                ':pai'   => $id,
            ];
            if (!empty($l['id'])) {
                $updLin->execute($bind + [':id' => $l['id']]);
            } else {
                $insLin->execute($bind);
            }
        }

        $pdo->commit();
        sendJson([
            'sucesso'  => true,
            'mensagem' => 'Planejamento alterado com sucesso!'
        ]);
    }

    // 5. EXCLUIR PLANEJAMENTO
    elseif ($acao === 'excluir') {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : null;

        if (!$id) {
            sendJson([
                'sucesso' => false,
                'mensagem' => 'ID do planejamento não informado.'
            ]);
        }

        try {
            $pdo = $conexao;
            $pdo->beginTransaction();

            $pdo->prepare(
                "DELETE FROM planejamento_linhas WHERE planejamento = :id"
            )->execute([':id' => $id]);

            $stmt = $pdo->prepare(
                "DELETE FROM planejamento 
                 WHERE id = :id " . ($isAdmin ? '' : 'AND login = :login')
            );

            if ($isAdmin) {
                $stmt->execute([':id' => $id]);
            } else {
                $stmt->execute([
                    ':id'    => $id,
                    ':login' => $userId,
                ]);
            }

            $pdo->commit();

            sendJson([
                'sucesso'  => true,
                'mensagem' => 'Planejamento excluído com sucesso!'
            ]);
        } catch (Exception $e) {
            $pdo->rollBack();
            sendJson([
                'sucesso'  => false,
                'mensagem' => 'Erro ao excluir planejamento: ' . $e->getMessage()
            ]);
        }
    }

    // 6. POPULAR BNCC via AJAX
    elseif ($acao === 'bncc' && !empty($_GET['campo'])) {
        $campo = $_GET['campo'];
        $lista = [];

        switch ($campo) {
            case 'etapas':
                $stmt = $conexao_bncc->query(
                    "SELECT id, nome AS label FROM bncc_etapas ORDER BY nome"
                );
                break;
            case 'anos':
                if (!empty($_GET['id_etapa'])) {
                    $e = (int) $_GET['id_etapa'];
                    $stmt = $conexao_bncc->prepare(
                        "SELECT ano FROM bncc_anos WHERE id_etapa = :e"
                    );
                    $stmt->execute([':e' => $e]);
                    $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    $anos = array_unique(
                        array_filter(
                            array_map('trim', explode(',', implode(',', $result)))
                        )
                    );
                    foreach ($anos as $a) {
                        $lista[] = ['id' => $a, 'label' => $a];
                    }
                    echo json_encode($lista);
                    exit;
                }
                break;
            case 'areas':
                $stmt = $conexao_bncc->query(
                    "SELECT id, nome AS label FROM bncc_areas ORDER BY nome"
                );
                break;
            case 'componentes':
                if (!empty($_GET['id_area'])) {
                    $a = (int) $_GET['id_area'];
                    $stmt = $conexao_bncc->prepare(
                        "SELECT id, nome AS label FROM bncc_componentes 
                         WHERE id_area = :a ORDER BY nome"
                    );
                    $stmt->execute([':a' => $a]);
                }
                break;
            case 'unidades_tematicas':
                if (!empty($_GET['id_componente'])) {
                    $c = (int) $_GET['id_componente'];
                    $stmt = $conexao_bncc->prepare(
                        "SELECT id, nome AS label FROM bncc_unidades_tematicas 
                         WHERE id_componente = :c ORDER BY nome"
                    );
                    $stmt->execute([':c' => $c]);
                }
                break;
            case 'objetosConhecimento':
                if (!empty($_GET['id_unidade_tematica'])) {
                    $u = (int) $_GET['id_unidade_tematica'];
                    $stmt = $conexao_bncc->prepare(
                        "SELECT id, nome AS label FROM bncc_objetos_conhecimento 
                         WHERE id_unidade_tematica = :u ORDER BY nome"
                    );
                    $stmt->execute([':u' => $u]);
                }
                break;
            case 'habilidades':
                if (!empty($_GET['id_objeto'])) {
                    $o = (int) $_GET['id_objeto'];
                    $stmt = $conexao_bncc->prepare(
                        "SELECT id, CONCAT(codigo,' – ',descricao) AS label 
                         FROM bncc_habilidades WHERE id_objeto = :o ORDER BY codigo"
                    );
                    $stmt->execute([':o' => $o]);
                }
                break;
            default:
                echo json_encode([]); exit;
        }

        if (isset($stmt)) {
            $lista = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($lista);
        } else {
            echo json_encode([]);
        }
        exit;
    }

    // 7. MATÉRIAS DO PROFESSOR
    elseif ($acao === 'materias_do_professor') {
        $idProf = $_SESSION['id_professor'] ?? $userId;
        if (!$idProf) {
            echo json_encode([]); exit;
        }
        if (!$isAdmin) {
            $stmt = $conexao->prepare(
                "SELECT id, nome AS label FROM materias 
                 WHERE id_professor = :p ORDER BY nome"
            );
            $stmt->execute([':p' => $idProf]);
        } else {
            $stmt = $conexao->query(
                "SELECT id, nome AS label FROM materias ORDER BY nome"
            );
        }
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    // 8. LISTA DE PERIODOS
    elseif ($acao === 'listar_ciclos') {
        $stmt = $conexao->prepare(
            "SELECT id, nome, quantidadeMeses
             FROM planejamento_periodos
             ORDER BY id"
        );
        $stmt->execute();
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    // 9. Detalhe de Periodos
    elseif ($acao === 'detalhe_ciclo' && !empty($_GET['id'])) {
        $id = (int) $_GET['id'];
        $stmt = $conexao->prepare("SELECT nome, quantidadeMeses FROM planejamento_periodos WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $ciclo = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($ciclo) {
            echo json_encode([
                'sucesso' => true,
                'nome' => $ciclo['nome'],
                'quantidadeMeses' => (int)$ciclo['quantidadeMeses']
            ]);
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Ciclo não encontrado']);
        }
        exit;
    }

    // 10. linhas
    elseif ($acao === 'html_add_linha') {
        ob_start();
        include 'add-linha.php';
        $html = ob_get_clean();

        echo $html;
        exit;
    }

    // AÇÃO INVÁLIDA
    else {
        sendJson([
            'sucesso'  => false,
            'mensagem' => 'Ação inválida.'
        ]);
    }

} catch (Exception $e) {
    sendJson([
        'sucesso'  => false,
        'mensagem' => 'Erro inesperado: ' . $e->getMessage()
    ]);
}
?>
