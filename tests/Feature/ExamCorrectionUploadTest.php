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

    public function testConsecutiveUploadsProduceDistinctPaths(): void
    {
        $controller = new ExamCorrectionController();

        $storagePathMethod = new \ReflectionMethod($controller, 'storagePath');
        $storagePathMethod->setAccessible(true);
        $directory = $storagePathMethod->invoke($controller, 'exams');

        $directoryExisted = is_dir($directory);
        $existingFiles = $directoryExisted ? glob($directory . DIRECTORY_SEPARATOR . '*') : [];

        $storeMethod = new \ReflectionMethod($controller, 'storeUploadedFile');
        $storeMethod->setAccessible(true);

        $tmpOne = tempnam(sys_get_temp_dir(), 'upload');
        $tmpTwo = tempnam(sys_get_temp_dir(), 'upload');
        $this->assertIsString($tmpOne);
        $this->assertIsString($tmpTwo);

        file_put_contents($tmpOne, 'first');
        file_put_contents($tmpTwo, 'second');

        $firstPath = $storeMethod->invoke($controller, [
            'tmp_name' => $tmpOne,
            'name' => 'upload.jpg',
        ], 42, 'STU-1', 1);

        $secondPath = $storeMethod->invoke($controller, [
            'tmp_name' => $tmpTwo,
            'name' => 'upload.jpg',
        ], 42, 'STU-1', 1);

        $this->assertIsString($firstPath);
        $this->assertIsString($secondPath);
        $this->assertNotSame($firstPath, $secondPath);

        foreach ([$firstPath, $secondPath] as $path) {
            if (is_string($path) && file_exists($path)) {
                unlink($path);
            }
        }

        if (!$directoryExisted && is_dir($directory)) {
            $remaining = glob($directory . DIRECTORY_SEPARATOR . '*');
            if ($remaining === [] || $remaining === false || $remaining === $existingFiles) {
                @rmdir($directory);
            }
        }

        if (is_string($tmpOne) && file_exists($tmpOne)) {
            unlink($tmpOne);
        }

        if (is_string($tmpTwo) && file_exists($tmpTwo)) {
            unlink($tmpTwo);
        }
    }
}
