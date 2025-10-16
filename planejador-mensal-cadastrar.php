<?php
require('conexao.php');
require('sessao-professor.php');

// Inicializa variáveis para evitar erros - preenche com cookies se existirem
$campos = [
    'curso' => $_COOKIE['planejamento_curso'] ?? '',
    'componente_curricular' => $_COOKIE['planejamento_componente_curricular'] ?? '',
    'objetivo_geral' => $_COOKIE['planejamento_objetivo_geral'] ?? '',
    'objetivo_especifico' => $_COOKIE['planejamento_objetivo_especifico'] ?? '',
    'escola' => $_SESSION['escola'] ?? '',
    'professor' => $_SESSION['nome'] ?? '',
    'login' => $_SESSION['login'] ?? '',
    'ano' => $_COOKIE['planejamento_ano'] ?? '',
    'numero_aulas_semanais' => $_COOKIE['planejamento_numero_aulas_semanais'] ?? '',
    'periodo_realizacao' => $_COOKIE['planejamento_periodo_realizacao'] ?? '',
    'unidade_tematica' => '',
    'objeto_do_conhecimento' => $_COOKIE['planejamento_objeto_do_conhecimento'] ?? '',
    'conteudos' => '',
    'habilidades' => '',
    'metodologias' => ''
];

$erros = [];

// Processa o formulário quando é submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitiza e valida os dados para salvar
    $campos = [
        'data' => date('Y-m-d'),
        'login' => $_SESSION['login'],
        'escola' => $_SESSION['escola'],
        'professor' => $_SESSION['nome'],
        'curso' => trim($_POST['curso'] ?? ''),
        'ano' => trim($_POST['ano'] ?? ''),
        'periodo_realizacao' => trim($_POST['periodo_realizacao'] ?? ''),
        'componente_curricular' => trim($_POST['componente_curricular'] ?? ''),
        'numero_aulas_semanais' => trim($_POST['numero_aulas_semanais'] ?? ''),
        'objetivo_geral' => trim($_POST['objetivo_geral'] ?? ''),
        'objetivo_especifico' => trim($_POST['objetivo_especifico'] ?? ''),
        'unidade_tematica' => trim($_POST['unidade_tematica'] ?? ''),
        'objeto_do_conhecimento' => trim($_POST['objeto_do_conhecimento'] ?? ''),
        'conteudos' => trim($_POST['conteudos'] ?? ''),
        'habilidades' => trim($_POST['habilidades'] ?? ''),
        'metodologias' => trim($_POST['metodologias'] ?? '')
    ];

    // Validação básica
    if (empty($campos['professor'])) {
        $erros[] = "O campo Professor é obrigatório.";
    }
    if (empty($campos['escola'])) {
        $erros[] = "O campo Escola é obrigatório.";
    }
    if (empty($campos['curso'])) {
        $erros[] = "O campo Curso é obrigatório.";
    }
    if (empty($campos['componente_curricular'])) {
        $erros[] = "O campo Componente Curricular é obrigatório.";
    }
    if (empty($campos['numero_aulas_semanais'])) {
        $erros[] = "O campo Número de Aulas Semanais é obrigatório.";
    } elseif (!is_numeric($campos['numero_aulas_semanais']) || $campos['numero_aulas_semanais'] <= 0) {
        $erros[] = "O campo Número de Aulas Semanais deve ser um número positivo.";
    }
    
    // Validação adicional para ano se preenchido
    if (!empty($campos['ano']) && (!is_numeric($campos['ano']) || $campos['ano'] < 1900 || $campos['ano'] > 2100)) {
        $erros[] = "O campo Ano deve ser um ano válido.";
    }

    // Se não houver erros, insere no banco de dados
    if (empty($erros)) {
        try {
            $sql = "INSERT INTO planejadormensal (
                data, login, escola, professor, curso, ano, periodo_realizacao, 
                componente_curricular, numero_aulas_semanais, objetivo_geral, 
                objetivo_especifico, unidade_tematica, 
                objeto_do_conhecimento, conteudos, habilidades, metodologias
            ) VALUES (
                :data, :login, :escola, :professor, :curso, :ano, :periodo_realizacao,
                :componente_curricular, :numero_aulas_semanais, :objetivo_geral,
                :objetivo_especifico, :unidade_tematica, 
                :objeto_do_conhecimento, :conteudos, :habilidades, :metodologias
            )";
            
            $stmt = $conexao->prepare($sql);
            $stmt->execute([
                'data' => $campos['data'],
                'login' => $campos['login'],
                'escola' => $campos['escola'],
                'professor' => $campos['professor'],
                'curso' => $campos['curso'],
                'ano' => $campos['ano'] ? (int)$campos['ano'] : null,
                'periodo_realizacao' => $campos['periodo_realizacao'],
                'componente_curricular' => $campos['componente_curricular'],
                'numero_aulas_semanais' => (int)$campos['numero_aulas_semanais'],
                'objetivo_geral' => $campos['objetivo_geral'],
                'objetivo_especifico' => $campos['objetivo_especifico'],
                'unidade_tematica' => $campos['unidade_tematica'],
                'objeto_do_conhecimento' => $campos['objeto_do_conhecimento'],
                'conteudos' => $campos['conteudos'],
                'habilidades' => $campos['habilidades'],
                'metodologias' => $campos['metodologias']
            ]);
            
            // Salva cookies dos campos especificados (válidos por 30 dias)
            $cookie_expiry = time() + (30 * 24 * 60 * 60); // 30 dias
            setcookie('planejamento_curso', $campos['curso'], $cookie_expiry, '/');
            setcookie('planejamento_ano', $campos['ano'], $cookie_expiry, '/');
            setcookie('planejamento_componente_curricular', $campos['componente_curricular'], $cookie_expiry, '/');
            setcookie('planejamento_numero_aulas_semanais', $campos['numero_aulas_semanais'], $cookie_expiry, '/');
            setcookie('planejamento_objetivo_geral', $campos['objetivo_geral'], $cookie_expiry, '/');
            setcookie('planejamento_objetivo_especifico', $campos['objetivo_especifico'], $cookie_expiry, '/');
            setcookie('planejamento_periodo_realizacao', $campos['periodo_realizacao'], $cookie_expiry, '/');
            setcookie('planejamento_objeto_do_conhecimento', $campos['objeto_do_conhecimento'], $cookie_expiry, '/');
            
            echo "<script>alert('Planejador Cadastrado com sucesso!')</script>";
            echo "<script>location.assign('planejador-mensal-cadastrar')</script>";

            // Limpa os campos após salvar com sucesso (opcional)
            $campos = [
                'curso' => $_COOKIE['planejamento_curso'] ?? '',
                'componente_curricular' => $_COOKIE['planejamento_componente_curricular'] ?? '',
                'objetivo_geral' => $_COOKIE['planejamento_objetivo_geral'] ?? '',
                'objetivo_especifico' => $_COOKIE['planejamento_objetivo_especifico'] ?? '',
                'escola' => $_SESSION['escola'] ?? '',
                'professor' => $_SESSION['nome'] ?? '',
                'login' => $_SESSION['login'] ?? '',
                'ano' => $_COOKIE['planejamento_ano'] ?? '',
                'numero_aulas_semanais' => $_COOKIE['planejamento_numero_aulas_semanais'] ?? '',
                'periodo_realizacao' => $_COOKIE['planejamento_periodo_realizacao'] ?? '',
                'unidade_tematica' => '',
                'objeto_do_conhecimento' => $_COOKIE['planejamento_objeto_do_conhecimento'] ?? '',
                'conteudos' => '',
                'habilidades' => '',
                'metodologias' => ''
            ];
            
        } catch (PDOException $e) {
            $erros[] = "Erro ao cadastrar planejamento: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Planejamento - Universo do Saber</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-blue: #0057b7;
            --primary-orange: #ffa500;
            --dark-blue: #003d7a;
            --dark-orange: #cc8400;
            --light-gray: #f5f7fa;
            --medium-gray: #e1e5eb;
            --dark-gray: #6c757d;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--light-gray);
            min-height: 100vh;
        }
        
        .main-content {
            transition: all 0.3s;
        }
        
        .content {
            padding: 30px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        
        .titulo-principal {
            font-size: 2rem;
            color: var(--primary-blue);
            font-weight: 600;
            margin-bottom: 10px;
            text-align: center;
            width: 100%;
        }
        
        .page-title h1 {
            font-size: 1.8rem;
            color: var(--dark-blue);
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .page-title p {
            color: var(--dark-gray);
            font-size: 0.9rem;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--dark-blue);
        }
        
        .btn-secondary {
            background-color: var(--medium-gray);
            color: var(--dark-gray);
        }
        
        .btn-secondary:hover {
            background-color: #d1d7e0;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark-blue);
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--medium-gray);
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 2px rgba(0, 87, 183, 0.1);
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .error-list {
            margin-top: 5px;
            padding-left: 20px;
            color: #721c24;
        }
        
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .required {
            color: #dc3545;
        }
        
        @media (max-width: 768px) {
            .content {
                padding: 15px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .btn-group {
                width: 100%;
                flex-direction: column;
            }
            
            .btn {
                justify-content: center;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="content">
            <div class="page-header">
                <div class="titulo-principal">PLANEJADOR MENSAL</div>
                <div class="page-title">
                    <h1>Cadastrar Novo Planejamento</h1>
                    <p>Preencha todos os campos obrigatórios</p>
                </div>
                <a href="planejador-mensal" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
            
            <?php if (!empty($erros)): ?>
                <div class="alert alert-danger">
                    <strong>Por favor, corrija os seguintes erros:</strong>
                    <ul class="error-list">
                        <?php foreach ($erros as $erro): ?>
                            <li><?= htmlspecialchars($erro) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="professor">Professor <span class="required">*</span></label>
                        <input type="text" id="professor" name="professor" value="<?= htmlspecialchars($campos['professor']) ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="login">Login</label>
                        <input type="text" id="login" name="login" value="<?= htmlspecialchars($campos['login']) ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="escola">Escola <span class="required">*</span></label>
                        <input type="text" id="escola" name="escola" value="<?= htmlspecialchars($campos['escola']) ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="curso">Curso <span class="required">*</span></label>
                        <input type="text" id="curso" name="curso" value="<?= htmlspecialchars($campos['curso']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="componente_curricular">Componente Curricular <span class="required">*</span></label>
                        <input type="text" id="componente_curricular" name="componente_curricular" value="<?= htmlspecialchars($campos['componente_curricular']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="ano">Ano</label>
                        <input type="number" id="ano" name="ano" value="<?= htmlspecialchars($campos['ano']) ?>" min="1900" max="2100" step="1">
                    </div>
                    
                    <div class="form-group">
                        <label for="numero_aulas_semanais">Número de Aulas Semanais <span class="required">*</span></label>
                        <input type="number" id="numero_aulas_semanais" name="numero_aulas_semanais" value="<?= htmlspecialchars($campos['numero_aulas_semanais']) ?>" required min="1" max="40" step="1">
                    </div>
                    
                    <div class="form-group">
                        <label for="periodo_realizacao">Período de Realização</label>
                        <input type="text" id="periodo_realizacao" name="periodo_realizacao" value="<?= htmlspecialchars($campos['periodo_realizacao']) ?>" placeholder="Ex: 01/03/2023 a 30/03/2023">
                    </div>
                    
                    <div class="form-group">
                        <label for="objetivo_geral">Objetivo Geral</label>
                        <textarea id="objetivo_geral" name="objetivo_geral" placeholder="Descreva o objetivo geral do planejamento..."><?= htmlspecialchars($campos['objetivo_geral']) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="objetivo_especifico">Objetivo Específico</label>
                        <textarea id="objetivo_especifico" name="objetivo_especifico" placeholder="Descreva os objetivos específicos..."><?= htmlspecialchars($campos['objetivo_especifico']) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="unidade_tematica">Unidades Temáticas</label>
                        <select name='unidade_tematica' id='unidade_tematica'>
                        <?php
                        require('unidades-tematicas.php');
                        ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="objeto_do_conhecimento">Objeto do Conhecimento</label>
                        <input list="objetos_conhecimento" id="objeto_do_conhecimento" name="objeto_do_conhecimento" 
                               placeholder="Selecione ou digite o objeto do conhecimento..." 
                               value="<?= htmlspecialchars($campos['objeto_do_conhecimento']) ?>">
                        <datalist id="objetos_conhecimento">
                            <?php require('objetos-do-conhecimento.php'); ?>
                        </datalist>
                    </div>
                    
                    <div class="form-group">
                        <label for="conteudos">Conteúdos</label>
                        <textarea id="conteudos" name="conteudos" placeholder="Liste os conteúdos a serem abordados..."><?= htmlspecialchars($campos['conteudos']) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="habilidades">Habilidades</label>
                        <select name="habilidades" id="habilidades">
                            <option value='-'>Escolha a habilidade</option>
                            <?php
                            try {
                                $sql = "SELECT habilidade FROM habilidades";
                                $stmt = $conexao->prepare($sql);
                                $stmt->execute();
                                $habilidades = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                if ($habilidades) {
                                    foreach ($habilidades as $row) {
                                        echo "<option value='" . htmlspecialchars($row['habilidade']) . "'>" . htmlspecialchars($row['habilidade']) . "</option>";
                                    }
                                } else {
                                    echo "<option value=''>Nenhuma habilidade encontrada</option>";
                                }
                            } catch (PDOException $e) {
                                echo "<option value=''>Erro ao buscar habilidades</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="metodologias">Metodologias</label>
                        <textarea id="metodologias" name="metodologias" placeholder="Descreva as metodologias de ensino..."><?= htmlspecialchars($campos['metodologias']) ?></textarea>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" name="salvar" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Planejamento
                        </button>
                        <button type="button" onclick="limparCookies()" class="btn btn-secondary">
                            <i class="fas fa-trash"></i> Limpar Dados Salvos
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    function limparCookies() {
        if (confirm('Tem certeza que deseja limpar todos os dados salvos automaticamente?')) {
            // Remove todos os cookies do planejamento
            document.cookie = "planejamento_curso=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "planejamento_ano=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "planejamento_componente_curricular=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "planejamento_numero_aulas_semanais=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "planejamento_objetivo_geral=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "planejamento_objetivo_especifico=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "planejamento_periodo_realizacao=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie = "planejamento_objeto_do_conhecimento=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            
            alert('Dados salvos foram limpos com sucesso!');
            location.reload(); // Recarrega a página para mostrar os campos limpos
        }
    }
    </script>
</body>
</html>