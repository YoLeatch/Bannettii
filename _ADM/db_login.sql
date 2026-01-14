-- 1. Usuário READ_ONLY (Apenas Leitura)
DROP USER IF EXISTS 'read_only'@'localhost';
CREATE USER 'read_only'@'localhost' IDENTIFIED BY '95kw2hT{UiJ[+d[9';
GRANT SELECT ON bennettii.* TO 'read_only'@'localhost';
FLUSH PRIVILEGES;

-- 2. Usuário DEFAULT (Leitura e Escrita/Admin)
DROP USER IF EXISTS 'default'@'localhost';
CREATE USER 'default'@'localhost' IDENTIFIED BY 'u*!v2aSN#;^9sNR_';
GRANT ALL PRIVILEGES ON bennettii.* TO 'default'@'localhost';
FLUSH PRIVILEGES;