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
  '$2y$13$DSuZWjFPp11OOI50YSLMMO40wWGqkgB1rsHud76qaM7TPE3fHXhba', -- johndoe
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
  '$2y$13$OsofQ3O63MnNRF6V6FFC0em2NIVlvqQCqzX07HRdJYozuTjJI282K', -- admin
  '["ROLE_ADMIN", "ROLE_STAFF"]',
  NOW(), 
  NOW()
),
(
  'jérémy',
  'sananikone',
  'jeremy.snnk@gmail.com',
  '$2y$13$R2Qpl04fiYSXVuUAfKcl1.KCPYit7dTFiLnScJMq6ltRMS8MkAZry', -- staff
  '["ROLE_STAFF"]',
  NOW(), 
  NOW()
);