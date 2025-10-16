<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../sessao-professor.php'; 
require_once __DIR__ . '/../conexao.php';       
require_once __DIR__ . '/../conexao-bncc.php';   

header('Content-Type: application/json; charset=utf-8');

$acao = $_REQUEST['acao'] ?? '';

try {
    // 1. BUSCA TODOS OS PLANEJAMENTOS DO USUÁRIO
    if ($acao === 'buscar_todos') {
        $termo = $_GET['pesquisa'] ?? '';
        $sql = "SELECT * FROM planejamento_mensal 
                WHERE (nome LIKE :pesquisa OR periodo LIKE :pesquisa)
                AND login = :login
                ORDER BY created_date DESC";
        $stmt = $conexao->prepare($sql);
        $stmt->bindValue(':pesquisa', '%'.$termo.'%', PDO::PARAM_STR);
        $stmt->bindValue(':login', $_SESSION['id'], PDO::PARAM_INT);
        $stmt->execute();
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['sucesso' => true, 'data' => $dados]);
        exit;
    }

    // 2. BUSCAR UM PLANEJAMENTO PARA EDIÇÃO (cabeçalho + linhas)
    elseif ($acao === 'buscar' && !empty($_GET['id'])) {
        $id = intval($_GET['id']);
        $stmt = $conexao->prepare("SELECT * FROM planejamento_mensal WHERE id = :id AND login = :login");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':login', $_SESSION['id'], PDO::PARAM_INT);
        $stmt->execute();
        $cabecalho = $stmt->fetch(PDO::FETCH_ASSOC);

        // Buscar as linhas desse planejamento
        $stmtLinhas = $conexao->prepare("SELECT * FROM planejamento_mensal_linhas WHERE planejamentoMensal = :id");
        $stmtLinhas->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtLinhas->execute();
        $linhas = $stmtLinhas->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($cabecalho 
            ? ['sucesso' => true, 'cabecalho' => $cabecalho, 'linhas' => $linhas] 
            : ['sucesso' => false, 'mensagem' => 'Planejamento não encontrado.']
        );
        exit;
    }

    // 3. CRIAR UM NOVO PLANEJAMENTO (CABEÇALHO + LINHAS)
    elseif ($acao === 'criar') {
        // ----- CABEÇALHO
        $campos = [
            'nome' => trim($_POST['nome-plano-mensal'] ?? ''),
            'materia' => intval($_POST['materia'] ?? 0),
            'escola' => $_SESSION['escola'],
            'periodo' => trim($_POST['periodo_realizacao'] ?? ''),
            'numeroDeAulas' => intval($_POST['numero_aulas_semanais'] ?? 0),
            'anosDoPlano' => isset($_POST['anos_plano']) ? implode(',', $_POST['anos_plano']) : '',
            'objetivoGeral' => trim($_POST['objetivo_geral'] ?? ''),
            'objetivoEspecifico' => trim($_POST['objetivo_especifico'] ?? ''),
            'login' => $_SESSION['id'],
            'created_date' => date('Y-m-d H:i:s'),
            'updated_date' => date('Y-m-d H:i:s'),
        ];
        // Validação mínima
        $erros = [];
        if (empty($campos['nome'])) $erros[] = "O campo Nome é obrigatório.";
        if (empty($campos['materia'])) $erros[] = "O campo Matéria é obrigatório.";
        if (empty($campos['numeroDeAulas'])) $erros[] = "O campo Número de Aulas Semanais é obrigatório.";
        if ($erros) {
            echo json_encode(['sucesso' => false, 'mensagem' => implode(" ", $erros)]);
            exit;
        }

        // INSERT CABEÇALHO
        $sql = "INSERT INTO planejamento_mensal
            (nome, materia, escola, periodo, numeroDeAulas, anosDoPlano, objetivoGeral, objetivoEspecifico, login, created_date, updated_date)
            VALUES
            (:nome, :materia, :escola, :periodo, :numeroDeAulas, :anosDoPlano, :objetivoGeral, :objetivoEspecifico, :login, :created_date, :updated_date)";
        $stmt = $conexao->prepare($sql);
        foreach ($campos as $chave => $valor) {
            $stmt->bindValue(':' . $chave, $valor);
        }
        $stmt->execute();
        $id_planejamento = $conexao->lastInsertId();

        // ----- LINHAS
        if (!empty($_POST['linhas'])) {
            foreach ($_POST['linhas'] as $linha) {
                // Só insere linhas com ao menos um dado preenchido
                $temDado = (
                    !empty($linha['etapa']) ||
                    !empty($linha['ano']) ||
                    !empty($linha['area']) ||
                    !empty($linha['componente_curricular']) ||
                    !empty($linha['unidade_tematica']) ||
                    !empty($linha['objeto_do_conhecimento']) ||
                    (isset($linha['habilidades']) && !empty($linha['habilidades']))
                );
                if (!$temDado) continue;

                // $linha['habilidades'] pode vir como array ou string
                $habilidades = '';
                if (isset($linha['habilidades'])) {
                    if (is_array($linha['habilidades'])) {
                        $habilidades = implode(',', $linha['habilidades']);
                    } else {
                        $habilidades = $linha['habilidades'];
                    }
                }
                $sqlLinha = "INSERT INTO planejamento_mensal_linhas
                    (planejamentoMensal, etapa, ano, areaConhecimento, componenteCurricular, unidadeTematicas, objetosConhecimento, habilidades, conteudos, metodologias, created_date, updated_date)
                    VALUES
                    (:planejamentoMensal, :etapa, :ano, :areaConhecimento, :componenteCurricular, :unidadeTematicas, :objetosConhecimento, :habilidades, :conteudos, :metodologias, NOW(), NOW())";
                $stmtLinha = $conexao->prepare($sqlLinha);
                $stmtLinha->bindValue(':planejamentoMensal', $id_planejamento, PDO::PARAM_INT);
                $stmtLinha->bindValue(':etapa', $linha['etapa'] ?? null);
                $stmtLinha->bindValue(':ano', $linha['ano'] ?? null);
                $stmtLinha->bindValue(':areaConhecimento', $linha['area'] ?? null);
                $stmtLinha->bindValue(':componenteCurricular', $linha['componente_curricular'] ?? null);
                $stmtLinha->bindValue(':unidadeTematicas', $linha['unidade_tematica'] ?? null);
                $stmtLinha->bindValue(':objetosConhecimento', $linha['objeto_do_conhecimento'] ?? null);
                $stmtLinha->bindValue(':habilidades', $habilidades);
                $stmtLinha->bindValue(':conteudos', $linha['conteudos'] ?? '');
                $stmtLinha->bindValue(':metodologias', $linha['metodologias'] ?? '');
                $stmtLinha->execute();
            }
        }
        echo json_encode(['sucesso' => true, 'mensagem' => 'Planejamento cadastrado com sucesso!']);
        exit;
    }

    // 4. EDITAR UM PLANEJAMENTO (CABEÇALHO + LINHAS)
    elseif ($acao === 'editar') {
        $id = intval($_POST['id-planejamento-mensal'] ?? 0);
        $campos = [
            'nome' => trim($_POST['nome-plano-mensal'] ?? ''),
            'materia' => intval($_POST['materia'] ?? 0),
            'escola' => $_SESSION['escola'],
            'periodo' => trim($_POST['periodo_realizacao'] ?? ''),
            'numeroDeAulas' => intval($_POST['numero_aulas_semanais'] ?? 0),
            'anosDoPlano' => isset($_POST['anos_plano']) ? implode(',', $_POST['anos_plano']) : '',
            'objetivoGeral' => trim($_POST['objetivo_geral'] ?? ''),
            'objetivoEspecifico' => trim($_POST['objetivo_especifico'] ?? ''),
            'updated_date' => date('Y-m-d H:i:s'),
        ];
        // UPDATE CABEÇALHO
        $sql = "UPDATE planejamento_mensal SET
            nome = :nome,
            materia = :materia,
            escola = :escola,
            periodo = :periodo,
            numeroDeAulas = :numeroDeAulas,
            anosDoPlano = :anosDoPlano,
            objetivoGeral = :objetivoGeral,
            objetivoEspecifico = :objetivoEspecifico,
            updated_date = :updated_date
            WHERE id = :id AND login = :login";
        $stmt = $conexao->prepare($sql);
        foreach ($campos as $chave => $valor) {
            $stmt->bindValue(':' . $chave, $valor);
        }
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':login', $_SESSION['id'], PDO::PARAM_INT);
        $stmt->execute();

        // REMOVE TODAS AS LINHAS ANTERIORES
        $conexao->prepare("DELETE FROM planejamento_mensal_linhas WHERE planejamentoMensal = :id")->execute([':id' => $id]);

        // INSERE NOVAS LINHAS
        if (!empty($_POST['linhas'])) {
            foreach ($_POST['linhas'] as $linha) {
                $temDado = (
                    !empty($linha['etapa']) ||
                    !empty($linha['ano']) ||
                    !empty($linha['area']) ||
                    !empty($linha['componente_curricular']) ||
                    !empty($linha['unidade_tematica']) ||
                    !empty($linha['objeto_do_conhecimento']) ||
                    (isset($linha['habilidades']) && !empty($linha['habilidades']))
                );
                if (!$temDado) continue;
                $habilidades = '';
                if (isset($linha['habilidades'])) {
                    if (is_array($linha['habilidades'])) {
                        $habilidades = implode(',', $linha['habilidades']);
                    } else {
                        $habilidades = $linha['habilidades'];
                    }
                }
                $sqlLinha = "INSERT INTO planejamento_mensal_linhas
                    (planejamentoMensal, etapa, ano, areaConhecimento, componenteCurricular, unidadeTematicas, objetosConhecimento, habilidades, conteudos, metodologias, created_date, updated_date)
                    VALUES
                    (:planejamentoMensal, :etapa, :ano, :areaConhecimento, :componenteCurricular, :unidadeTematicas, :objetosConhecimento, :habilidades, :conteudos, :metodologias, NOW(), NOW())";
                $stmtLinha = $conexao->prepare($sqlLinha);
                $stmtLinha->bindValue(':planejamentoMensal', $id, PDO::PARAM_INT);
                $stmtLinha->bindValue(':etapa', $linha['etapa'] ?? null);
                $stmtLinha->bindValue(':ano', $linha['ano'] ?? null);
                $stmtLinha->bindValue(':areaConhecimento', $linha['area'] ?? null);
                $stmtLinha->bindValue(':componenteCurricular', $linha['componente_curricular'] ?? null);
                $stmtLinha->bindValue(':unidadeTematicas', $linha['unidade_tematica'] ?? null);
                $stmtLinha->bindValue(':objetosConhecimento', $linha['objeto_do_conhecimento'] ?? null);
                $stmtLinha->bindValue(':habilidades', $habilidades);
                $stmtLinha->bindValue(':conteudos', $linha['conteudos'] ?? '');
                $stmtLinha->bindValue(':metodologias', $linha['metodologias'] ?? '');
                $stmtLinha->execute();
            }
        }
        echo json_encode(['sucesso' => true, 'mensagem' => 'Planejamento alterado com sucesso!']);
        exit;
    }

    // 5. EXCLUIR UM PLANEJAMENTO (cabeçalho + linhas)
    elseif ($acao === 'excluir' && !empty($_POST['id'])) {
        $id = intval($_POST['id']);
        $conexao->prepare("DELETE FROM planejamento_mensal_linhas WHERE planejamentoMensal = :id")->execute([':id' => $id]);
        $stmt = $conexao->prepare("DELETE FROM planejamento_mensal WHERE id = :id AND login = :login");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':login', $_SESSION['id'], PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode(['sucesso' => true, 'mensagem' => 'Planejamento excluído com sucesso!']);
        exit;
    }

    // 6. BUSCAR DADOS DA BNCC PARA POPULAR SELECTS
    elseif ($acao === 'bncc' && !empty($_GET['campo'])) {
        $campo = $_GET['campo'];
        $lista = [];

        if ($campo === 'etapas') {
            $sql = "SELECT id, nome as label FROM bncc_etapas ORDER BY nome";
            $stmt = $conexao_bncc->query($sql);
        } elseif ($campo === 'anos' && isset($_GET['id_etapa'])) {
            $id_etapa = (int)$_GET['id_etapa'];
            $sql = "SELECT ano FROM bncc_anos WHERE id_etapa = $id_etapa";
            $stmt = $conexao_bncc->query($sql);
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            $anos = [];
            foreach ($result as $linha) {
                foreach (explode(',', $linha) as $ano) {
                    $ano = trim($ano);
                    if ($ano !== '' && !in_array($ano, $anos)) {
                        $anos[] = $ano;
                    }
                }
            }
            $lista = [];
            foreach ($anos as $index => $ano) {
                $lista[] = ['id' => $ano, 'label' => $ano];
            }
            echo json_encode($lista);
            exit;
        } elseif ($campo === 'areas') {
            $sql = "SELECT id, nome as label FROM bncc_areas ORDER BY nome";
            $stmt = $conexao_bncc->query($sql);
        } elseif ($campo === 'componentes' && isset($_GET['id_area'])) {
            $id_area = (int)$_GET['id_area'];
            $sql = "SELECT id, nome as label FROM bncc_componentes WHERE id_area = $id_area ORDER BY nome";
            $stmt = $conexao_bncc->query($sql);
        } elseif ($campo === 'unidades_tematicas' && isset($_GET['id_componente'])) {
            $id_componente = (int)$_GET['id_componente'];
            $sql = "SELECT id, nome as label FROM bncc_unidades_tematicas WHERE id_componente = $id_componente ORDER BY nome";
            $stmt = $conexao_bncc->query($sql);
        } elseif ($campo === 'objetos_conhecimento' && isset($_GET['id_unidade_tematica'])) {
            $id_unidade_tematica = (int)$_GET['id_unidade_tematica'];
            $sql = "SELECT id, nome as label FROM bncc_objetos_conhecimento WHERE id_unidade_tematica = $id_unidade_tematica ORDER BY nome";
            $stmt = $conexao_bncc->query($sql);
        } elseif ($campo === 'habilidades' && isset($_GET['id_objeto'])) {
            $id_objeto = (int)$_GET['id_objeto'];
            $sql = "SELECT id, CONCAT(codigo, ' - ', descricao) as label FROM bncc_habilidades WHERE id_objeto = $id_objeto ORDER BY codigo";
            $stmt = $conexao_bncc->query($sql);
        } else {
            echo json_encode([]);
            exit;
        }
        $raw = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $lista = array_values(array_filter($raw, function($row) {
            return isset($row['label']) && trim($row['label']) !== '';
        }));
        echo json_encode($lista);
        exit;
    }

    // 7. BUSCAR MATÉRIAS DO PROFESSOR
    elseif ($acao === 'materias_do_professor') {
        $id_professor = $_SESSION['id_professor'] ?? ($_SESSION['id'] ?? null);
        file_put_contents(__DIR__ . '/debug-materias.txt', print_r([
            'id_professor' => $id_professor,
            '_SESSION' => $_SESSION
        ], true), FILE_APPEND);

        if (!$id_professor) {
            echo json_encode([]);
            exit;
        }
        $stmt = $conexao->prepare("SELECT id, nome as label FROM materias WHERE id_professor = :id_professor ORDER BY nome");
        $stmt->bindValue(':id_professor', $id_professor, PDO::PARAM_INT);
        $stmt->execute();
        $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($materias);
        exit;
    }

    // 8. AÇÃO INVÁLIDA
    else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Ação inválida.']);
        exit;
    }

} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro inesperado: ' . $e->getMessage()]);
    exit;
}
?>
