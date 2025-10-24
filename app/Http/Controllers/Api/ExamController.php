<?php

namespace App\Http\Controllers\Api;

use App\Auth\Profiles;
use App\Http\Controllers\Controller;
use App\Http\JsonResponse;
use App\Http\Request;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Turma;
use App\Models\User;
use App\Support\LegacyDatabase;
use App\Support\LegacySchema;
use RuntimeException;

class ExamController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user instanceof User) {
            return $this->json([
                'sucesso' => false,
                'mensagem' => 'Usuário não autenticado.',
            ], 401);
        }

        $profile = $user->profile ?? Profiles::Teacher;
        $isAdmin = $profile === Profiles::Administrator;
        $login = $user->login;
        $legacyId = $user->legacyId ?? null;

        $acao = $request->input('acao', 'listar');

        return match ($acao) {
            'listarTurmas' => $this->listarTurmas($isAdmin, $login),
            'listarMaterias' => $this->listarMaterias(),
            'listarQuestoes' => $this->listarQuestoes($request, $isAdmin, $legacyId),
            'listar' => $this->listarExams($isAdmin, $login),
            'buscar' => $this->buscarExam($request, $isAdmin, $login),
            'criar' => $this->salvarExam($request, $isAdmin, $login, true),
            'editar' => $this->salvarExam($request, $isAdmin, $login, false),
            'excluir' => $this->excluirExam($request, $isAdmin, $login),
            default => $this->json([
                'sucesso' => false,
                'mensagem' => 'Ação inválida.',
            ], 400),
        };
    }

    protected function listarTurmas(bool $isAdmin, string $login): JsonResponse
    {
        $turmas = Turma::listForUser($isAdmin, $login);

        return $this->json([
            'sucesso' => true,
            'turmas' => array_map(static fn (array $turma): array => [
                'id' => isset($turma['id']) ? (int) $turma['id'] : null,
                'nome' => (string) ($turma['nome'] ?? ''),
            ], $turmas),
        ]);
    }

    protected function listarMaterias(): JsonResponse
    {
        if (! LegacySchema::hasTable('bncc_componentes')) {
            return $this->json([
                'sucesso' => true,
                'materias' => [],
            ]);
        }

        $materias = LegacyDatabase::column('SELECT DISTINCT nome FROM bncc_componentes ORDER BY nome');
        $materias = array_map(static fn ($nome) => (string) $nome, $materias);

        return $this->json([
            'sucesso' => true,
            'materias' => $materias,
        ]);
    }

    protected function listarQuestoes(Request $request, bool $isAdmin, ?int $legacyId): JsonResponse
    {
        $materia = $request->input('materia');
        if (! is_string($materia)) {
            $materia = null;
        }

        $questoes = ExamQuestion::listAvailable($isAdmin, $legacyId, $materia);

        return $this->json([
            'sucesso' => true,
            'questoes' => array_map(static fn (array $questao): array => [
                'id' => isset($questao['id']) ? (int) $questao['id'] : null,
                'questao' => (string) ($questao['questao'] ?? ''),
                'materia' => $questao['materia'] ?? null,
            ], $questoes),
        ]);
    }

    protected function listarExams(bool $isAdmin, string $login): JsonResponse
    {
        $exams = Exam::list($isAdmin, $login);

        return $this->json([
            'sucesso' => true,
            'provas' => array_map(static fn (array $exam): array => [
                'id' => isset($exam['id']) ? (int) $exam['id'] : null,
                'turma' => $exam['turma'] ?? null,
                'materia' => $exam['materia'] ?? null,
                'escola' => $exam['escola'] ?? null,
                'lista_quest' => $exam['lista_quest'] ?? null,
            ], $exams),
        ]);
    }

    protected function buscarExam(Request $request, bool $isAdmin, string $login): JsonResponse
    {
        $id = (int) $request->input('id');
        if ($id <= 0) {
            return $this->json([
                'sucesso' => false,
                'mensagem' => 'ID inválido.',
            ], 422);
        }

        $exam = Exam::find($id, $isAdmin, $login);
        if ($exam === null) {
            return $this->json([
                'sucesso' => false,
                'mensagem' => 'Prova não encontrada.',
            ], 404);
        }

        return $this->json([
            'sucesso' => true,
            'dado' => $exam,
        ]);
    }

    protected function salvarExam(Request $request, bool $isAdmin, string $login, bool $isCreate): JsonResponse
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return $this->json([
                'sucesso' => false,
                'mensagem' => 'Método inválido.',
            ], 405);
        }

        $turma = trim((string) $request->input('turma'));
        $materia = trim((string) $request->input('materia'));
        $escola = $request->input('escola');
        if ($escola !== null) {
            $escola = trim((string) $escola);
        }

        $questoes = $this->normalizarListaQuestoes($request->input('lista_quest'));

        if ($turma === '' || $materia === '' || $questoes === []) {
            return $this->json([
                'sucesso' => false,
                'mensagem' => 'Turma, matéria e questões são obrigatórias.',
            ], 422);
        }

        $payload = [
            'turma' => $turma,
            'materia' => $materia,
            'escola' => $escola !== '' ? $escola : null,
            'lista_quest' => implode(',', $questoes),
        ];

        try {
            if ($isCreate) {
                Exam::create($payload, $login);
            } else {
                $id = (int) $request->input('id');
                if ($id <= 0) {
                    return $this->json([
                        'sucesso' => false,
                        'mensagem' => 'ID inválido.',
                    ], 422);
                }

                $updated = Exam::update($id, $payload, $isAdmin, $login);
                if (! $updated) {
                    return $this->json([
                        'sucesso' => false,
                        'mensagem' => 'Prova não encontrada.',
                    ], 404);
                }
            }
        } catch (RuntimeException $exception) {
            return $this->json([
                'sucesso' => false,
                'mensagem' => $exception->getMessage(),
            ], 500);
        }

        return $this->json([
            'sucesso' => true,
        ]);
    }

    protected function excluirExam(Request $request, bool $isAdmin, string $login): JsonResponse
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return $this->json([
                'sucesso' => false,
                'mensagem' => 'Método inválido.',
            ], 405);
        }

        $id = (int) $request->input('id');
        if ($id <= 0) {
            return $this->json([
                'sucesso' => false,
                'mensagem' => 'ID inválido.',
            ], 422);
        }

        if (! Exam::delete($id, $isAdmin, $login)) {
            return $this->json([
                'sucesso' => false,
                'mensagem' => 'Prova não encontrada.',
            ], 404);
        }

        return $this->json([
            'sucesso' => true,
        ]);
    }

    /**
     * @param mixed $lista
     * @return array<int,string>
     */
    protected function normalizarListaQuestoes(mixed $lista): array
    {
        if (is_string($lista)) {
            $lista = explode(',', $lista);
        }

        if (! is_array($lista)) {
            return [];
        }

        $ids = [];
        foreach ($lista as $valor) {
            if (is_array($valor)) {
                $valor = $valor['value'] ?? null;
            }

            if ($valor === null || $valor === '') {
                continue;
            }

            $valor = (string) $valor;
            $valor = trim($valor);
            if ($valor === '') {
                continue;
            }

            $ids[] = $valor;
        }

        $ids = array_values(array_unique($ids));

        return $ids;
    }
}
