<?php

namespace App\Constant;

class ErrorMessages 
{
  const CURRENT_USER_IS_NOT_RESERVATION_OWNER = "Ce n'est pas votre réservation";
  const RESERVATION_IS_ALREADY_PAID = "Reservation déjà payée";
  const TICKETS_COUNT_DOES_NOT_MATCH_RESERVED_SEATS_COUNT = "Le nombre de billets ne correspond pas au nombre de siège réservés";
  const RESERVATION_TIMEOUT  = "Votre réservation a été supprimée car vous avez dépassé les 5 minutes";
  const TICKET_CATEGORY_NOT_FOUND = "Une categorie de billet n'existe pas";
  const PROJECTION_EVENT_NOT_AVAILABLE = "La séance n'est plus disponible";
}
