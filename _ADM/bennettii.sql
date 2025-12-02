SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `bennettii`
--
CREATE DATABASE IF NOT EXISTS `bennettii` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `bennettii`;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cargo`
--

DROP TABLE IF EXISTS `cargo`;
CREATE TABLE `cargo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cargo` varchar(256) NOT NULL,
  `poder` int(11) NOT NULL,
  primary key (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cartao_credito`
--

DROP TABLE IF EXISTS `cartao_credito`;
CREATE TABLE `cartao_credito` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(40) NOT NULL,
  `validade` date NOT NULL,
  `usuario` int(11) NOT NULL,
  `status` varchar(1) NOT NULL,
    primary key (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `cartao_credito`
  ADD KEY `fk_Cartao_Cliente` (`Usuario`);
-- --------------------------------------------------------

--
-- Estrutura para tabela `cupom`
--

DROP TABLE IF EXISTS `cupom`;
CREATE TABLE `cupom` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cod` varchar(100) NOT NULL,
  `criacao` date NOT NULL,
  `validade` date NOT NULL,
  `status` varchar(1) NOT NULL,
    primary key (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `categoria`
--

DROP TABLE IF EXISTS `categoria`;
CREATE TABLE `categoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria` varchar(45) NOT NULL,
    primary key (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

  
-- --------------------------------------------------------

--
-- Estrutura para tabela `sub_categoria`
--

DROP TABLE IF EXISTS `sub_categoria`;
CREATE TABLE `sub_categoria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoria` int(11) NOT NULL,
  `sub_categoria` varchar(45) NOT NULL,
    primary key (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `sub_categoria`
  ADD KEY `fk_SubCat_Categoria` (`categoria`);
-- --------------------------------------------------------

--
-- Estrutura para tabela `cupom_sub_categoria`
--

DROP TABLE IF EXISTS `cupom_sub_categoria`;
CREATE TABLE `cupom_sub_categoria` (
  `sub_categoria_id` int(11) NOT NULL,
  `cupom_id` int(11) NOT NULL,
    primary key (`sub_categoria_id`, `cupom_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

  
-- --------------------------------------------------------

--
-- Estrutura para tabela `estado`
--

DROP TABLE IF EXISTS `estado`;
CREATE TABLE `estado` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estado` varchar(256) NOT NULL,
    primary key (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cidade`
--

DROP TABLE IF EXISTS `cidade`;
CREATE TABLE `cidade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cidade` varchar(256) NOT NULL,
  `estado` int(11) NOT NULL,
    primary key (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `cidade`
  ADD KEY `fk_Cidade_Estado` (`estado`);
-- --------------------------------------------------------

--
-- Estrutura para tabela `cliente`
--

DROP TABLE IF EXISTS `cliente`;
CREATE TABLE `cliente` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idade` int(11) DEFAULT NULL,
  `status` varchar(1) NOT NULL,
    primary key (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `endereco`
--

DROP TABLE IF EXISTS `endereco`;
CREATE TABLE `endereco` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `logradouro` varchar(60) NOT NULL,
  `pessoa` int(11) NOT NULL,
  `cidade` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `status` varchar(1) NOT NULL,
  `cep` varchar(8) NOT NULL,
    primary key (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------



--
-- Estrutura para tabela `funcionario`
--

DROP TABLE IF EXISTS `funcionario`;
CREATE TABLE `funcionario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `carteirinha` varchar(45) NOT NULL,
  `status` varchar(1) NOT NULL,
    primary key (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `funcionario_cargo`
--

DROP TABLE IF EXISTS `funcionario_cargo`;
CREATE TABLE `funcionario_cargo` (
  `funcionario_id` int(11) NOT NULL,
  `cargo_id` int(11) NOT NULL,
  `status` varchar(1) NOT NULL,
    primary key (`funcionario_id`, `cargo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `imagem`
--

DROP TABLE IF EXISTS `imagem`;
CREATE TABLE `imagem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `imagem` varchar(45) NOT NULL,
  `produto` int(11) NOT NULL,
    primary key (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `imagem`
  ADD KEY `fk_Imagem_Produto` (`produto`);
-- --------------------------------------------------------

--
-- Estrutura para tabela `pessoa`
--

DROP TABLE IF EXISTS `pessoa`;
CREATE TABLE `pessoa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(45) NOT NULL,
  `login` varchar(200) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `CPF` varchar(13) NOT NULL UNIQUE,
  `email` varchar(256) NOT NULL UNIQUE,
  `dt_criacao` datetime NOT NULL,
  `status` varchar(1) NOT NULL,
  `uid` int(11) NOT NULL,
    primary key (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `produto`
--

DROP TABLE IF EXISTS `produto`;
CREATE TABLE `produto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(60) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `cod` varchar(60) NOT NULL,
  `categoria` int(11) NOT NULL,
  `sub_categoria` int(11) NOT NULL,
  `pesoliq` varchar(45) DEFAULT NULL,
  `pesototal` varchar(45) DEFAULT NULL,
  `dimensoes` varchar(45) DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `data` datetime NOT NULL,
  `status` varchar(1) NOT NULL,
  `desconto` int(11) DEFAULT NULL,
  `estoque` int(11) DEFAULT NULL,
    primary key (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `produto`
  ADD KEY `fk_Produto_SubCategoria` (`sub_categoria`),
  ADD KEY `fk_Produto_Categoria` (`categoria`);
-- --------------------------------------------------------

--
-- Estrutura para tabela `produto_vendas`
--

DROP TABLE IF EXISTS `produto_vendas`;
CREATE TABLE `produto_vendas` (
  `produto_id` int(11) NOT NULL,
  `vendas_id` int(11) NOT NULL,
  `valor` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
    primary key (`produto_id`, `vendas_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `produto_vendas`
  ADD KEY `fk_ProdVenda_Venda` (`vendas_id`);
-- --------------------------------------------------------

--
-- Estrutura para tabela `telefone`
--

DROP TABLE IF EXISTS `telefone`;
CREATE TABLE `telefone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `telefone` int(11) NOT NULL,
  `pessoa` int(11) NOT NULL,
  `status` varchar(1) NOT NULL,
    primary key (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `telefone`
  ADD KEY `fk_Telefone_Pessoa` (`pessoa`);
-- --------------------------------------------------------

--
-- Estrutura para tabela `tipo`
--

DROP TABLE IF EXISTS `tipo`;
CREATE TABLE `tipo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo` varchar(256) NOT NULL,
  `endereco` int(11) NOT NULL,
    primary key (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `tipo`
  ADD KEY `fk_Tipo_Endereco` (`endereco`);
-- --------------------------------------------------------

--
-- Estrutura para tabela `venda`
--

DROP TABLE IF EXISTS `venda`;
CREATE TABLE `venda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valor_total` int(11) NOT NULL,
  `cliente` int(11) NOT NULL,
  `endereco` int(11) NOT NULL,
  `cod` varchar(45) NOT NULL,
    primary key (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `venda`
  ADD KEY `fk_Venda_Cliente` (`cliente`),
  ADD KEY `fk_Venda_Endereco` (`endereco`);

-- Restrições para tabelas desejadas
--

--
-- Restrições para tabelas `cartao_credito`
--
ALTER TABLE `cartao_credito`
  ADD CONSTRAINT `fk_Cartao_Cliente` FOREIGN KEY (`Usuario`) REFERENCES `cliente` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `cidade`
--
ALTER TABLE `cidade`
  ADD CONSTRAINT `fk_Cidade_Estado` FOREIGN KEY (`estado`) REFERENCES `estado` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `fk_Cliente_Pessoa` FOREIGN KEY (`id`) REFERENCES `pessoa` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;



--
-- Restrições para tabelas `endereco`
--
ALTER TABLE `endereco`
  ADD CONSTRAINT `fk_Endereco_Cidade` FOREIGN KEY (`cidade`) REFERENCES `cidade` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Endereco_Pessoa` FOREIGN KEY (`pessoa`) REFERENCES `pessoa` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `funcionario`
--
ALTER TABLE `funcionario`
  ADD CONSTRAINT `fk_Funcionario_Pessoa` FOREIGN KEY (`id`) REFERENCES `pessoa` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `funcionario_cargo`
--
ALTER TABLE `funcionario_cargo`
  ADD CONSTRAINT `fk_Func_Cargo_Cargo` FOREIGN KEY (`cargo_id`) REFERENCES `cargo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Func_Cargo_Funcionario` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `imagem`
--
ALTER TABLE `imagem`
  ADD CONSTRAINT `fk_Imagem_Produto` FOREIGN KEY (`produto`) REFERENCES `produto` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `produto`
--
ALTER TABLE `produto`
  ADD CONSTRAINT `fk_Produto_SubCategoria` FOREIGN KEY (`sub_categoria`) REFERENCES `sub_categoria` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Produto_Categoria` FOREIGN KEY (`categoria`) REFERENCES `categoria` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `produto_vendas`
--
ALTER TABLE `produto_vendas`
  ADD CONSTRAINT `fk_ProdVenda_Produto` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_ProdVenda_Venda` FOREIGN KEY (`vendas_id`) REFERENCES `venda` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `cupom_sub_categoria`
--
ALTER TABLE `cupom_sub_categoria`
  ADD CONSTRAINT `fk_CupSubCat_SubCat` FOREIGN KEY (`sub_categoria_id`) REFERENCES `sub_categoria` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_CupSubCat_Cupom` FOREIGN KEY (`cupom_id`) REFERENCES `cupom` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `sub_categoria`
--
ALTER TABLE `sub_categoria`
  ADD CONSTRAINT `fk_SubCat_Categoria` FOREIGN KEY (`categoria`) REFERENCES `categoria` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `telefone`
--
ALTER TABLE `telefone`
  ADD CONSTRAINT `fk_Telefone_Pessoa` FOREIGN KEY (`pessoa`) REFERENCES `pessoa` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `tipo`
--
ALTER TABLE `tipo`
  ADD CONSTRAINT `fk_Tipo_Endereco` FOREIGN KEY (`endereco`) REFERENCES `endereco` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Restrições para tabelas `venda`
--
ALTER TABLE `venda`
  ADD CONSTRAINT `fk_Venda_Cliente` FOREIGN KEY (`cliente`) REFERENCES `cliente` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Venda_Endereco` FOREIGN KEY (`endereco`) REFERENCES `endereco` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

-- 2. Criar o usuário (substitua 'sua_senha_app' por uma senha segura)
CREATE USER 'read_only'@'localhost' IDENTIFIED BY '95kw2hT{UiJ[+d[9';

-- 3. Dar permissões ao usuário
GRANT SELECT ON bennettii.* TO 'read_only'@'localhost';

-- 4. Aplicar as permissões 
FLUSH PRIVILEGES;

CREATE USER 'default'@'localhost' IDENTIFIED BY 'u*!v2aSN#;^9sNR_';

-- 3. Dar permissões ao usuário
GRANT ALL PRIVILEGES ON bennettii.* TO 'default'@'localhost';

-- 4. Aplicar as permissões
FLUSH PRIVILEGES;