<?php

namespace Tests\Feature;

use App\Http\Controllers\ExamCorrectionController;
use PDO;
use Tests\TestCase;

class ExamCorrectionUploadTest extends TestCase
{
    public function testRapidConsecutiveReservationsYieldDistinctAttempts(): void
    {
        $pdo = $this->pdo();

        $pdo->exec('CREATE TABLE provas_online (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            turma TEXT NULL,
            materia TEXT NULL
        )');

        $pdo->exec('CREATE TABLE provas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            matricula TEXT NOT NULL,
            turma TEXT NULL,
            materia TEXT NULL,
            tentativa_feita INTEGER NOT NULL DEFAULT 0,
            data DATETIME NULL
        )');

        $pdo->prepare('INSERT INTO provas_online (turma, materia) VALUES (?, ?)')
            ->execute(['6B', 'Ciências']);

        $provaOnline = $pdo->query('SELECT * FROM provas_online WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
        $this->assertIsArray($provaOnline);

        $controller = new ExamCorrectionController();
        $reflection = new \ReflectionMethod($controller, 'resolveProvaAluno');
        $reflection->setAccessible(true);

        $first = $reflection->invoke($controller, $pdo, $provaOnline, 'STU1', true, true);
        $second = $reflection->invoke($controller, $pdo, $provaOnline, 'STU1', true, true);

        $this->assertSame(1, $first['reserved_attempt']);
        $this->assertSame(2, $second['reserved_attempt']);
        $this->assertSame(1, $first['tentativa_feita']);
        $this->assertSame(2, $second['tentativa_feita']);

        $prova = $pdo->query('SELECT tentativa_feita FROM provas WHERE matricula = "STU1"')->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($prova);
        $this->assertSame(2, (int) $prova['tentativa_feita']);
    }
}
