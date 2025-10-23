<?php

namespace Tests\Services;

use App\Services\ExamAutoGrader;
use PDO;
use Tests\TestCase;

class ExamAutoGraderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_ENV['EXAM_OMR_MIN_CONFIDENCE'] = 0.7;
        $_ENV['EXAM_OMR_REVIEW_THRESHOLD'] = 0.85;

        $pdo = $this->pdo();
        $pdo->exec('CREATE TABLE provas_online (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            lista_quest TEXT NULL,
            turma TEXT NULL,
            materia TEXT NULL
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

        $pdo->prepare('INSERT INTO provas_online (lista_quest, turma, materia) VALUES (?, ?, ?)')
            ->execute(['101,102,103', '5A', 'Matemática']);

        $insertQuestion = $pdo->prepare('INSERT INTO questoes (id, resposta, alternativa_A, alternativa_B, alternativa_C, alternativa_D, alternativa_E) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $insertQuestion->execute([101, 'A', '1', '2', '3', '4', '5']);
        $insertQuestion->execute([102, 'B', '1', '2', '3', '4', '5']);
        $insertQuestion->execute([103, 'C', '1', '2', '3', '4', '5']);
    }

    public function testGradeNormalizesAndTranslatesProviderResponse(): void
    {
        $response = [
            'scan_id' => 'scan-123',
            'status' => 'completed',
            'confidence' => 0.92,
            'answers' => [
                ['question_id' => 101, 'choice' => 'a', 'confidence' => 0.95, 'status' => 'detected'],
                ['question_id' => 102, 'choice' => 'C', 'confidence' => 0.90, 'status' => 'detected'],
                ['question_id' => 103, 'choice' => 'c', 'confidence' => 0.60, 'status' => 'detected'],
            ],
        ];

        $service = new ExamAutoGrader(null, fn () => $response);

        $result = $service->grade([
            'provas_online_id' => 1,
            'student_matricula' => 'A001',
            'attempt' => 1,
        ]);

        $this->assertSame('scan-123', $result['provider_scan_id']);
        $this->assertSame(0.92, $result['overall_confidence']);
        $this->assertNotEmpty($result['items']);

        $legacy = $result['legacy'];
        $this->assertTrue($legacy['requires_review']);

        $summary = $legacy['summary'];
        $this->assertSame(3, $summary['total_questions']);
        $this->assertSame(1, $summary['correct']);
        $this->assertEquals(3.33, $summary['grade']);

        $answers = $legacy['answers'];
        $this->assertCount(3, $answers);
        $this->assertTrue($answers[0]['correct']);
        $this->assertFalse($answers[1]['correct']);
        $this->assertNull($answers[2]['answer']);
        $this->assertSame('low_confidence', $answers[2]['status']);
    }
}
