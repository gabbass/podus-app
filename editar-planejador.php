<?php
require('conexao.php');
require('sessao-professor.php');

// Verifica se foi passado um ID válido para edição
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: planejador.php');
    exit();
}

$id = (int)$_GET['id'];
$erros = [];

// Busca os dados do planejamento no banco de dados
try {
    $sql = "SELECT * FROM planejador WHERE id = :id AND login = :login";
    $stmt = $conexao->prepare($sql);
    $stmt->execute(['id' => $id, 'login' => $_SESSION['login']]);
    $planejamento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$planejamento) {
        header('Location: planejador.php');
        exit();
    }
    
    // Inicializa variáveis com os dados do banco
    $campos = [
        'id' => $planejamento['id'],
        'curso' => $planejamento['curso'],
        'componente_curricular' => $planejamento['componente_curricular'],
        'objetivo_geral' => $planejamento['objetivo_geral'],
        'objetivo_especifico' => $planejamento['objetivo_especifico'],
        'escola' => $planejamento['escola'],
        'professor' => $planejamento['professor'],
        'login' => $planejamento['login'],
        'ano' => $planejamento['ano'],
        'numero_aulas_semanais' => $planejamento['numero_aulas_semanais'],
        'tipo' => $planejamento['tipo'],
        'sequencial' => $planejamento['sequencial'],
        'diagnostico' => $planejamento['diagnostico'],
        'referencias' => $planejamento['referencias'],
        'unidade_tematica' => $planejamento['unidade_tematica'],
        'objeto_do_conhecimento' => $planejamento['objeto_do_conhecimento'],
        'conteudos' => $planejamento['conteudos'],
        'habilidades' => $planejamento['habilidades'],
        'metodologias' => $planejamento['metodologias'],
        'projetos_integrador' => $planejamento['projetos_integrador'],
        'data' => $planejamento['data']
    ];
    
} catch (PDOException $e) {
    $erros[] = "Erro ao carregar planejamento: " . $e->getMessage();
}

// Processa o formulário quando é submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitiza e valida os dados para atualização
    $campos = [
        'id' => $id,
        'curso' => trim($_POST['curso'] ?? ''),
        'componente_curricular' => trim($_POST['componente_curricular'] ?? ''),
        'objetivo_geral' => trim($_POST['objetivo_geral'] ?? ''),
        'objetivo_especifico' => trim($_POST['objetivo_especifico'] ?? ''),
        'escola' => trim($_POST['escola'] ?? ''),
        'professor' => trim($_POST['professor'] ?? ''),
        'login' => $_SESSION['login'],
        'ano' => trim($_POST['ano'] ?? ''),
        'numero_aulas_semanais' => trim($_POST['numero_aulas_semanais'] ?? ''),
        'tipo' => trim($_POST['tipo'] ?? 'Bimestral'),
        'sequencial' => trim($_POST['sequencial'] ?? '1º'),
        'diagnostico' => trim($_POST['diagnostico'] ?? ''),
        'referencias' => trim($_POST['referencias'] ?? ''),
        'unidade_tematica' => trim($_POST['unidade_tematica'] ?? ''),
        'objeto_do_conhecimento' => trim($_POST['objeto_do_conhecimento'] ?? ''),
        'conteudos' => trim($_POST['conteudos'] ?? ''),
        'habilidades' => trim($_POST['habilidades'] ?? ''),
        'metodologias' => trim($_POST['metodologias'] ?? ''),
        'projetos_integrador' => trim($_POST['projetos_integrador'] ?? ''),
        'data' => strtotime($_POST['data'] ?? date('Y-m-d'))
    ];

    // Validação básica (igual ao cadastro)
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
    
    if (!empty($campos['ano']) && (!is_numeric($campos['ano']) || $campos['ano'] < 1900 || $campos['ano'] > 2100)) {
        $erros[] = "O campo Ano deve ser um ano válido.";
    }

    // Se não houver erros, atualiza no banco de dados
    if (empty($erros)) {
        try {
            $sql = "UPDATE planejador SET
                curso = :curso, 
                componente_curricular = :componente_curricular, 
                objetivo_geral = :objetivo_geral, 
                objetivo_especifico = :objetivo_especifico, 
                escola = :escola,
                professor = :professor, 
                login = :login, 
                ano = :ano, 
                numero_aulas_semanais = :numero_aulas_semanais, 
                tipo = :tipo,
                sequencial = :sequencial, 
                unidade_tematica = :unidade_tematica, 
                objeto_do_conhecimento = :objeto_do_conhecimento, 
                conteudos = :conteudos, 
                habilidades = :habilidades,
                metodologias = :metodologias, 
                projetos_integrador = :projetos_integrador, 
                diagnostico = :diagnostico, 
                referencias = :referencias,
                data = :data
            WHERE id = :id AND login = :login";
            
            $stmt = $conexao->prepare($sql);
            $stmt->execute([
                'id' => $campos['id'],
                'curso' => $campos['curso'],
                'componente_curricular' => $campos['componente_curricular'],
                'objetivo_geral' => $campos['objetivo_geral'],
                'objetivo_especifico' => $campos['objetivo_especifico'],
                'escola' => $campos['escola'],
                'professor' => $campos['professor'],
                'login' => $campos['login'],
                'ano' => $campos['ano'] ? (int)$campos['ano'] : null,
                'numero_aulas_semanais' => (int)$campos['numero_aulas_semanais'],
                'tipo' => $campos['tipo'],
                'sequencial' => $campos['sequencial'],
                'unidade_tematica' => $campos['unidade_tematica'],
                'objeto_do_conhecimento' => $campos['objeto_do_conhecimento'],
                'conteudos' => $campos['conteudos'],
                'habilidades' => $campos['habilidades'],
                'metodologias' => $campos['metodologias'],
                'projetos_integrador' => $campos['projetos_integrador'],
                'diagnostico' => $campos['diagnostico'],
                'referencias' => $campos['referencias'],
                'data' => $campos['data']
            ]);
            
            // Mensagem de sucesso
            $sucesso = "Planejamento atualizado com sucesso!";
            
        } catch (PDOException $e) {
            $erros[] = "Erro ao atualizar planejamento: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Planejamento - Universo do Saber</title>
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
                <div class="titulo-principal">PLANEJAMENTO ANUAL</div>
                <div class="page-title">
                    <h1>Editar Planejamento</h1>
                    <p>Atualize os campos necessários</p>
                </div>
                <a href="planejador.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
            
            <?php if (isset($sucesso)): ?>
                <div class="alert alert-success">
                    <?= $sucesso ?>
                </div>
            <?php endif; ?>
            
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
                    <input type="hidden" name="id" value="<?= $campos['id'] ?>">
                    
                    <div class="form-group">
                        <label for="data">Data</label>
                        <input type="date" id="data" name="data" value="<?= htmlspecialchars(date('Y-m-d', $campos['data'])) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="tipo">Tipo <span class="required">*</span></label>
                        <select id="tipo" name="tipo" required>
                            <option value='-'>Escolha o tipo</option>
                            <option value="Bimestral" <?= $campos['tipo'] === 'Bimestral' ? 'selected' : '' ?>>Bimestral</option>
                            <option value="Trimestral" <?= $campos['tipo'] === 'Trimestral' ? 'selected' : '' ?>>Trimestral</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="sequencial">Sequencial <span class="required">*</span></label>
                        <select id="sequencial" name="sequencial" required>
                            <option value='-'>Escolha a Sequência</option>
                            <?php if ($campos['tipo'] === 'Bimestral'): ?>
                                <option value="1º" <?= $campos['sequencial'] === '1º' ? 'selected' : '' ?>>1º Bimestre</option>
                                <option value="2º" <?= $campos['sequencial'] === '2º' ? 'selected' : '' ?>>2º Bimestre</option>
                                <option value="3º" <?= $campos['sequencial'] === '3º' ? 'selected' : '' ?>>3º Bimestre</option>
                                <option value="4º" <?= $campos['sequencial'] === '4º' ? 'selected' : '' ?>>4º Bimestre</option>
                            <?php else: ?>
                                <option value="1º" <?= $campos['sequencial'] === '1º' ? 'selected' : '' ?>>1º Trimestre</option>
                                <option value="2º" <?= $campos['sequencial'] === '2º' ? 'selected' : '' ?>>2º Trimestre</option>
                                <option value="3º" <?= $campos['sequencial'] === '3º' ? 'selected' : '' ?>>3º Trimestre</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="professor">Professor <span class="required">*</span></label>
                        <input type="text" id="professor" name="professor" value="<?= htmlspecialchars($campos['professor']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="login">Login</label>
                        <input type="text" id="login" name="login" value="<?= htmlspecialchars($campos['login']) ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="escola">Escola <span class="required">*</span></label>
                        <input type="text" id="escola" name="escola" value="<?= htmlspecialchars($campos['escola']) ?>" required>
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
                        <label for="objetivo_geral">Objetivo Geral</label>
                        <textarea id="objetivo_geral" name="objetivo_geral" placeholder="Descreva o objetivo geral do planejamento..."><?= htmlspecialchars($campos['objetivo_geral']) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="objetivo_especifico">Objetivo Específico</label>
                        <textarea id="objetivo_especifico" name="objetivo_especifico" placeholder="Descreva os objetivos específicos..."><?= htmlspecialchars($campos['objetivo_especifico']) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="projetos_integrador">Projetos Integrador</label>
                        <textarea id="projetos_integrador" name="projetos_integrador" placeholder="Descreva os projetos integradores..."><?= htmlspecialchars($campos['projetos_integrador']) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="unidade_tematica">Unidades Temáticas</label>
                        <select name="unidade_tematica" id="unidade_tematica">
                            <?php
                            require('unidades-tematicas.php');
                            // Supondo que unidades-tematicas.php contém options como:
                            // <option value="valor1">Texto1</option>
                            // <option value="valor2">Texto2</option>
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
                            <?php
                            require('habilidades.php');
                            // Supondo que habilidades.php contém options como:
                            // <option value="valor1">Texto1</option>
                            // <option value="valor2">Texto2</option>
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="metodologias">Metodologias</label>
                        <textarea id="metodologias" name="metodologias" placeholder="Descreva as metodologias de ensino..."><?= htmlspecialchars($campos['metodologias']) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="diagnostico">DIAGNÓSTICO/PERFIL DE TURMA(S)</label>
                        <textarea id="diagnostico" name="diagnostico" placeholder="Descreva o diagnóstico e perfil das turmas..."><?= htmlspecialchars($campos['diagnostico']) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="referencias">REFERÊNCIAS</label>
                        <textarea id="referencias" name="referencias" placeholder="Liste as referências bibliográficas..."><?= htmlspecialchars($campos['referencias']) ?></textarea>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" name="salvar" class="btn btn-primary">
                            <i class="fas fa-save"></i> Atualizar Planejamento
                        </button>
                        <a href="planejador.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    // Adicione esta função para atualizar o sequencial quando o tipo mudar
    document.getElementById('tipo').addEventListener('change', function() {
        const tipo = this.value;
        const sequencialSelect = document.getElementById('sequencial');
        
        if (tipo === 'Bimestral') {
            sequencialSelect.innerHTML = `
                <option value="1º">1º Bimestre</option>
                <option value="2º">2º Bimestre</option>
                <option value="3º">3º Bimestre</option>
                <option value="4º">4º Bimestre</option>
            `;
        } else {
            sequencialSelect.innerHTML = `
                <option value="1º">1º Trimestre</option>
                <option value="2º">2º Trimestre</option>
                <option value="3º">3º Trimestre</option>
            `;
        }
        
        // Mantém o valor selecionado se ainda existir nas novas opções
        const currentValue = '<?= $campos['sequencial'] ?>';
        if (currentValue) {
            const option = sequencialSelect.querySelector(`option[value="${currentValue}"]`);
            if (option) {
                option.selected = true;
            }
        }
    });

    // Seleciona o valor correto nos selects de unidades temáticas e habilidades
    document.addEventListener('DOMContentLoaded', function() {
        const unidadeTematicaSelect = document.getElementById('unidade_tematica');
        if (unidadeTematicaSelect) {
            const valorAtual = '<?= $campos['unidade_tematica'] ?>';
            if (valorAtual) {
                for (let i = 0; i < unidadeTematicaSelect.options.length; i++) {
                    if (unidadeTematicaSelect.options[i].value === valorAtual) {
                        unidadeTematicaSelect.options[i].selected = true;
                        break;
                    }
                }
            }
        }

        const habilidadesSelect = document.getElementById('habilidades');
        if (habilidadesSelect) {
            const valorAtual = '<?= $campos['habilidades'] ?>';
            if (valorAtual) {
                for (let i = 0; i < habilidadesSelect.options.length; i++) {
                    if (habilidadesSelect.options[i].value === valorAtual) {
                        habilidadesSelect.options[i].selected = true;
                        break;
                    }
                }
            }
        }
    });
    </script>
</body>
</html>