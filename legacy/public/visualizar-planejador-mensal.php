<?php
require('conexao.php');
require('sessao-professor.php');

// Verifica se foi passado um ID válido para visualização
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: planejador.php');
    exit();
}

$id = (int)$_GET['id'];
$erros = [];

// Busca os dados do planejamento no banco de dados
try {
    $sql = "SELECT * FROM planejadormensal WHERE id = :id AND login = :login";
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
        'periodo_realizacao' => $planejamento['periodo_realizacao'],
        'unidade_tematica' => $planejamento['unidade_tematica'],
        'objeto_do_conhecimento' => $planejamento['objeto_do_conhecimento'],
        'conteudos' => $planejamento['conteudos'],
        'habilidades' => $planejamento['habilidades'],
        'metodologias' => $planejamento['metodologias'],
        'data' => $planejamento['data']
    ];
    
} catch (PDOException $e) {
    $erros[] = "Erro ao carregar planejamento: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Planejamento - Universo do Saber</title>
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
        
        .view-field {
            padding: 12px 15px;
            background-color: var(--light-gray);
            border-radius: 4px;
            border: 1px solid var(--medium-gray);
            margin-bottom: 15px;
            min-height: 44px;
        }
        
        .view-textarea {
            padding: 12px 15px;
            background-color: var(--light-gray);
            border-radius: 4px;
            border: 1px solid var(--medium-gray);
            margin-bottom: 15px;
            min-height: 100px;
            white-space: pre-wrap;
        }
        
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
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
        
        .periodo-info {
            background-color: var(--primary-blue);
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            margin-bottom: 15px;
            display: inline-block;
            font-weight: 500;
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
                    <h1>Visualizar Planejamento</h1>
                    <p>Detalhes do planejamento selecionado</p>
                </div>
                <a href="planejador-mensal.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
            </div>
            
            <?php if (!empty($erros)): ?>
                <div class="alert alert-danger">
                    <strong>Erro ao carregar planejamento:</strong>
                    <ul class="error-list">
                        <?php foreach ($erros as $erro): ?>
                            <li><?= htmlspecialchars($erro) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="form-group">
                    <label>Data</label>
                    <div class="view-field"><?= htmlspecialchars(date('d/m/Y', ($campos['data']))) ?></div>
                </div>
                
                <div class="form-group">
                    <label>Tipo</label>
                    <div class="view-field"><?= htmlspecialchars($campos['tipo']) ?></div>
                </div>
                
                <div class="form-group">
                    <label>Sequencial</label>
                    <div class="view-field"><?= htmlspecialchars($campos['sequencial']) ?></div>
                </div>

                <div class="form-group">
                    <label>Professor</label>
                    <div class="view-field"><?= htmlspecialchars($campos['professor']) ?></div>
                </div>
                
                <div class="form-group">
                    <label>Login</label>
                    <div class="view-field"><?= htmlspecialchars($campos['login']) ?></div>
                </div>
                
                <div class="form-group">
                    <label>Escola</label>
                    <div class="view-field"><?= htmlspecialchars($campos['escola']) ?></div>
                </div>
                
                <div class="form-group">
                    <label>Curso</label>
                    <div class="view-field"><?= htmlspecialchars($campos['curso']) ?></div>
                </div>
                
                <div class="form-group">
                    <label>Componente Curricular</label>
                    <div class="view-field"><?= htmlspecialchars($campos['componente_curricular']) ?></div>
                </div>
                
                <div class="form-group">
                    <label>Ano</label>
                    <div class="view-field"><?= htmlspecialchars($campos['ano']) ?></div>
                </div>
                
                <div class="form-group">
                    <label>Número de Aulas Semanais</label>
                    <div class="view-field"><?= htmlspecialchars($campos['numero_aulas_semanais']) ?></div>
                </div>
                
                <div class="form-group">
                    <label>Período de Realização</label>
                    <div class="view-field"><?= htmlspecialchars($campos['periodo_realizacao']) ?></div>
                </div>
                
                <div class="form-group">
                    <label>Objetivo Geral</label>
                    <div class="view-textarea"><?= nl2br(htmlspecialchars($campos['objetivo_geral'])) ?></div>
                </div>
                
                <div class="form-group">
                    <label>Objetivo Específico</label>
                    <div class="view-textarea"><?= nl2br(htmlspecialchars($campos['objetivo_especifico'])) ?></div>
                </div>
                
                <div class="periodo-info">
                    <?= htmlspecialchars($campos['tipo']) ?> - <?= htmlspecialchars($campos['sequencial']) ?>
                </div>
                
                <div class="form-group">
                    <label>Unidades Temáticas</label>
                    <div class="view-field"><?= htmlspecialchars($campos['unidade_tematica']) ?></div>
                </div>
                
                <div class="form-group">
                    <label>Objeto do Conhecimento</label>
                    <div class="view-field"><?= htmlspecialchars($campos['objeto_do_conhecimento']) ?></div>
                </div>
                
                <div class="form-group">
                    <label>Conteúdos</label>
                    <div class="view-textarea"><?= nl2br(htmlspecialchars($campos['conteudos'])) ?></div>
                </div>
                
                <div class="form-group">
                    <label>Habilidades</label>
                    <div class="view-field"><?= htmlspecialchars($campos['habilidades']) ?></div>
                </div>
                
                <div class="form-group">
                    <label>Metodologias</label>
                    <div class="view-textarea"><?= nl2br(htmlspecialchars($campos['metodologias'])) ?></div>
                </div>
                
                <div class="btn-group">
                    <a onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print"></i> Imprimir
                    </a>
                    <a href="planejador-mensal" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Voltar
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>