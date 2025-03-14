<?php

namespace App\Tests\Entity;

use App\Entity\ProjectionEvent;
use App\Entity\ProjectionRoom;
use App\Entity\ProjectionRoomSeat;
use App\Entity\Reservation;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProjectionEventTest extends KernelTestCase
{
    private $projectionEvent;
    private $projectionRoom;
    private $reservation;

    public function setUp(): void
    {
        parent::setUp();
        
        // Create base objects
        $this->projectionEvent = new ProjectionEvent();
        $this->projectionRoom = new ProjectionRoom();
        $this->reservation = new Reservation();

        // Create 10 seats in the room
        for ($i = 0; $i < 10; $i++) {
            $seat = (new ProjectionRoomSeat())
                ->setSeatNumber($i + 1)
                ->setSeatRow('A');
            $this->projectionRoom->addProjectionRoomSeat($seat);
        }

        // Configure the projection event
        $this->projectionEvent
            ->setProjectionRoom($this->projectionRoom)
            ->setBeginAt(new \DateTime('tomorrow', new \DateTimeZone('Europe/Paris')));

        // Add a reservation with 5 seats
        $this->projectionEvent->addReservation($this->reservation);
        $this->reservation
            ->addSeat($this->projectionRoom->getProjectionRoomSeats()[0])
            ->addSeat($this->projectionRoom->getProjectionRoomSeats()[1])
            ->addSeat($this->projectionRoom->getProjectionRoomSeats()[2])
            ->addSeat($this->projectionRoom->getProjectionRoomSeats()[3])
            ->addSeat($this->projectionRoom->getProjectionRoomSeats()[4]);
    }

    public function testGetAvailableSeatsCount(): void
    {
        // Check that the number of available seats is correct (10 seats - 5 reserved)
        $this->assertEquals(
            5,
            $this->projectionEvent->getAvailableSeatsCount(),
            'Available seats count should be 5'
        );
    }

    public function testGetAllSeats(): void
    {
        // Check that all seats are properly retrieved
        $this->assertEquals(
            count($this->projectionEvent->getAllSeats()),
            count($this->projectionRoom->getProjectionRoomSeats()),
            'Total seats count should match the number of seats in the room'
        );
    }

    public function testAddAndRemoveReservation(): void
    {
        $newReservation = new Reservation();
        
        // Test adding a reservation
        $this->projectionEvent->addReservation($newReservation);
        $this->assertCount(
            2,
            $this->projectionEvent->getReservations(),
            'Projection event should have 2 reservations'
        );

        // Test removing a reservation
        $this->projectionEvent->removeReservation($newReservation);
        $this->assertCount(
            1,
            $this->projectionEvent->getReservations(),
            'Projection event should have 1 reservation after removal'
        );
    }

    public function testSetAndGetBeginAt(): void
    {
        $date = new \DateTime('2024-03-20 15:00:00', new \DateTimeZone('Europe/Paris'));
        
        // Test setting begin date
        $this->projectionEvent->setBeginAt($date);
        $this->assertEquals(
            $date,
            $this->projectionEvent->getBeginAt(),
            'Begin date should match the set date'
        );
    }

    public function testSetAndGetProjectionRoom(): void
    {
        $newRoom = new ProjectionRoom();
        
        // Test setting projection room
        $this->projectionEvent->setProjectionRoom($newRoom);
        $this->assertSame(
            $newRoom,
            $this->projectionEvent->getProjectionRoom(),
            'Projection room should be the newly set room'
        );
    }

    public function testGetReservedSeats(): void
    {
        // Check that reserved seats are correctly retrieved
        $reservedSeats = $this->projectionEvent->getReservedSeats();

        $this->assertCount(
            5,
            $reservedSeats,
            'Reserved seats count should be 5'
        );

    }
}
