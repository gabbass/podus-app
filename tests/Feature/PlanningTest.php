<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\PlanningApiController;
use App\Http\Request;
use Tests\TestCase;

class PlanningTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $pdo = $this->pdo();
        $pdo->exec('CREATE TABLE IF NOT EXISTS planejamento (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome TEXT,
            materia TEXT,
            escola TEXT,
            professor TEXT,
            curso TEXT,
            ano TEXT,
            anosDoPlano TEXT,
            periodo TEXT,
            componenteCurricular TEXT,
            numeroDeAulas INTEGER,
            objetivoGeral TEXT,
            objetivoEspecifico TEXT,
            tipo TEXT,
            sequencial TEXT,
            projetosIntegrador TEXT,
            unidadeTematica TEXT,
            objetoDoConhecimento TEXT,
            grupo TEXT,
            conteudos TEXT,
            habilidades TEXT,
            metodologias TEXT,
            diagnostico TEXT,
            referencias TEXT,
            login INTEGER,
            created_date TEXT,
            updated_date TEXT,
            tempo INTEGER
        )');

        $pdo->exec('CREATE TABLE IF NOT EXISTS planejamento_linhas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            planejamento INTEGER,
            etapa TEXT,
            ano TEXT,
            areaConhecimento TEXT,
            componenteCurricular TEXT,
            unidadeTematicas TEXT,
            objetosConhecimento TEXT,
            habilidades TEXT,
            conteudos TEXT,
            metodologias TEXT,
            grupo TEXT,
            created_date TEXT,
            updated_date TEXT,
            FOREIGN KEY (planejamento) REFERENCES planejamento(id) ON DELETE CASCADE
        )');
    }

    public function testTeacherCanCreatePlanningWithLines(): void
    {
        $controller = new PlanningApiController();
        $request = new Request(body: [
            'acao' => 'criar',
            'nome-plano-mensal' => 'Plano Teste',
            'materia' => 'Matemática',
            'escola' => 'Escola Central',
            'periodo_realizacao' => '2024-01',
            'numero_aulas_semanais' => 2,
            'anos_plano' => ['1º Ano', '2º Ano'],
            'objetivo_geral' => 'Aprender frações',
            'objetivo_especifico' => 'Identificar frações simples',
            'tempo' => 3,
            'linhas_serializadas' => json_encode([
                [
                    'etapa' => 'Introdução',
                    'ano' => '1º Ano',
                    'area' => 'Matemática',
                    'componenteCurricular' => 'Cálculo',
                    'habilidades' => ['H1', 'H2'],
                    'conteudos' => 'Frações',
                    'metodologias' => 'Jogos',
                ],
            ], JSON_THROW_ON_ERROR),
        ]);
        $request->setUser([
            'id' => 10,
            'perfil' => 'Professor',
        ]);

        $response = $controller($request);

        $this->assertSame(200, $response->status());
        $payload = $response->data();
        $this->assertTrue($payload['sucesso']);
        $this->assertArrayHasKey('id', $payload);

        $planning = $this->pdo()->query('SELECT * FROM planejamento')->fetch(
            mode: \PDO::FETCH_ASSOC
        );
        $this->assertNotFalse($planning);
        $this->assertSame('Plano Teste', $planning['nome']);
        $this->assertSame(10, (int) $planning['login']);

        $line = $this->pdo()->query('SELECT * FROM planejamento_linhas')->fetch(\PDO::FETCH_ASSOC);
        $this->assertSame('Introdução', $line['etapa']);
        $this->assertSame('H1,H2', $line['habilidades']);
    }
}
