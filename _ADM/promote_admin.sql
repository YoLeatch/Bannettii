-- Script para promover usuário a Administrador
-- Execute este script no seu banco de dados MySQL

-- 1. Primeiro, insere o cargo "Administrador" se não existir
INSERT IGNORE INTO cargo (id, cargo, poder) VALUES (1, 'Administrador', 10);

-- 2. Busca o ID da pessoa pelo email e insere como funcionário
INSERT INTO funcionario (id, carteirinha, status)
SELECT id, CONCAT('ADM-', id), '1'
FROM pessoa 
WHERE email = 'suporte.contato@bennettii.com.br'
AND NOT EXISTS (
    SELECT 1 FROM funcionario f WHERE f.id = pessoa.id
);

-- 3. Associa o funcionário ao cargo de Administrador
INSERT INTO funcionario_cargo (funcionario_id, cargo_id, status)
SELECT p.id, 1, '1'
FROM pessoa p
WHERE p.email = 'suporte.contato@bennettii.com.br'
AND NOT EXISTS (
    SELECT 1 FROM funcionario_cargo fc WHERE fc.funcionario_id = p.id AND fc.cargo_id = 1
);

-- Verificar se foi promovido corretamente
SELECT 
    p.id,
    p.nome,
    p.email,
    c.cargo,
    c.poder
FROM pessoa p
JOIN funcionario f ON f.id = p.id
JOIN funcionario_cargo fc ON fc.funcionario_id = f.id
JOIN cargo c ON c.id = fc.cargo_id
WHERE p.email = 'suporte.contato@bennettii.com.br';
