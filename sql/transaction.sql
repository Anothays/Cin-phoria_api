START TRANSACTION;

-- Make reservation as paid
UPDATE reservation 
SET is_paid = TRUE 
WHERE id = :reservation_id;

SET @current_date = NOW();

-- Make CTE to insert tickets 
INSERT INTO ticket (reservation_id, category_id, unique_code, created_at, updated_at, is_scanned)
WITH RECURSIVE ticket_numbers AS (
    SELECT 
        1 AS ticket_number, 
        :reservation_id AS reservation_id,
        (SELECT id FROM ticket_category WHERE category_name = JSON_EXTRACT(:tickets, CONCAT('$[', 0, ']'))) AS category_id,
        UUID() AS unique_code, 
        @current_date AS created_at, 
        @current_date AS updated_at, 
        FALSE AS is_scanned
    UNION ALL
    SELECT 
        ticket_number + 1, 
        :reservation_id as reservation_id,
        (SELECT id FROM ticket_category WHERE category_name = JSON_EXTRACT(:tickets, CONCAT('$[', ticket_number, ']'))) AS category_id,
        UUID(), 
        @current_date, 
        @current_date, 
        FALSE
    FROM ticket_numbers
    WHERE ticket_number < JSON_LENGTH(:tickets)
)
SELECT 
    reservation_id,
    category_id,
    unique_code,
    created_at,
    updated_at,
    is_scanned
FROM ticket_numbers;

COMMIT;
