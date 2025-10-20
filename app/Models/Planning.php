<?php

namespace App\Models;

use DateTimeImmutable;
use LegacyConfig;
use PDO;
use PDOException;
use RuntimeException;

class Planning
{
    /**
     * @return array<int,array<string,mixed>>
     */
    public static function list(?string $term, ?int $userId, bool $isAdmin): array
    {
        $pdo = LegacyConfig::createPdo();
        $conditions = [];
        $params = [];

        if (! $isAdmin && $userId !== null) {
            $conditions[] = 'login = :login';
            $params['login'] = $userId;
        }

        if ($term !== null && $term !== '') {
            $conditions[] = '(nome LIKE :term OR periodo LIKE :term)';
            $params['term'] = '%' . $term . '%';
        }

        $sql = 'SELECT * FROM planejamento';
        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql .= ' ORDER BY created_date DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (! $rows) {
            return [];
        }

        return array_map(static function (array $row): array {
            return array_change_key_case($row, CASE_LOWER);
        }, $rows);
    }

    /**
     * @return array{cabecalho:array<string,mixed>,linhas:array<int,array<string,mixed>>}|null
     */
    public static function findWithLines(int $id, ?int $userId, bool $isAdmin): ?array
    {
        $pdo = LegacyConfig::createPdo();
        $conditions = ['id = :id'];
        $params = ['id' => $id];

        if (! $isAdmin && $userId !== null) {
            $conditions[] = 'login = :login';
            $params['login'] = $userId;
        }

        $sql = 'SELECT * FROM planejamento WHERE ' . implode(' AND ', $conditions) . ' LIMIT 1';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $cabecalho = $stmt->fetch(PDO::FETCH_ASSOC);

        if (! $cabecalho) {
            return null;
        }

        $linStmt = $pdo->prepare('SELECT * FROM planejamento_linhas WHERE planejamento = :id ORDER BY id');
        $linStmt->execute(['id' => $id]);
        $linhas = $linStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return [
            'cabecalho' => $cabecalho,
            'linhas' => $linhas,
        ];
    }

    /**
     * @param array<string,mixed> $dados
     * @param array<int,array<string,mixed>> $linhas
     */
    public static function create(array $dados, array $linhas, int $userId): int
    {
        $pdo = LegacyConfig::createPdo();
        $pdo->beginTransaction();

        try {
            $now = new DateTimeImmutable();
            $stmt = $pdo->prepare('INSERT INTO planejamento (
                nome, materia, escola, professor, curso, ano, anosDoPlano, periodo,
                componenteCurricular, numeroDeAulas, objetivoGeral, objetivoEspecifico,
                tipo, sequencial, projetosIntegrador, unidadeTematica, objetoDoConhecimento,
                grupo, conteudos, habilidades, metodologias, diagnostico, referencias,
                login, created_date, updated_date, tempo
            ) VALUES (
                :nome, :materia, :escola, :professor, :curso, :ano, :anosDoPlano, :periodo,
                :componenteCurricular, :numeroDeAulas, :objetivoGeral, :objetivoEspecifico,
                :tipo, :sequencial, :projetosIntegrador, :unidadeTematica, :objetoDoConhecimento,
                :grupo, :conteudos, :habilidades, :metodologias, :diagnostico, :referencias,
                :login, :created, :updated, :tempo
            )');

            $payload = self::mapPayload($dados);
            $payload['login'] = $userId;
            $payload['created'] = $now->format('Y-m-d H:i:s');
            $payload['updated'] = $now->format('Y-m-d H:i:s');
            $payload['tempo'] = isset($dados['tempo']) ? (int) $dados['tempo'] : 1;

            $stmt->execute($payload);
            $planningId = (int) $pdo->lastInsertId();

            self::syncLinhas($pdo, $planningId, $linhas);

            $pdo->commit();

            return $planningId;
        } catch (PDOException $exception) {
            $pdo->rollBack();
            throw new RuntimeException('Falha ao salvar planejamento: ' . $exception->getMessage(), 0, $exception);
        }
    }

    /**
     * @param array<string,mixed> $dados
     * @param array<int,array<string,mixed>> $linhas
     */
    public static function update(int $id, array $dados, array $linhas, ?int $userId, bool $isAdmin): bool
    {
        $pdo = LegacyConfig::createPdo();
        $pdo->beginTransaction();

        try {
            $conditions = ['id = :id'];
            $params = ['id' => $id];

            if (! $isAdmin && $userId !== null) {
                $conditions[] = 'login = :login';
                $params['login'] = $userId;
            }

            $payload = self::mapPayload($dados);
            $payload['tempo'] = isset($dados['tempo']) ? (int) $dados['tempo'] : 1;
            $payload['updated_date'] = (new DateTimeImmutable())->format('Y-m-d H:i:s');

            $setParts = [];
            foreach ($payload as $column => $value) {
                $setParts[] = $column . ' = :' . $column;
            }

            $sql = 'UPDATE planejamento SET ' . implode(', ', $setParts) . ' WHERE ' . implode(' AND ', $conditions);
            $stmt = $pdo->prepare($sql);
            $stmt->execute(array_merge($payload, $params));

            if ($stmt->rowCount() === 0) {
                $pdo->rollBack();
                return false;
            }

            self::syncLinhas($pdo, $id, $linhas, true);

            $pdo->commit();

            return true;
        } catch (PDOException $exception) {
            $pdo->rollBack();
            throw new RuntimeException('Falha ao atualizar planejamento: ' . $exception->getMessage(), 0, $exception);
        }
    }

    public static function delete(int $id, ?int $userId, bool $isAdmin): bool
    {
        $pdo = LegacyConfig::createPdo();
        $conditions = ['id = :id'];
        $params = ['id' => $id];

        if (! $isAdmin && $userId !== null) {
            $conditions[] = 'login = :login';
            $params['login'] = $userId;
        }

        $pdo->beginTransaction();

        try {
            $pdo->prepare('DELETE FROM planejamento_linhas WHERE planejamento = :id')->execute(['id' => $id]);

            $stmt = $pdo->prepare('DELETE FROM planejamento WHERE ' . implode(' AND ', $conditions));
            $stmt->execute($params);

            $pdo->commit();

            return $stmt->rowCount() > 0;
        } catch (PDOException $exception) {
            $pdo->rollBack();
            throw new RuntimeException('Falha ao excluir planejamento: ' . $exception->getMessage(), 0, $exception);
        }
    }

    /**
     * @return array<string,mixed>
     */
    private static function mapPayload(array $dados): array
    {
        return [
            'nome' => $dados['nome'] ?? null,
            'materia' => $dados['materia'] ?? null,
            'escola' => $dados['escola'] ?? null,
            'professor' => $dados['professor'] ?? null,
            'curso' => $dados['curso'] ?? null,
            'ano' => $dados['ano'] ?? null,
            'anosDoPlano' => $dados['anosDoPlano'] ?? null,
            'periodo' => $dados['periodo'] ?? null,
            'componenteCurricular' => $dados['componenteCurricular'] ?? null,
            'numeroDeAulas' => $dados['numeroDeAulas'] ?? null,
            'objetivoGeral' => $dados['objetivoGeral'] ?? null,
            'objetivoEspecifico' => $dados['objetivoEspecifico'] ?? null,
            'tipo' => $dados['tipo'] ?? null,
            'sequencial' => $dados['sequencial'] ?? null,
            'projetosIntegrador' => $dados['projetosIntegrador'] ?? null,
            'unidadeTematica' => $dados['unidadeTematica'] ?? null,
            'objetoDoConhecimento' => $dados['objetoDoConhecimento'] ?? null,
            'grupo' => $dados['grupo'] ?? null,
            'conteudos' => $dados['conteudos'] ?? null,
            'habilidades' => $dados['habilidades'] ?? null,
            'metodologias' => $dados['metodologias'] ?? null,
            'diagnostico' => $dados['diagnostico'] ?? null,
            'referencias' => $dados['referencias'] ?? null,
        ];
    }

    /**
     * @param array<int,array<string,mixed>> $linhas
     */
    private static function syncLinhas(PDO $pdo, int $planningId, array $linhas, bool $replaceExisting = false): void
    {
        $now = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $keptIds = [];

        foreach ($linhas as $linha) {
            if (! isset($linha['etapa']) || $linha['etapa'] === '') {
                continue;
            }

            $payload = [
                'planejamento' => $planningId,
                'etapa' => $linha['etapa'],
                'ano' => $linha['ano'] ?? null,
                'areaConhecimento' => $linha['areaConhecimento'] ?? null,
                'componenteCurricular' => $linha['componenteCurricular'] ?? null,
                'unidadeTematicas' => $linha['unidadeTematicas'] ?? null,
                'objetosConhecimento' => $linha['objetosConhecimento'] ?? null,
                'habilidades' => $linha['habilidades'] ?? null,
                'conteudos' => $linha['conteudos'] ?? null,
                'metodologias' => $linha['metodologias'] ?? null,
                'grupo' => $linha['grupo'] ?? null,
                'updated_date' => $now,
            ];

            if (! empty($linha['id']) && $replaceExisting) {
                $payload['id'] = (int) $linha['id'];
                $stmt = $pdo->prepare('UPDATE planejamento_linhas SET
                    etapa = :etapa,
                    ano = :ano,
                    areaConhecimento = :areaConhecimento,
                    componenteCurricular = :componenteCurricular,
                    unidadeTematicas = :unidadeTematicas,
                    objetosConhecimento = :objetosConhecimento,
                    habilidades = :habilidades,
                    conteudos = :conteudos,
                    metodologias = :metodologias,
                    grupo = :grupo,
                    updated_date = :updated_date
                WHERE id = :id AND planejamento = :planejamento');
                $stmt->execute($payload);
                $keptIds[] = $payload['id'];
            } else {
                $stmt = $pdo->prepare('INSERT INTO planejamento_linhas (
                    planejamento, etapa, ano, areaConhecimento, componenteCurricular,
                    unidadeTematicas, objetosConhecimento, habilidades, conteudos,
                    metodologias, grupo, created_date, updated_date
                ) VALUES (
                    :planejamento, :etapa, :ano, :areaConhecimento, :componenteCurricular,
                    :unidadeTematicas, :objetosConhecimento, :habilidades, :conteudos,
                    :metodologias, :grupo, :created_date, :updated_date
                )');
                $stmt->execute($payload + ['created_date' => $now]);
                $keptIds[] = (int) $pdo->lastInsertId();
            }
        }

        if ($replaceExisting) {
            if ($keptIds) {
                $placeholders = implode(',', array_fill(0, count($keptIds), '?'));
                $pdo->prepare('DELETE FROM planejamento_linhas WHERE planejamento = ? AND id NOT IN (' . $placeholders . ')')
                    ->execute(array_merge([$planningId], $keptIds));
            } else {
                $pdo->prepare('DELETE FROM planejamento_linhas WHERE planejamento = :id')->execute(['id' => $planningId]);
            }
        }
    }
}
