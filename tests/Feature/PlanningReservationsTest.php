<?php

namespace Tests\Feature;

use App\Auth\Profiles;
use App\Http\Controllers\Api\PlanningApiController;
use App\Http\JsonResponse;
use App\Http\Request;
use App\Models\Room;
use App\Models\RoomReservation;
use App\Services\RoomReservationService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class PlanningReservationsTest extends TestCase
{
    public function testListarSalasRetornaSalasEPoderAprovar(): void
    {
        $service = new FakeRoomReservationService();
        $controller = new PlanningApiController($service);

        $request = new Request(query: ['acao' => 'listar_salas']);
        $request->setUser([
            'id' => 42,
            'perfil' => Profiles::Administrator->value,
            'school' => ['id' => 5],
        ]);

        $response = $controller($request);
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->status());

        $payload = $response->data();
        $this->assertTrue($payload['sucesso']);
        $this->assertCount(1, $payload['salas']);
        $this->assertSame('Laboratório de Ciências', $payload['salas'][0]['nome']);
        $this->assertTrue($payload['pode_aprovar']);
        $this->assertSame(['schoolId' => 5], $service->lastContext);
    }

    public function testReservarSalaComPerfilAprovador(): void
    {
        $service = new FakeRoomReservationService();
        $controller = new PlanningApiController($service);

        $request = new Request(body: [
            'acao' => 'reservar_sala',
            'room_id' => 10,
            'planning_id' => 7,
            'inicio' => '2024-08-01T09:00',
            'fim' => '2024-08-01T10:00',
            'observacoes' => 'Apresentação de ciências',
        ]);
        $request->setUser([
            'id' => 99,
            'perfil' => Profiles::Administrator->value,
            'school_id' => 3,
        ]);

        $response = $controller($request);
        $this->assertSame(200, $response->status());
        $payload = $response->data();

        $this->assertTrue($payload['sucesso']);
        $this->assertTrue($payload['auto_aprovada']);
        $this->assertSame('Reserva confirmada com sucesso.', $payload['mensagem']);
        $this->assertSame(10, $service->createdReservation['room_id']);
        $this->assertSame('2024-08-01 09:00:00', $service->createdReservation['inicio']);
    }

    public function testReservarSalaSemPermissaoRetornaErro(): void
    {
        $service = new FakeRoomReservationService();
        $controller = new PlanningApiController($service);

        $request = new Request(body: [
            'acao' => 'reservar_sala',
            'room_id' => 10,
            'planning_id' => 7,
            'inicio' => '2024-08-01T09:00',
            'fim' => '2024-08-01T10:00',
        ]);
        $request->setUser([
            'id' => 200,
            'perfil' => Profiles::Student->value,
        ]);

        $response = $controller($request);
        $this->assertSame(403, $response->status());
        $this->assertFalse($response->data()['sucesso']);
    }

    public function testProfessorNaoPodeAprovarReserva(): void
    {
        $service = new FakeRoomReservationService();
        $controller = new PlanningApiController($service);

        $request = new Request(body: [
            'acao' => 'aprovar_reserva',
            'reserva_id' => 1,
            'decisao' => 'aprovar',
        ]);
        $request->setUser([
            'id' => 21,
            'perfil' => Profiles::Teacher->value,
        ]);

        $response = $controller($request);
        $this->assertSame(403, $response->status());
        $this->assertFalse($response->data()['sucesso']);
    }

    public function testAdministradorPodeAprovarReserva(): void
    {
        $service = new FakeRoomReservationService();
        $controller = new PlanningApiController($service);

        $request = new Request(body: [
            'acao' => 'aprovar_reserva',
            'reserva_id' => 1,
            'decisao' => 'aprovar',
        ]);
        $request->setUser([
            'id' => 2,
            'perfil' => Profiles::Administrator->value,
            'school_id' => 3,
        ]);

        $response = $controller($request);
        $this->assertSame(200, $response->status());

        $payload = $response->data();
        $this->assertTrue($payload['sucesso']);
        $this->assertSame('Reserva aprovada com sucesso.', $payload['mensagem']);
        $this->assertSame('approved', $payload['reserva']['status']);
        $this->assertSame(['status' => 'approved', 'comentario' => null, 'schoolId' => 3], $service->lastApproval);
    }

    public function testAutorPodeCancelarReserva(): void
    {
        $service = new FakeRoomReservationService();
        $controller = new PlanningApiController($service);

        $request = new Request(body: [
            'acao' => 'cancelar_reserva',
            'reserva_id' => 55,
        ]);
        $request->setUser([
            'id' => 99,
            'perfil' => Profiles::Teacher->value,
            'school_id' => 3,
        ]);

        $response = $controller($request);
        $this->assertSame(200, $response->status());
        $payload = $response->data();

        $this->assertTrue($payload['sucesso']);
        $this->assertSame('cancelled', $payload['reserva']['status']);
        $this->assertSame(['id' => 55, 'userId' => 99, 'schoolId' => 3], $service->lastCancelled);
    }
}

class FakeRoomReservationService extends RoomReservationService
{
    public array $lastContext = [];
    public array $createdReservation = [];
    public array $lastApproval = [];
    public array $lastCancelled = [];

    public function __construct()
    {
        // Intentionally bypass parent constructor.
    }

    public function getRoomsForSchool(?int $schoolId): array
    {
        $this->lastContext = ['schoolId' => $schoolId];

        return [
            new Room(1, $schoolId, 'Laboratório de Ciências', 32, 'Bloco B', 'Equipamentos multimídia'),
        ];
    }

    public function listReservations(array $filters = [], ?int $schoolId = null): array
    {
        return [
            new RoomReservation(
                1,
                1,
                7,
                99,
                null,
                RoomReservation::STATUS_PENDING,
                new DateTimeImmutable('2024-08-01 09:00:00'),
                new DateTimeImmutable('2024-08-01 10:00:00'),
                'Apresentação',
                null,
                null,
                null
            ),
        ];
    }

    public function createReservation(
        int $roomId,
        int $planningId,
        int $reservedBy,
        string $startsAt,
        string $endsAt,
        bool $autoApprove = false,
        ?string $notes = null,
        ?int $reservedFor = null,
        ?int $schoolId = null,
        bool $allowOverride = false
    ): RoomReservation {
        $this->createdReservation = [
            'room_id' => $roomId,
            'planning_id' => $planningId,
            'reserved_by' => $reservedBy,
            'inicio' => $startsAt,
            'fim' => $endsAt,
            'notes' => $notes,
            'schoolId' => $schoolId,
            'autoApprove' => $autoApprove,
        ];

        $status = $autoApprove ? RoomReservation::STATUS_APPROVED : RoomReservation::STATUS_PENDING;

        $reservation = new RoomReservation(
            25,
            $roomId,
            $planningId,
            $reservedBy,
            $reservedFor,
            $status,
            new DateTimeImmutable(str_replace('T', ' ', $startsAt) . ':00'),
            new DateTimeImmutable(str_replace('T', ' ', $endsAt) . ':00'),
            $notes,
            $autoApprove ? $reservedBy : null,
            $autoApprove ? new DateTimeImmutable(str_replace('T', ' ', $startsAt) . ':00') : null,
            null
        );
        $reservation->roomName = 'Laboratório de Ciências';
        $reservation->reservedByName = 'Administrador';

        return $reservation;
    }

    public function updateStatus(int $reservationId, string $status, int $approverId, ?string $comment = null, ?int $schoolId = null): RoomReservation
    {
        $this->lastApproval = [
            'status' => $status,
            'comentario' => $comment,
            'schoolId' => $schoolId,
        ];

        $reservation = new RoomReservation(
            $reservationId,
            1,
            7,
            99,
            null,
            $status,
            new DateTimeImmutable('2024-08-01 09:00:00'),
            new DateTimeImmutable('2024-08-01 10:00:00'),
            'Apresentação'
        );
        $reservation->reservedByName = 'Professor Paulo';
        $reservation->approvedByName = 'Ana Admin';

        return $reservation;
    }

    public function cancelReservation(int $reservationId, int $userId, ?int $schoolId = null): RoomReservation
    {
        $this->lastCancelled = [
            'id' => $reservationId,
            'userId' => $userId,
            'schoolId' => $schoolId,
        ];

        $reservation = new RoomReservation(
            $reservationId,
            1,
            7,
            $userId,
            null,
            RoomReservation::STATUS_CANCELLED,
            new DateTimeImmutable('2024-08-01 09:00:00'),
            new DateTimeImmutable('2024-08-01 10:00:00'),
            'Apresentação'
        );
        $reservation->reservedByName = 'Professor Paulo';

        return $reservation;
    }
}
