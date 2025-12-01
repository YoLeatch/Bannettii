-- 2. Criar o usuário (substitua 'sua_senha_app' por uma senha segura)
CREATE USER IF NOT EXISTS 'read_only'@'localhost' IDENTIFIED BY '95kw2hT{UiJ[+d[9';

-- 3. Dar permissões ao usuário
GRANT SELECT ON bennettii.* TO 'read_only'@'localhost';

-- 4. Aplicar as permissões 
FLUSH PRIVILEGES;

CREATE USER IF NOT EXISTS 'default'@'localhost' IDENTIFIED BY 'u*!v2aSN#;^9sNR_';

-- 3. Dar permissões ao usuário
GRANT ALL PRIVILEGES ON bennettii.* TO 'default'@'localhost';

-- 4. Aplicar as permissões
FLUSH PRIVILEGES;