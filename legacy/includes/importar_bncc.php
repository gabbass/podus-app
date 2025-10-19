<?php
require 'conexoes-bncc.php';

$endpoints = [
    'Medio' => 'https://cientificar1992.pythonanywhere.com/bncc_medio/',
    'Fundamental' => 'https://cientificar1992.pythonanywhere.com/bncc_fundamental/'
];

// Mapeamento de componentes para áreas (adapte e expanda conforme necessidade)
$componente_area = [
    // Exemplos (adicione todos que identificar nos dados)
    'Língua Portuguesa' => 'Linguagens',
    'Matemática' => 'Matemática',
    'Química' => 'Ciências da Natureza',
    'Física' => 'Ciências da Natureza',
    'Biologia' => 'Ciências da Natureza',
    'Geografia' => 'Ciências Humanas',
    'História' => 'Ciências Humanas',
    'Filosofia' => 'Ciências Humanas',
    'Sociologia' => 'Ciências Humanas',
    // Fallback
    '' => 'Outros',
];

$total_inseridas = 0;
$total_existentes = 0;

foreach ($endpoints as $etapa_nome => $url) {
    $json = @file_get_contents($url);
    if ($json === false) {
        echo "Erro ao acessar endpoint: $url<br>";
        continue;
    }
    $data = json_decode($json, true);
    if (!$data) {
        echo "JSON inválido em: $url<br>";
        continue;
    }

    // Etapa de ensino (ex: "Medio" ou "Fundamental")
    $stmt = $pdo_bncc->prepare("SELECT id FROM bncc_etapas WHERE nome = ?");
    $stmt->execute([$etapa_nome]);
    $id_etapa = $stmt->fetchColumn();
    if (!$id_etapa) {
        $stmt = $pdo_bncc->prepare("INSERT INTO bncc_etapas (nome) VALUES (?)");
        $stmt->execute([$etapa_nome]);
        $id_etapa = $pdo_bncc->lastInsertId();
    }

    // Ano genérico (adapte se conseguir extrair o ano da habilidade/código)
    $ano_nome = "Geral";
    $stmt = $pdo_bncc->prepare("SELECT id FROM bncc_anos WHERE ano = ? AND id_etapa = ?");
    $stmt->execute([$ano_nome, $id_etapa]);
    $id_ano = $stmt->fetchColumn();
    if (!$id_ano) {
        $stmt = $pdo_bncc->prepare("INSERT INTO bncc_anos (id_etapa, ano) VALUES (?, ?)");
        $stmt->execute([$id_etapa, $ano_nome]);
        $id_ano = $pdo_bncc->lastInsertId();
    }

    foreach ($data as $row) {
        // Descobrir a área do componente
        $componente = trim($row['componente'] ?? 'Componente Indefinido');
        $area_nome = $componente_area[$componente] ?? 'Outros';

        // Insira ou busque área
        $stmt = $pdo_bncc->prepare("SELECT id FROM bncc_areas WHERE nome = ?");
        $stmt->execute([$area_nome]);
        $id_area = $stmt->fetchColumn();
        if (!$id_area) {
            $stmt = $pdo_bncc->prepare("INSERT INTO bncc_areas (nome) VALUES (?)");
            $stmt->execute([$area_nome]);
            $id_area = $pdo_bncc->lastInsertId();
        }

        // Insira ou busque componente, agora com área correta
        $stmt = $pdo_bncc->prepare("SELECT id FROM bncc_componentes WHERE nome = ? AND id_area = ?");
        $stmt->execute([$componente, $id_area]);
        $id_componente = $stmt->fetchColumn();
        if (!$id_componente) {
            $stmt = $pdo_bncc->prepare("INSERT INTO bncc_componentes (id_area, nome) VALUES (?, ?)");
            $stmt->execute([$id_area, $componente]);
            $id_componente = $pdo_bncc->lastInsertId();
        }

        // Unidade temática
        $unidade = trim($row['unidade_tematica'] ?? 'Unidade Indefinida');
        $stmt = $pdo_bncc->prepare("SELECT id FROM bncc_unidades_tematicas WHERE nome = ? AND id_componente = ?");
        $stmt->execute([$unidade, $id_componente]);
        $id_unidade = $stmt->fetchColumn();
        if (!$id_unidade) {
            $stmt = $pdo_bncc->prepare("INSERT INTO bncc_unidades_tematicas (id_componente, nome) VALUES (?, ?)");
            $stmt->execute([$id_componente, $unidade]);
            $id_unidade = $pdo_bncc->lastInsertId();
        }

        // Objeto do conhecimento
        $objeto = trim($row['objeto_do_conhecimento'] ?? 'Objeto Indefinido');
        $stmt = $pdo_bncc->prepare("SELECT id FROM bncc_objetos_conhecimento WHERE nome = ? AND id_unidade_tematica = ?");
        $stmt->execute([$objeto, $id_unidade]);
        $id_objeto = $stmt->fetchColumn();
        if (!$id_objeto) {
            $stmt = $pdo_bncc->prepare("INSERT INTO bncc_objetos_conhecimento (id_unidade_tematica, nome) VALUES (?, ?)");
            $stmt->execute([$id_unidade, $objeto]);
            $id_objeto = $pdo_bncc->lastInsertId();
        }

        // Habilidade
        $codigo = trim($row['codigo'] ?? '');
        $descricao = trim($row['descricao'] ?? '');
        if (!$codigo || !$descricao) continue;

        // Verificar duplicidade de habilidade por código
        $stmt = $pdo_bncc->prepare("SELECT id FROM bncc_habilidades WHERE codigo = ?");
        $stmt->execute([$codigo]);
        if (!$stmt->fetchColumn()) {
            $stmt = $pdo_bncc->prepare("INSERT INTO bncc_habilidades (id_objeto, codigo, descricao) VALUES (?, ?, ?)");
            $stmt->execute([$id_objeto, $codigo, $descricao]);
            $total_inseridas++;
        } else {
            $total_existentes++;
        }
    }
}

echo "Importação concluída.<br>Habilidades novas: $total_inseridas<br>Já existentes: $total_existentes<br>";
?>
