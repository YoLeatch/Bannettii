# Bennettii - E-commerce

## 🚀 Deploy na Hostinger

### Estrutura de Pastas na Hostinger
```
public_html/
├── .htaccess (raiz)
├── public/ (conteúdo da pasta Public do projeto)
│   ├── index.php
│   ├── css/
│   └── assets/
├── App/
├── Core/
├── Config/
├── Resources/
├── storage/
├── autoload.php
└── .env
```

### Passos para Deploy

1. **Criar Banco de Dados no hPanel**
   - Acesse MySQL Databases
   - Crie um novo banco
   - Anote: nome do banco, usuário e senha

2. **Importar SQL**
   - Acesse phpMyAdmin
   - Importe: `_ADM/bennettii.sql`
   - Execute: `_ADM/promote_admin.sql` (se necessário)

3. **Upload dos Arquivos**
   - Via FTP ou File Manager
   - Faça upload de TODOS os arquivos para `public_html/`
   - Mantenha a estrutura de pastas

4. **Configurar .env**
   - Copie `.env.example` para `.env`
   - Configure as credenciais do banco
   - Gere JWT_SECRET aleatório

5. **Configurar Permissões**
   ```bash
   storage/ - 755
   storage/logs/ - 755
   Public/uploads/ - 755
   Public/uploads/avatars/ - 755
   Public/assets/uploads/products/ - 755
   ```

6. **Teste**
   - Acesse: https://seudominio.com
   - Teste login/registro
   - Verifique painel admin

### Credenciais Padrão Admin
Após executar `promote_admin.sql`:
- Email: admin@bennettii.com
- Senha: definida no SQL

## 📞 Problemas Comuns

### Erro 500
- Verifique permissões das pastas
- Confira configuração do .env
- Ative error_log no PHP

### CSS/JS não carregam
- Verifique caminhos no .htaccess
- Confirme estrutura de pastas
- Limpe cache do navegador

### Erro de Conexão BD
- Confirme credenciais no .env
- Use 'localhost' como DB_HOST
- Verifique usuário tem permissões

## 🔒 Segurança

- [ ] Altere JWT_SECRET
- [ ] Use senha forte no banco
- [ ] Ative HTTPS
- [ ] Configure CORS
- [ ] Implemente rate limiting

## 📝 Notas

- PHP mínimo: 7.4
- MySQL mínimo: 5.7
- Mod_rewrite: Habilitado
