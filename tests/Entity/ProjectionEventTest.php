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
    $this->projectionEvent = new ProjectionEvent();
    $this->projectionRoom = new ProjectionRoom();
    $this->reservation = new Reservation();

    for ($i=0; $i < 10; $i++) { 
      $seat = (new ProjectionRoomSeat())
        ->setSeatNumber($i + 1)
        ->setSeatRow('A');
      $this->projectionRoom->addProjectionRoomSeat($seat);
    }
    $this->projectionEvent->setProjectionRoom($this->projectionRoom);
    $this->projectionEvent->addReservation($this->reservation);
    $this->reservation
      ->addSeat($this->projectionEvent->getProjectionRoom()->getProjectionRoomSeats()[0])
      ->addSeat($this->projectionEvent->getProjectionRoom()->getProjectionRoomSeats()[1])
      ->addSeat($this->projectionEvent->getProjectionRoom()->getProjectionRoomSeats()[2])
      ->addSeat($this->projectionEvent->getProjectionRoom()->getProjectionRoomSeats()[3])
      ->addSeat($this->projectionEvent->getProjectionRoom()->getProjectionRoomSeats()[4]);
  }

  public function testGetAvailableSeatsCount()
  {
    $this->assertEquals(5, $this->projectionEvent->getAvailableSeatsCount());
  }

  public function testGetAllSeats()
  {
    $this->assertEquals(count($this->projectionEvent->getAllSeats()), count($this->projectionEvent->getProjectionRoom()->getProjectionRoomSeats()));
  }
}
