<?php

// Verifica se existem IDs na URL
if (isset($_GET['ids'])) {
    // Converte a string de IDs separados por vírgula em um array
    $ids = explode(',', $_GET['ids']);
    
    // Sanitiza os IDs (importante para segurança)
    $ids = array_map('intval', $ids);
    $ids = array_filter($ids); // Remove valores vazios
    
    // Agora você pode usar $ids em sua consulta SQL
    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT * FROM provas WHERE id IN ($placeholders)";
        // Execute a consulta...
    }
}

?>