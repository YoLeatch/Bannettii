<?php
/**
 * BannerModel - Modelo para gerenciar banners via arquivo JSON
 * 
 * Este modelo NÃO usa banco de dados. Todos os dados são armazenados
 * e lidos de um arquivo JSON em storage/JSON/banners.json
 * 
 * @package App\Pages\Models
 */

namespace App\Pages\Models;

class BannerModel {
    // ============================
    // CONSTANTES
    // ============================
    
    /** @var string Caminho do arquivo JSON de banners */
    private const JSON_FILE = __DIR__ . '/../../../storage/JSON/banners.json';
    
    // ============================
    // PROPRIEDADES DO MODELO
    // ============================
    
    /** @var int ID único do banner */
    protected int $id;
    
    /** @var string Título do banner */
    protected string $titulo;
    
    /** @var string|null Descrição/subtítulo do banner */
    protected ?string $descricao;
    
    /** @var string Caminho da imagem do banner */
    protected string $imagem;
    
    /** @var string|null URL de destino ao clicar no banner */
    protected ?string $link;
    
    /** @var int Ordem de exibição do banner */
    protected int $ordem;
    
    /** @var bool Se o banner está ativo */
    protected bool $ativo;
    
    /** @var string Data de criação */
    protected string $criacao;
    
    /** @var string|null Data de início da exibição */
    protected ?string $dataInicio;
    
    /** @var string|null Data de fim da exibição */
    protected ?string $dataFim;

    // ============================
    // CONSTRUTOR
    // ============================
    
    /**
     * Construtor do modelo Banner
     */
    public function __construct(array $data) {
        $this->id = $data['id'];
        $this->titulo = $data['titulo'];
        $this->descricao = $data['descricao'] ?? null;
        $this->imagem = $data['imagem'];
        $this->link = $data['link'] ?? null;
        $this->ordem = $data['ordem'] ?? 0;
        $this->ativo = $data['ativo'] ?? true;
        $this->criacao = $data['criacao'] ?? date('Y-m-d H:i:s');
        $this->dataInicio = $data['dataInicio'] ?? null;
        $this->dataFim = $data['dataFim'] ?? null;
    }

    // ============================
    // GETTERS
    // ============================
    
    public function getId(): int { return $this->id; }
    public function getTitulo(): string { return $this->titulo; }
    public function getDescricao(): ?string { return $this->descricao; }
    public function getImagem(): string { return $this->imagem; }
    public function getLink(): ?string { return $this->link; }
    public function getOrdem(): int { return $this->ordem; }
    public function isAtivo(): bool { return $this->ativo; }
    public function getCriacao(): string { return $this->criacao; }
    public function getDataInicio(): ?string { return $this->dataInicio; }
    public function getDataFim(): ?string { return $this->dataFim; }

    /**
     * Verifica se o banner está dentro do período de exibição
     */
    public function isVisible(): bool {
        if (!$this->ativo) return false;
        
        $hoje = date('Y-m-d');
        if ($this->dataInicio && $hoje < $this->dataInicio) return false;
        if ($this->dataFim && $hoje > $this->dataFim) return false;
        
        return true;
    }

    /**
     * Converte o objeto para array
     */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'imagem' => $this->imagem,
            'link' => $this->link,
            'ordem' => $this->ordem,
            'ativo' => $this->ativo,
            'criacao' => $this->criacao,
            'dataInicio' => $this->dataInicio,
            'dataFim' => $this->dataFim
        ];
    }

    // ============================
    // MÉTODOS DE ARQUIVO JSON
    // ============================
    
    /**
     * Lê todos os banners do arquivo JSON
     * 
     * @return array Lista de arrays com dados dos banners
     */
    private static function readJsonFile(): array {
        // Garante que o diretório existe
        $dir = dirname(self::JSON_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Se o arquivo não existe, cria com array vazio
        if (!file_exists(self::JSON_FILE)) {
            file_put_contents(self::JSON_FILE, json_encode([], JSON_PRETTY_PRINT));
            return [];
        }
        
        $content = file_get_contents(self::JSON_FILE);
        return json_decode($content, true) ?: [];
    }

    /**
     * Salva os banners no arquivo JSON
     * 
     * @param array $banners Lista de arrays com dados dos banners
     * @return bool Retorna true se salvou com sucesso
     */
    private static function writeJsonFile(array $banners): bool {
        $dir = dirname(self::JSON_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $json = json_encode($banners, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents(self::JSON_FILE, $json) !== false;
    }

    /**
     * Gera um novo ID único
     * 
     * @return int Novo ID
     */
    private static function generateId(): int {
        $banners = self::readJsonFile();
        if (empty($banners)) return 1;
        
        $maxId = max(array_column($banners, 'id'));
        return $maxId + 1;
    }

    // ============================
    // MÉTODOS DE BUSCA
    // ============================
    
    /**
     * Busca um banner por ID
     * 
     * @param int $id ID do banner
     * @return BannerModel|null
     */
    public static function findById(int $id): ?BannerModel {
        $banners = self::readJsonFile();
        
        foreach ($banners as $bannerData) {
            if ($bannerData['id'] === $id) {
                return new BannerModel($bannerData);
            }
        }
        
        return null;
    }

    /**
     * Retorna todos os banners
     * 
     * @return array Lista de BannerModel
     */
    public static function findAll(): array {
        $banners = self::readJsonFile();
        $result = [];
        
        // Ordena por ordem crescente
        usort($banners, fn($a, $b) => ($a['ordem'] ?? 0) - ($b['ordem'] ?? 0));
        
        foreach ($banners as $bannerData) {
            $result[] = new BannerModel($bannerData);
        }
        
        return $result;
    }

    /**
     * Retorna apenas banners ativos e visíveis
     * 
     * @return array Lista de BannerModel visíveis
     */
    public static function findAllVisible(): array {
        $allBanners = self::findAll();
        
        return array_filter($allBanners, fn($banner) => $banner->isVisible());
    }

    /**
     * Conta total de banners
     * 
     * @return int Total de banners
     */
    public static function count(): int {
        return count(self::readJsonFile());
    }

    // ============================
    // MÉTODOS DE CRIAÇÃO
    // ============================
    
    /**
     * Cria um novo banner
     * 
     * @param array $data Dados do banner (titulo, descricao, imagem, link, ordem, ativo, dataInicio, dataFim)
     * @return BannerModel|null Banner criado ou null em caso de erro
     */
    public static function create(array $data): ?BannerModel {
        $banners = self::readJsonFile();
        
        // Gera ID único
        $data['id'] = self::generateId();
        $data['criacao'] = date('Y-m-d H:i:s');
        $data['ativo'] = $data['ativo'] ?? true;
        $data['ordem'] = $data['ordem'] ?? count($banners);
        
        // Adiciona ao array
        $banners[] = $data;
        
        // Salva no arquivo
        if (self::writeJsonFile($banners)) {
            return new BannerModel($data);
        }
        
        return null;
    }

    // ============================
    // MÉTODOS DE ATUALIZAÇÃO
    // ============================
    
    /**
     * Atualiza o banner atual
     * 
     * @param array $data Dados a atualizar
     * @return bool Retorna true se atualizado com sucesso
     */
    public function update(array $data): bool {
        $banners = self::readJsonFile();
        
        foreach ($banners as $index => $bannerData) {
            if ($bannerData['id'] === $this->id) {
                // Atualiza os campos fornecidos
                $banners[$index] = array_merge($bannerData, $data);
                $banners[$index]['id'] = $this->id; // Garante que o ID não mude
                
                if (self::writeJsonFile($banners)) {
                    // Atualiza propriedades locais
                    if (isset($data['titulo'])) $this->titulo = $data['titulo'];
                    if (isset($data['descricao'])) $this->descricao = $data['descricao'];
                    if (isset($data['imagem'])) $this->imagem = $data['imagem'];
                    if (isset($data['link'])) $this->link = $data['link'];
                    if (isset($data['ordem'])) $this->ordem = $data['ordem'];
                    if (isset($data['ativo'])) $this->ativo = $data['ativo'];
                    if (isset($data['dataInicio'])) $this->dataInicio = $data['dataInicio'];
                    if (isset($data['dataFim'])) $this->dataFim = $data['dataFim'];
                    return true;
                }
                break;
            }
        }
        
        return false;
    }

    /**
     * Ativa o banner
     */
    public function activate(): bool {
        return $this->update(['ativo' => true]);
    }

    /**
     * Desativa o banner
     */
    public function deactivate(): bool {
        return $this->update(['ativo' => false]);
    }

    /**
     * Atualiza a ordem do banner
     */
    public function setOrdem(int $ordem): bool {
        return $this->update(['ordem' => $ordem]);
    }

    /**
     * Reordena todos os banners
     * 
     * @param array $idsOrdenados Array de IDs na nova ordem
     * @return bool Retorna true se reordenado com sucesso
     */
    public static function reorder(array $idsOrdenados): bool {
        $banners = self::readJsonFile();
        
        foreach ($banners as $index => $bannerData) {
            $novaOrdem = array_search($bannerData['id'], $idsOrdenados);
            if ($novaOrdem !== false) {
                $banners[$index]['ordem'] = $novaOrdem;
            }
        }
        
        return self::writeJsonFile($banners);
    }

    // ============================
    // MÉTODOS DE EXCLUSÃO
    // ============================
    
    /**
     * Remove o banner atual
     * 
     * @return bool Retorna true se removido com sucesso
     */
    public function delete(): bool {
        return self::deleteById($this->id);
    }

    /**
     * Remove um banner por ID
     * 
     * @param int $id ID do banner
     * @return bool Retorna true se removido com sucesso
     */
    public static function deleteById(int $id): bool {
        $banners = self::readJsonFile();
        
        $banners = array_filter($banners, fn($b) => $b['id'] !== $id);
        $banners = array_values($banners); // Reindexa o array
        
        return self::writeJsonFile($banners);
    }

    /**
     * Remove todos os banners
     * 
     * @return bool Retorna true se limpo com sucesso
     */
    public static function deleteAll(): bool {
        return self::writeJsonFile([]);
    }
}
