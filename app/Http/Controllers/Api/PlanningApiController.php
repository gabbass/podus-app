<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Materia;
use App\Models\Planning;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PlanningApiController extends Controller
{
    public function __invoke(Request $request)
    {
        $acao = $request->input('acao', 'buscar_todos');
        $user = (array) $request->user();
        $perfil = $user['perfil'] ?? 'Professor';
        $userId = $user['id'] ?? null;

        return match ($acao) {
            'listar_ciclos' => $this->listarCiclos(),
            'materias_do_professor' => $this->listarMaterias(),
            'buscar' => $this->showPlanning($request, $perfil, $userId),
            'criar' => $this->storePlanning($request, $userId),
            'editar' => $this->updatePlanning($request, $perfil, $userId),
            'excluir' => $this->deletePlanning($request, $perfil, $userId),
            'bncc' => $this->bnccResponse($request),
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
        return response()->json(
            Materia::orderBy('nome')->get(['id', 'nome as label'])
        );
    }

    protected function listPlanning(Request $request, string $perfil, ?int $userId)
    {
        $termo = $request->input('pesquisa');
        $query = Planning::query()->orderByDesc('created_date');

        if ($perfil !== 'Administrador' && $userId) {
            $query->where('login', $userId);
        }

        if ($termo) {
            $query->where(function ($q) use ($termo) {
                $q->where('nome', 'like', "%{$termo}%")
                  ->orWhere('periodo', 'like', "%{$termo}%");
            });
        }

        return response()->json([
            'sucesso' => true,
            'data' => $query->get(),
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

        $planning = Planning::with('linhas')
            ->when($perfil !== 'Administrador' && $userId, fn ($q) => $q->where('login', $userId))
            ->find($id);

        if (!$planning) {
            return response()->json([
                'sucesso' => false,
                'mensagem' => 'Planejamento não encontrado.',
            ], 404);
        }

        return response()->json([
            'sucesso' => true,
            'cabecalho' => $this->mapCabecalho($planning->toArray()),
            'linhas' => $planning->linhas->map(fn ($linha) => $this->mapLinha($linha->toArray()))->all(),
        ]);
    }

    protected function storePlanning(Request $request, ?int $userId)
    {
        if (!$userId) {
            return response()->json(['sucesso' => false, 'mensagem' => 'Usuário não identificado.'], 401);
        }

        return DB::transaction(function () use ($request, $userId) {
            [$dados, $linhas] = $this->extractPayload($request);
            $dados['login'] = $userId;
            $dados['created_date'] = now();
            $dados['updated_date'] = now();

            $planning = Planning::create($dados);

            foreach ($linhas as $linha) {
                $planning->linhas()->create($linha + [
                    'created_date' => now(),
                    'updated_date' => now(),
                ]);
            }

            return response()->json([
                'sucesso' => true,
                'mensagem' => 'Planejamento cadastrado com sucesso.',
                'id' => $planning->id,
            ]);
        });
    }

    protected function updatePlanning(Request $request, string $perfil, ?int $userId)
    {
        $id = (int) $request->input('id-planejamento-mensal');
        if (!$id) {
            return response()->json(['sucesso' => false, 'mensagem' => 'ID inválido.'], 422);
        }

        $planning = Planning::query()
            ->when($perfil !== 'Administrador' && $userId, fn ($q) => $q->where('login', $userId))
            ->find($id);

        if (!$planning) {
            return response()->json(['sucesso' => false, 'mensagem' => 'Planejamento não encontrado.'], 404);
        }

        [$dados, $linhas] = $this->extractPayload($request);
        $dados['updated_date'] = now();

        return DB::transaction(function () use ($planning, $dados, $linhas) {
            $planning->update($dados);

            $idsMantidos = [];
            foreach ($linhas as $linha) {
                $linhaId = Arr::pull($linha, 'id');
                if ($linhaId) {
                    $planning->linhas()->whereKey($linhaId)->update($linha + ['updated_date' => now()]);
                    $idsMantidos[] = $linhaId;
                } else {
                    $nova = $planning->linhas()->create($linha + [
                        'created_date' => now(),
                        'updated_date' => now(),
                    ]);
                    $idsMantidos[] = $nova->id;
                }
            }

            if (!empty($idsMantidos)) {
                $planning->linhas()->whereNotIn('id', $idsMantidos)->delete();
            }

            return response()->json([
                'sucesso' => true,
                'mensagem' => 'Planejamento atualizado com sucesso.',
            ]);
        });
    }

    protected function deletePlanning(Request $request, string $perfil, ?int $userId)
    {
        $id = (int) $request->input('id');
        if (!$id) {
            return response()->json(['sucesso' => false, 'mensagem' => 'ID inválido.'], 422);
        }

        $planning = Planning::query()
            ->when($perfil !== 'Administrador' && $userId, fn ($q) => $q->where('login', $userId))
            ->find($id);

        if (!$planning) {
            return response()->json(['sucesso' => false, 'mensagem' => 'Planejamento não encontrado.'], 404);
        }

        $planning->delete();

        return response()->json([
            'sucesso' => true,
            'mensagem' => 'Planejamento excluído com sucesso.',
        ]);
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
        if (!Schema::hasTable($tabela)) {
            return [];
        }

        $query = DB::table($tabela)
            ->select([$colunaId . ' as id', $colunaLabel . ' as label']);

        foreach ($filtros as $coluna => $valor) {
            if ($valor !== null && $valor !== '' && Schema::hasColumn($tabela, $coluna)) {
                $query->where($coluna, $valor);
            }
        }

        return $query
            ->orderBy($colunaLabel)
            ->get()
            ->map(fn ($item) => ['id' => (string) $item->id, 'label' => (string) $item->label])
            ->all();
    }

    protected function bnccListaAnos(int|string|null $etapaId = null): array
    {
        if (!Schema::hasTable('bncc_anos')) {
            return [];
        }

        $query = DB::table('bncc_anos')
            ->select('ano')
            ->distinct()
            ->orderBy('ano');

        if ($etapaId !== null && $etapaId !== '' && Schema::hasColumn('bncc_anos', 'id_etapa')) {
            $query->where('id_etapa', $etapaId);
        }

        return $query->pluck('ano')
            ->filter(fn ($valor) => $valor !== null && $valor !== '')
            ->map(fn ($ano) => ['id' => (string) $ano, 'label' => (string) $ano])
            ->values()
            ->all();
    }

    protected function bnccLookup(string $tabela, string $colunaLabel = 'nome'): array
    {
        if (!Schema::hasTable($tabela)) {
            return [];
        }

        return DB::table($tabela)
            ->select(['id', $colunaLabel . ' as label'])
            ->orderBy($colunaLabel)
            ->pluck('label', 'id')
            ->map(fn ($label) => (string) $label)
            ->all();
    }

    protected function bnccLookupAnos(): array
    {
        if (!Schema::hasTable('bncc_anos')) {
            return [];
        }

        return DB::table('bncc_anos')
            ->select('ano')
            ->distinct()
            ->orderBy('ano')
            ->pluck('ano')
            ->filter(fn ($valor) => $valor !== null && $valor !== '')
            ->mapWithKeys(fn ($ano) => [(string) $ano => (string) $ano])
            ->all();
    }

    protected function bnccListaHabilidades(array $filtros = []): array
    {
        if (!Schema::hasTable('bncc_habilidades')) {
            return [];
        }

        $query = DB::table('bncc_habilidades')
            ->select(['id', 'codigo', 'descricao']);

        if (!empty($filtros['id_objeto'])) {
            $query->where('id_objeto', $filtros['id_objeto']);
        }

        return $query
            ->orderBy('codigo')
            ->get()
            ->map(function ($item) {
                $label = trim(sprintf('%s – %s', $item->codigo, $item->descricao));

                return [
                    'id' => (string) $item->id,
                    'label' => $label,
                    'codigo' => (string) $item->codigo,
                ];
            })
            ->all();
    }

    protected function bnccLookupHabilidades(bool $porCodigo = false): array
    {
        if (!Schema::hasTable('bncc_habilidades')) {
            return [];
        }

        $colunaChave = $porCodigo ? 'codigo' : 'id';

        return DB::table('bncc_habilidades')
            ->select([$colunaChave, 'codigo', 'descricao'])
            ->orderBy('codigo')
            ->get()
            ->mapWithKeys(function ($item) use ($colunaChave) {
                $label = trim(sprintf('%s – %s', $item->codigo, $item->descricao));
                $key = $colunaChave === 'codigo' ? (string) $item->codigo : (string) $item->id;

                return [$key => $label];
            })
            ->all();
    }

    protected function filtros(Request $request, array $chaves): array
    {
        return collect($chaves)
            ->mapWithKeys(fn ($chave) => [$chave => $request->input($chave)])
            ->filter(fn ($valor) => $valor !== null && $valor !== '')
            ->all();
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

        $linhas = collect(json_decode($request->input('linhas_serializadas', '[]'), true) ?: [])
            ->filter(fn ($linha) => !empty($linha['etapa']))
            ->map(function ($linha) {
                return [
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
            })->values()->all();

        return [$dados, $linhas];
    }

    protected function csv($value): ?string
    {
        if (is_array($value)) {
            return collect($value)
                ->filter(fn ($item) => filled($item))
                ->map(fn ($item) => Str::of($item)->trim())
                ->implode(',');
        }

        return $value ? (string) $value : null;
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
}
