<?php

namespace App\Http\Controllers\Api;

use App\Auth\Policies\PermissionMatrix;
use App\Auth\Profiles;
use App\Http\Controllers\Controller;
use App\Http\JsonResponse;
use App\Http\Request;
use App\Models\Materia;
use App\Models\Planning;
use App\Models\RoomReservation;
use App\Services\RoomReservationService;
use App\Support\LegacyDatabase;
use App\Support\LegacySchema;
use InvalidArgumentException;
use RuntimeException;

class PlanningApiController extends Controller
{
    protected RoomReservationService $reservations;

    public function __construct(?RoomReservationService $reservations = null)
    {
        $this->reservations = $reservations ?? new RoomReservationService();
    }

    public function __invoke(Request $request)
    {
        $acao = $request->input('acao', 'buscar_todos');
        $user = (array) $request->user();
        $perfil = $user['perfil'] ?? 'Professor';
        $userId = $user['id'] ?? null;
        $perfilEnum = Profiles::fromString((string) $perfil) ?? Profiles::Teacher;
        $schoolData = $user['school'] ?? [];
        $schoolId = null;
        if (is_array($schoolData) && isset($schoolData['id'])) {
            $schoolId = (int) $schoolData['id'];
        } elseif (isset($user['school_id'])) {
            $schoolId = (int) $user['school_id'];
        }

        return match ($acao) {
            'listar_ciclos' => $this->listarCiclos(),
            'materias_do_professor' => $this->listarMaterias(),
            'buscar' => $this->showPlanning($request, $perfil, $userId),
            'criar' => $this->storePlanning($request, $userId),
            'editar' => $this->updatePlanning($request, $perfil, $userId),
            'excluir' => $this->deletePlanning($request, $perfil, $userId),
            'bncc' => $this->bnccResponse($request),
            'listar_salas' => $this->listarSalas($perfilEnum, $schoolId),
            'listar_reservas' => $this->listarReservas($request, $perfilEnum, $schoolId, $userId),
            'reservar_sala' => $this->reservarSala($request, $perfilEnum, $schoolId, $userId),
            'aprovar_reserva' => $this->aprovarReserva($request, $perfilEnum, $schoolId, $userId),
            'cancelar_reserva' => $this->cancelarReserva($request, $perfilEnum, $schoolId, $userId),
            default => $this->listPlanning($request, $perfil, $userId),
        };
    }

    protected function listarCiclos()
    {
        return response()->json([
            ['id' => 'mensal', 'nome' => 'Mensal'],
            ['id' => 'bimestral', 'nome' => 'Bimestral'],
            ['id' => 'trimestral', 'nome' => 'Trimestral'],
            ['id' => 'semestral', 'nome' => 'Semestral'],
            ['id' => 'anual', 'nome' => 'Anual'],
        ]);
    }

    protected function listarMaterias()
    {
        return response()->json(Materia::listAll());
    }

    protected function listPlanning(Request $request, string $perfil, ?int $userId)
    {
        $termo = $request->input('pesquisa');
        $lista = Planning::list(
            is_string($termo) ? $termo : null,
            $userId ? (int) $userId : null,
            $perfil === 'Administrador'
        );

        return response()->json([
            'sucesso' => true,
            'data' => $lista,
        ]);
    }

    protected function showPlanning(Request $request, string $perfil, ?int $userId)
    {
        $id = (int) $request->input('id');
        if (!$id) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => 'ID inválido.',
            ], 422);
        }

        $planning = Planning::findWithLines(
            $id,
            $userId ? (int) $userId : null,
            $perfil === 'Administrador'
        );

        if (!$planning) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => 'Planejamento não encontrado.',
            ], 404);
        }

        return response()->json([
            'sucesso' => true,
            'cabecalho' => $this->mapCabecalho($planning['cabecalho']),
            'linhas' => array_map(fn ($linha) => $this->mapLinha($linha), $planning['linhas']),
        ]);
    }

    protected function storePlanning(Request $request, ?int $userId)
    {
        if (!$userId) {
            return response()->json(['sucesso' => false, 'mensagem' => 'Usuário não identificado.'], 401);
        }

        [$dados, $linhas] = $this->extractPayload($request);

        try {
            $id = Planning::create($dados, $linhas, (int) $userId);

            return response()->json([
                'sucesso' => true,
                'mensagem' => 'Planejamento cadastrado com sucesso.',
                'id' => $id,
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => $exception->getMessage(),
            ], 500);
        }
    }

    protected function updatePlanning(Request $request, string $perfil, ?int $userId)
    {
        $id = (int) $request->input('id-planejamento-mensal');
        if (!$id) {
            return response()->json(['sucesso' => false, 'mensagem' => 'ID inválido.'], 422);
        }

        [$dados, $linhas] = $this->extractPayload($request);
        try {
            $atualizado = Planning::update(
                $id,
                $dados,
                $linhas,
                $userId ? (int) $userId : null,
                $perfil === 'Administrador'
            );

            if (! $atualizado) {
                return response()->json(['sucesso' => false, 'mensagem' => 'Planejamento não encontrado.'], 404);
            }

            return response()->json([
                'sucesso' => true,
                'mensagem' => 'Planejamento atualizado com sucesso.',
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => $exception->getMessage(),
            ], 500);
        }
    }

    protected function deletePlanning(Request $request, string $perfil, ?int $userId)
    {
        $id = (int) $request->input('id');
        if (!$id) {
            return response()->json(['sucesso' => false, 'mensagem' => 'ID inválido.'], 422);
        }

        $removido = Planning::delete(
            $id,
            $userId ? (int) $userId : null,
            $perfil === 'Administrador'
        );

        if (! $removido) {
            return response()->json(['sucesso' => false, 'mensagem' => 'Planejamento não encontrado.'], 404);
        }

        return response()->json([
            'sucesso' => true,
            'mensagem' => 'Planejamento excluído com sucesso.',
        ]);
    }

    protected function listarSalas(Profiles $perfil, ?int $schoolId): JsonResponse
    {
        $rooms = $this->reservations->getRoomsForSchool($schoolId);

        return response()->json([
            'sucesso' => true,
            'salas' => array_map(static function ($room) {
                return [
                    'id' => $room->id,
                    'nome' => $room->name,
                    'capacidade' => $room->capacity,
                    'localizacao' => $room->location,
                    'descricao' => $room->description,
                ];
            }, $rooms),
            'pode_aprovar' => PermissionMatrix::allows($perfil, PermissionMatrix::RESERVATIONS_APPROVE),
        ]);
    }

    protected function listarReservas(Request $request, Profiles $perfil, ?int $schoolId, ?int $userId): JsonResponse
    {
        $filters = [];
        if ($request->filled('room_id')) {
            $filters['room_id'] = (int) $request->input('room_id');
        }

        if ($request->filled('planning_id')) {
            $filters['planning_id'] = (int) $request->input('planning_id');
        }

        if ($request->filled('inicio')) {
            $filters['starts_at'] = $this->normalizarData($request->input('inicio'));
        }

        if ($request->filled('fim')) {
            $filters['ends_at'] = $this->normalizarData($request->input('fim'));
        }

        try {
            $reservas = $this->reservations->listReservations($filters, $schoolId);
            $canApprove = PermissionMatrix::allows($perfil, PermissionMatrix::RESERVATIONS_APPROVE);

            return response()->json([
                'sucesso' => true,
                'reservas' => array_map(fn (RoomReservation $reserva) => $this->mapReserva($reserva, $userId, $canApprove), $reservas),
                'pode_aprovar' => $canApprove,
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => $exception->getMessage(),
            ], 500);
        }
    }

    protected function reservarSala(Request $request, Profiles $perfil, ?int $schoolId, ?int $userId): JsonResponse
    {
        if (! PermissionMatrix::allows($perfil, PermissionMatrix::RESERVATIONS_CREATE)) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => 'Permissão insuficiente para reservar salas.',
            ], 403);
        }

        $userId = $userId ? (int) $userId : null;
        if ($userId === null) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => 'Usuário não identificado para registrar a reserva.',
            ], 401);
        }

        $roomId = (int) $request->input('room_id');
        $planningId = (int) $request->input('planning_id');
        $inicio = $this->normalizarData($request->input('inicio'));
        $fim = $this->normalizarData($request->input('fim'));
        $notes = $request->input('observacoes');

        if ($roomId <= 0 || $planningId <= 0 || ! $inicio || ! $fim) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => 'Sala, planejamento e horários são obrigatórios.',
            ], 422);
        }

        $autoApprove = PermissionMatrix::allows($perfil, PermissionMatrix::RESERVATIONS_APPROVE);

        try {
            $reserva = $this->reservations->createReservation(
                $roomId,
                $planningId,
                $userId,
                $inicio,
                $fim,
                $autoApprove,
                $notes ?: null,
                null,
                $schoolId,
                $autoApprove
            );

            $mensagem = $autoApprove
                ? 'Reserva confirmada com sucesso.'
                : 'Reserva registrada e aguardando aprovação.';

            return response()->json([
                'sucesso' => true,
                'mensagem' => $mensagem,
                'reserva' => $this->mapReserva($reserva, $userId, $autoApprove),
                'auto_aprovada' => $autoApprove,
            ]);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => $exception->getMessage(),
            ], 422);
        } catch (RuntimeException $exception) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => $exception->getMessage(),
            ], 400);
        }
    }

    protected function aprovarReserva(Request $request, Profiles $perfil, ?int $schoolId, ?int $userId): JsonResponse
    {
        if (! PermissionMatrix::allows($perfil, PermissionMatrix::RESERVATIONS_APPROVE)) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => 'Permissão insuficiente para aprovar reservas.',
            ], 403);
        }

        $userId = $userId ? (int) $userId : null;
        if ($userId === null) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => 'Usuário não identificado para aprovar a reserva.',
            ], 401);
        }

        $reservaId = (int) $request->input('reserva_id');
        $decisao = (string) $request->input('decisao');
        $comentario = $request->input('comentario');

        $status = match (strtolower($decisao)) {
            'aprovar' => RoomReservation::STATUS_APPROVED,
            'rejeitar' => RoomReservation::STATUS_REJECTED,
            'cancelar' => RoomReservation::STATUS_CANCELLED,
            default => null,
        };

        if ($reservaId <= 0 || $status === null) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => 'Dados inválidos para atualização da reserva.',
            ], 422);
        }

        try {
            $reserva = $this->reservations->updateStatus($reservaId, $status, $userId, $comentario ?: null, $schoolId);

            $mensagem = match ($status) {
                RoomReservation::STATUS_APPROVED => 'Reserva aprovada com sucesso.',
                RoomReservation::STATUS_REJECTED => 'Reserva rejeitada com sucesso.',
                RoomReservation::STATUS_CANCELLED => 'Reserva cancelada com sucesso.',
                default => 'Reserva atualizada.',
            };

            return response()->json([
                'sucesso' => true,
                'mensagem' => $mensagem,
                'reserva' => $this->mapReserva($reserva, $userId, true),
            ]);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => $exception->getMessage(),
            ], 422);
        } catch (RuntimeException $exception) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => $exception->getMessage(),
            ], 400);
        }
    }

    protected function cancelarReserva(Request $request, Profiles $perfil, ?int $schoolId, ?int $userId): JsonResponse
    {
        $reservaId = (int) $request->input('reserva_id');
        if ($reservaId <= 0) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => 'Reserva inválida.',
            ], 422);
        }

        $userId = $userId ? (int) $userId : null;
        if ($userId === null) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => 'Usuário não autenticado para cancelar a reserva.',
            ], 401);
        }

        try {
            $reserva = $this->reservations->cancelReservation($reservaId, $userId, $schoolId);

            return response()->json([
                'sucesso' => true,
                'mensagem' => 'Reserva cancelada com sucesso.',
                'reserva' => $this->mapReserva($reserva, $userId, PermissionMatrix::allows($perfil, PermissionMatrix::RESERVATIONS_APPROVE)),
            ]);
        } catch (RuntimeException $exception) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => $exception->getMessage(),
            ], 400);
        }
    }

    protected function mapReserva(RoomReservation $reserva, ?int $userId, bool $canApprove): array
    {
        return [
            'id' => $reserva->id,
            'room_id' => $reserva->roomId,
            'sala' => $reserva->roomName,
            'inicio' => $reserva->startsAt->format('c'),
            'fim' => $reserva->endsAt->format('c'),
            'status' => $reserva->status,
            'solicitante' => $reserva->reservedByName,
            'aprovador' => $reserva->approvedByName,
            'aprovado_em' => $reserva->approvedAt?->format('c'),
            'observacoes' => $reserva->notes,
            'pode_aprovar' => $canApprove && $reserva->isPending(),
            'pode_cancelar' => $userId !== null && $reserva->reservedBy === $userId,
        ];
    }

    protected function normalizarData(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $valor = trim((string) $valor);
        if ($valor === '') {
            return null;
        }

        $valor = str_replace('T', ' ', $valor);
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $valor) === 1) {
            $valor .= ':00';
        }

        return $valor;
    }

    protected function bnccResponse(Request $request)
    {
        $campo = $request->input('campo', 'mapas');

        return response()->json([
            'sucesso' => true,
            'campo' => $campo,
            'dados' => match ($campo) {
                'mapas' => $this->bnccMapasCompletos(),
                'etapas' => $this->bnccLista('bncc_etapas'),
                'anos' => $this->bnccListaAnos($request->input('id_etapa')),
                'areas' => $this->bnccLista('bncc_areas', 'id', 'nome', $this->filtros($request, ['id_etapa'])),
                'componentes' => $this->bnccLista('bncc_componentes', 'id', 'nome', $this->filtros($request, ['id_area'])),
                'unidades_tematicas' => $this->bnccLista('bncc_unidades_tematicas', 'id', 'nome', $this->filtros($request, ['id_componente'])),
                'objetosConhecimento' => $this->bnccLista('bncc_objetos_conhecimento', 'id', 'nome', $this->filtros($request, ['id_unidade_tematica'])),
                'habilidades' => $this->bnccListaHabilidades($this->filtros($request, ['id_objeto'])),
                default => [],
            },
        ]);
    }

    protected function bnccMapasCompletos(): array
    {
        return [
            'options' => [
                'etapas' => $this->bnccLista('bncc_etapas'),
                'anos' => $this->bnccListaAnos(),
                'areas' => $this->bnccLista('bncc_areas'),
                'componentes' => $this->bnccLista('bncc_componentes'),
                'unidades_tematicas' => $this->bnccLista('bncc_unidades_tematicas'),
                'objetosConhecimento' => $this->bnccLista('bncc_objetos_conhecimento'),
                'habilidades' => $this->bnccListaHabilidades(),
            ],
            'lookups' => [
                'etapas' => $this->bnccLookup('bncc_etapas'),
                'anos' => $this->bnccLookupAnos(),
                'areas' => $this->bnccLookup('bncc_areas'),
                'componentes' => $this->bnccLookup('bncc_componentes'),
                'unidades_tematicas' => $this->bnccLookup('bncc_unidades_tematicas'),
                'objetosConhecimento' => $this->bnccLookup('bncc_objetos_conhecimento'),
                'habilidades' => $this->bnccLookupHabilidades(),
                'habilidades_codigo' => $this->bnccLookupHabilidades(true),
            ],
        ];
    }

    protected function bnccLista(string $tabela, string $colunaId = 'id', string $colunaLabel = 'nome', array $filtros = []): array
    {
        if (!LegacySchema::hasTable($tabela)) {
            return [];
        }

        $colunaId = $this->sanitizeIdentifier($colunaId, 'id');
        $colunaLabel = $this->sanitizeIdentifier($colunaLabel, 'nome');

        $conditions = [];
        $params = [];
        foreach ($filtros as $coluna => $valor) {
            if ($valor === null || $valor === '') {
                continue;
            }

            $coluna = $this->sanitizeIdentifier($coluna, $coluna);
            if (!LegacySchema::hasColumn($tabela, $coluna)) {
                continue;
            }

            $param = 'f_' . $coluna . count($params);
            $conditions[] = sprintf('%s = :%s', $coluna, $param);
            $params[$param] = $valor;
        }

        $sql = sprintf('SELECT %s AS id, %s AS label FROM %s', $colunaId, $colunaLabel, $tabela);
        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY ' . $colunaLabel;

        $rows = LegacyDatabase::select($sql, $params);

        return array_map(static function (array $row): array {
            return [
                'id' => (string) ($row['id'] ?? ''),
                'label' => (string) ($row['label'] ?? ''),
            ];
        }, $rows);
    }

    protected function bnccListaAnos(int|string|null $etapaId = null): array
    {
        if (!LegacySchema::hasTable('bncc_anos')) {
            return [];
        }

        $sql = 'SELECT DISTINCT ano FROM bncc_anos';
        $params = [];

        if ($etapaId !== null && $etapaId !== '' && LegacySchema::hasColumn('bncc_anos', 'id_etapa')) {
            $sql .= ' WHERE id_etapa = :etapa';
            $params['etapa'] = $etapaId;
        }

        $sql .= ' ORDER BY ano';

        $valores = LegacyDatabase::column($sql, $params);

        $resultado = [];
        foreach ($valores as $valor) {
            if ($valor === null || $valor === '') {
                continue;
            }
            $resultado[] = ['id' => (string) $valor, 'label' => (string) $valor];
        }

        return $resultado;
    }

    protected function bnccLookup(string $tabela, string $colunaLabel = 'nome'): array
    {
        if (!LegacySchema::hasTable($tabela)) {
            return [];
        }

        $colunaLabel = $this->sanitizeIdentifier($colunaLabel, 'nome');

        $sql = sprintf('SELECT id, %s AS label FROM %s ORDER BY %s', $colunaLabel, $tabela, $colunaLabel);
        $rows = LegacyDatabase::select($sql);

        $resultado = [];
        foreach ($rows as $row) {
            if (! isset($row['id'])) {
                continue;
            }
            $resultado[(string) $row['id']] = (string) ($row['label'] ?? '');
        }

        return $resultado;
    }

    protected function bnccLookupAnos(): array
    {
        if (!LegacySchema::hasTable('bncc_anos')) {
            return [];
        }

        $valores = LegacyDatabase::column('SELECT DISTINCT ano FROM bncc_anos ORDER BY ano');

        $resultado = [];
        foreach ($valores as $valor) {
            if ($valor === null || $valor === '') {
                continue;
            }

            $resultado[(string) $valor] = (string) $valor;
        }

        return $resultado;
    }

    protected function bnccListaHabilidades(array $filtros = []): array
    {
        if (!LegacySchema::hasTable('bncc_habilidades')) {
            return [];
        }

        $sql = 'SELECT id, codigo, descricao FROM bncc_habilidades';
        $params = [];

        if (!empty($filtros['id_objeto'])) {
            $sql .= ' WHERE id_objeto = :objeto';
            $params['objeto'] = $filtros['id_objeto'];
        }

        $sql .= ' ORDER BY codigo';

        $rows = LegacyDatabase::select($sql, $params);

        return array_map(static function (array $row): array {
            $codigo = (string) ($row['codigo'] ?? '');
            $descricao = (string) ($row['descricao'] ?? '');
            $label = trim($codigo !== '' ? sprintf('%s – %s', $codigo, $descricao) : $descricao);

            return [
                'id' => (string) ($row['id'] ?? ''),
                'label' => $label,
                'codigo' => $codigo,
            ];
        }, $rows);
    }

    protected function bnccLookupHabilidades(bool $porCodigo = false): array
    {
        if (!LegacySchema::hasTable('bncc_habilidades')) {
            return [];
        }

        $coluna = $porCodigo ? 'codigo' : 'id';
        $coluna = $this->sanitizeIdentifier($coluna, $porCodigo ? 'codigo' : 'id');

        $sql = sprintf('SELECT %s AS chave, codigo, descricao FROM bncc_habilidades ORDER BY codigo', $coluna);
        $rows = LegacyDatabase::select($sql);

        $resultado = [];
        foreach ($rows as $row) {
            if (! isset($row['chave'])) {
                continue;
            }
            $codigo = (string) ($row['codigo'] ?? '');
            $descricao = (string) ($row['descricao'] ?? '');
            $resultado[(string) $row['chave']] = trim($codigo !== '' ? sprintf('%s – %s', $codigo, $descricao) : $descricao);
        }

        return $resultado;
    }

    protected function filtros(Request $request, array $chaves): array
    {
        $resultado = [];
        foreach ($chaves as $chave) {
            $valor = $request->input($chave);
            if ($valor !== null && $valor !== '') {
                $resultado[$chave] = $valor;
            }
        }

        return $resultado;
    }

    protected function extractPayload(Request $request): array
    {
        $dados = [
            'nome' => $request->input('nome-plano-mensal'),
            'materia' => $request->input('materia'),
            'escola' => $request->input('escola'),
            'periodo' => $request->input('periodo_realizacao'),
            'numeroDeAulas' => $request->integer('numero_aulas_semanais'),
            'anosDoPlano' => $this->csv($request->input('anos_plano', [])),
            'objetivoGeral' => $request->input('objetivo_geral'),
            'objetivoEspecifico' => $request->input('objetivo_especifico'),
            'tempo' => $request->integer('tempo', 1),
        ];

        $raw = json_decode((string) $request->input('linhas_serializadas', '[]'), true);
        $linhas = [];
        if (is_array($raw)) {
            foreach ($raw as $linha) {
                if (!is_array($linha) || empty($linha['etapa'])) {
                    continue;
                }

                $linhas[] = [
                    'id' => $linha['id'] ?? null,
                    'etapa' => $linha['etapa'],
                    'ano' => $linha['ano'] ?? null,
                    'areaConhecimento' => $linha['area'] ?? null,
                    'componenteCurricular' => $linha['componenteCurricular'] ?? null,
                    'unidadeTematicas' => $linha['unidadeTematicas'] ?? null,
                    'objetosConhecimento' => $linha['objetosConhecimento'] ?? null,
                    'habilidades' => $this->csv($linha['habilidades'] ?? []),
                    'conteudos' => $linha['conteudos'] ?? null,
                    'metodologias' => $linha['metodologias'] ?? null,
                    'grupo' => $linha['grupo'] ?? null,
                ];
            }
        }

        return [$dados, $linhas];
    }

    protected function csv($value): ?string
    {
        if (is_array($value)) {
            $items = [];
            foreach ($value as $item) {
                if ($item === null) {
                    continue;
                }

                $texto = trim((string) $item);
                if ($texto !== '') {
                    $items[] = $texto;
                }
            }

            return $items ? implode(',', $items) : null;
        }

        if ($value === null) {
            return null;
        }

        $texto = trim((string) $value);

        return $texto !== '' ? $texto : null;
    }

    protected function mapCabecalho(array $dados): array
    {
        return [
            'id-planejamento-mensal' => $dados['id'] ?? null,
            'nome-plano-mensal' => $dados['nome'] ?? null,
            'materia' => $dados['materia'] ?? null,
            'escola' => $dados['escola'] ?? null,
            'professor' => $dados['professor'] ?? null,
            'curso' => $dados['curso'] ?? null,
            'ano' => $dados['ano'] ?? null,
            'anos_plano' => $dados['anosDoPlano'] ?? null,
            'periodo_realizacao' => $dados['periodo'] ?? null,
            'componente_curricular' => $dados['componenteCurricular'] ?? null,
            'numero_aulas_semanais' => $dados['numeroDeAulas'] ?? null,
            'objetivo_geral' => $dados['objetivoGeral'] ?? null,
            'objetivo_especifico' => $dados['objetivoEspecifico'] ?? null,
            'tipo' => $dados['tipo'] ?? null,
            'sequencial' => $dados['sequencial'] ?? null,
            'projetos_integrador' => $dados['projetosIntegrador'] ?? null,
            'unidade_tematica' => $dados['unidadeTematica'] ?? null,
            'objeto_do_conhecimento' => $dados['objetoDoConhecimento'] ?? null,
            'grupo' => $dados['grupo'] ?? null,
            'conteudos' => $dados['conteudos'] ?? null,
            'habilidades' => $dados['habilidades'] ?? null,
            'metodologias' => $dados['metodologias'] ?? null,
            'diagnostico' => $dados['diagnostico'] ?? null,
            'referencias' => $dados['referencias'] ?? null,
            'created_date' => $dados['created_date'] ?? null,
            'updated_date' => $dados['updated_date'] ?? null,
            'login' => $dados['login'] ?? null,
            'tempo' => $dados['tempo'] ?? null,
        ];
    }

    protected function mapLinha(array $dados): array
    {
        return [
            'id' => $dados['id'] ?? null,
            'etapa' => $dados['etapa'] ?? null,
            'ano' => $dados['ano'] ?? null,
            'area' => $dados['areaConhecimento'] ?? null,
            'componenteCurricular' => $dados['componenteCurricular'] ?? null,
            'unidadeTematicas' => $dados['unidadeTematicas'] ?? null,
            'objetosConhecimento' => $dados['objetosConhecimento'] ?? null,
            'habilidades' => isset($dados['habilidades']) ? explode(',', (string) $dados['habilidades']) : [],
            'conteudos' => $dados['conteudos'] ?? null,
            'metodologias' => $dados['metodologias'] ?? null,
            'grupo' => $dados['grupo'] ?? null,
        ];
    }

    protected function sanitizeIdentifier(string $valor, string $fallback = 'id'): string
    {
        return preg_match('/^[A-Za-z0-9_]+$/', $valor) ? $valor : $fallback;
    }
}
