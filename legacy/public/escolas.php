<?php
// Você pode buscar essas escolas do banco de dados se preferir
$escolas = [
    "Escola Estadual Professor João Silva",
    "Colégio Dom Pedro II",
    "Instituto Federal de Educação",
    "Escola Municipal Ana Maria",
    "Colégio Santa Maria",
    "Escola Técnica Federal",
    "Colégio Objetivo",
    "Escola Nova Era",
    "Colégio Progresso",
    "Escola Modelo"
];

foreach ($escolas as $escola) {
    echo "<option value=\"" . htmlspecialchars($escola) . "\">";
}
?>