/*

Ce fichier crée les utilisateurs :
- clients
- employés
- administrateurs

*/

USE cinephoria;

-- Insertion des users
INSERT INTO `user` (firstname, lastname, email, password, roles, created_at, updated_at, is_verified) VALUES 
(
  'john', 
  'doe', 
  'john@doe.com', 
  '$2y$13$yYs3m9jWXWpzkbbPYVNje.iXsqIwYDGiELQoA/dQS0v9N7FYtDlze ', -- changeMe
  '["ROLE_USER"]', 
  NOW(), 
  NOW(), 
  1
);

-- Insertion des admin/employés
INSERT INTO `user_staff` (firstname, lastname, email, password, roles, created_at, updated_at) VALUES
(
  'admin',
  'admin',
  'cinephoria@jeremysnnk.ovh',
  '$2y$13$rBt8GYi3zVdXfX0z3m.z/.KkvKDP0rxLFeh98HPOOvMs0PiyPJX0W', -- changeMe
  '["ROLE_ADMIN", "ROLE_STAFF"]',
  NOW(), 
  NOW()
),
(
  'jérémy',
  'sananikone',
  'jeremy.snnk@gmail.com',
  '$2y$13$6Rntp1AEsohaogP2oKLs4OOOrvDdunsM86.OFlnHgtpcfX8Re0/Bm', -- changeMe
  '["ROLE_STAFF"]',
  NOW(), 
  NOW()
);