<?php

namespace Tests\Feature;

use App\Jobs\ProcessExamScan;
use App\Models\ExamScan;
use App\Services\ExamAutoGrader;
use PDO;
use Tests\TestCase;

class ProcessExamScanTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_ENV['EXAM_OMR_MIN_CONFIDENCE'] = 0.5;
        $_ENV['EXAM_OMR_REVIEW_THRESHOLD'] = 0.8;

        $pdo = $this->pdo();

        $pdo->exec('CREATE TABLE provas_online (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            lista_quest TEXT NULL,
            turma TEXT NULL,
            materia TEXT NULL,
            moodle_grade_item_id INTEGER NULL
        )');

        $pdo->exec('CREATE TABLE questoes (
            id INTEGER PRIMARY KEY,
            resposta TEXT NULL,
            alternativa_A TEXT NULL,
            alternativa_B TEXT NULL,
            alternativa_C TEXT NULL,
            alternativa_D TEXT NULL,
            alternativa_E TEXT NULL
        )');

        $pdo->exec('CREATE TABLE provas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            matricula TEXT NOT NULL,
            turma TEXT NULL,
            materia TEXT NULL,
            tentativa_feita INTEGER NOT NULL DEFAULT 0,
            nota_tenta1 REAL NULL
        )');

        $pdo->exec('CREATE TABLE respostas_alunos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            id_provas_online INTEGER NOT NULL,
            id_provas INTEGER NOT NULL,
            id_questao INTEGER NOT NULL,
            id_matricula TEXT NOT NULL,
            resposta_tenta1 TEXT NULL,
            gabarito_tenta1 TEXT NULL
        )');

        $pdo->exec('CREATE TABLE exam_scans (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            provas_online_id INTEGER NOT NULL,
            provas_id INTEGER NULL,
            student_matricula TEXT NOT NULL,
            attempt INTEGER NOT NULL,
            status TEXT NOT NULL,
            provider TEXT NOT NULL,
            provider_scan_id TEXT NULL,
            file_path TEXT NOT NULL,
            overall_confidence REAL NULL,
            requires_review INTEGER NOT NULL DEFAULT 0,
            error_message TEXT NULL,
            metadata TEXT NULL,
            score REAL NULL,
            total_questions INTEGER NOT NULL DEFAULT 0,
            correct_answers INTEGER NOT NULL DEFAULT 0,
            processed_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )');

        $pdo->exec('CREATE TABLE exam_scan_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            exam_scan_id INTEGER NOT NULL,
            question_id INTEGER NOT NULL,
            detected_alternative TEXT NULL,
            confidence REAL NULL,
            status TEXT NOT NULL,
            raw_payload TEXT NULL
        )');

        $pdo->exec('CREATE TABLE moodle_sync_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            entity_type TEXT,
            entity_id TEXT,
            action TEXT,
            status TEXT,
            payload TEXT,
            response TEXT,
            error_message TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');

        $pdo->exec('CREATE TABLE login (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            matricula TEXT NOT NULL,
            moodle_user_id INTEGER NULL
        )');

        $pdo->prepare('INSERT INTO provas_online (lista_quest, turma, materia, moodle_grade_item_id) VALUES (?, ?, ?, ?)')
            ->execute(['201,202', '6B', 'Ciências', null]);

        $insertQuestion = $pdo->prepare('INSERT INTO questoes (id, resposta, alternativa_A, alternativa_B, alternativa_C, alternativa_D, alternativa_E) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $insertQuestion->execute([201, 'A', 'op1', 'op2', 'op3', 'op4', 'op5']);
        $insertQuestion->execute([202, 'C', 'op1', 'op2', 'op3', 'op4', 'op5']);

        $pdo->prepare('INSERT INTO provas (matricula, turma, materia, tentativa_feita, nota_tenta1) VALUES (?, ?, ?, ?, ?)')
            ->execute(['STU1', '6B', 'Ciências', 0, null]);

        $pdo->prepare('INSERT INTO login (matricula, moodle_user_id) VALUES (?, ?)')
            ->execute(['STU1', null]);
    }

    public function testProcessExamScanPersistsResultsAndLogsSync(): void
    {
        $pdo = $this->pdo();

        $scan = ExamScan::create([
            'provas_online_id' => 1,
            'provas_id' => 1,
            'student_matricula' => 'STU1',
            'attempt' => 1,
            'status' => 'pending',
            'provider' => 'saas',
            'file_path' => '/tmp/fake-scan.jpg',
        ]);

        $response = [
            'scan_id' => 'scan-555',
            'status' => 'completed',
            'confidence' => 0.93,
            'answers' => [
                ['question_id' => 201, 'choice' => 'A', 'confidence' => 0.96, 'status' => 'detected'],
                ['question_id' => 202, 'choice' => 'B', 'confidence' => 0.90, 'status' => 'detected'],
            ],
        ];

        $grader = new ExamAutoGrader($pdo, fn () => $response);
        $job = new ProcessExamScan((int) $scan['id'], $grader, $pdo);
        $job->handle();

        $updatedScan = $pdo->query('SELECT * FROM exam_scans WHERE id = ' . (int) $scan['id'])->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($updatedScan);
        $this->assertSame('completed', $updatedScan['status']);
        $this->assertEquals(0.93, (float) $updatedScan['overall_confidence']);
        $this->assertEquals(5.0, (float) $updatedScan['score']);
        $this->assertSame(0, (int) $updatedScan['requires_review']);

        $items = $pdo->query('SELECT * FROM exam_scan_items WHERE exam_scan_id = ' . (int) $scan['id'])->fetchAll(PDO::FETCH_ASSOC);
        $this->assertCount(2, $items);

        $answers = $pdo->query('SELECT * FROM respostas_alunos ORDER BY id_questao')->fetchAll(PDO::FETCH_ASSOC);
        $this->assertCount(2, $answers);
        $this->assertSame('A', $answers[0]['resposta_tenta1']);
        $this->assertSame('A', $answers[0]['gabarito_tenta1']);
        $this->assertSame('B', $answers[1]['resposta_tenta1']);
        $this->assertSame('C', $answers[1]['gabarito_tenta1']);

        $prova = $pdo->query('SELECT tentativa_feita, nota_tenta1 FROM provas WHERE id = 1')->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($prova);
        $this->assertSame(1, (int) $prova['tentativa_feita']);
        $this->assertEquals(5.0, (float) $prova['nota_tenta1']);

        $log = $pdo->query('SELECT * FROM moodle_sync_logs')->fetch(PDO::FETCH_ASSOC);
        $this->assertNotFalse($log);
        $this->assertSame('skipped', $log['status']);
    }
}
