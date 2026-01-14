<?php
/**
 * SessionModel - Modelo para gerenciar seções de página via arquivo JSON
 * 
 * Este modelo NÃO usa banco de dados. Todos os dados são armazenados
 * e lidos de um arquivo JSON em storage/JSON/sessions.json
 * 
 * Seções representam blocos de conteúdo configuráveis da página,
 * como: hero, promoções, destaques, newsletter, etc.
 * 
 * @package App\Pages\Models
 */

namespace App\Pages\Models;

class SessionModel {
    // ============================
    // CONSTANTES
    // ============================
    
    /** @var string Caminho do arquivo JSON de seções */
    private const JSON_FILE = __DIR__ . '/../../../storage/JSON/sessions.json';
    
    // Tipos de seção disponíveis
    public const TYPE_HERO = 'hero';
    public const TYPE_BANNER = 'banner';
    public const TYPE_PRODUCTS = 'products';
    public const TYPE_CATEGORIES = 'categories';
    public const TYPE_PROMO = 'promo';
    public const TYPE_NEWSLETTER = 'newsletter';
    public const TYPE_TESTIMONIALS = 'testimonials';
    public const TYPE_CUSTOM = 'custom';
    
    // ============================
    // PROPRIEDADES DO MODELO
    // ============================
    
    /** @var int ID único da seção */
    protected int $id;
    
    /** @var string Identificador único da seção (slug) */
    protected string $slug;
    
    /** @var string Tipo da seção */
    protected string $tipo;
    
    /** @var string Título da seção */
    protected string $titulo;
    
    /** @var string|null Subtítulo da seção */
    protected ?string $subtitulo;
    
    /** @var array Conteúdo/configurações da seção */
    protected array $conteudo;
    
    /** @var int Ordem de exibição */
    protected int $ordem;
    
    /** @var bool Se a seção está ativa */
    protected bool $ativo;
    
    /** @var string|null Página onde a seção aparece */
    protected ?string $pagina;
    
    /** @var string Data de criação */
    protected string $criacao;
    
    /** @var string Data da última atualização */
    protected string $atualizacao;

    // ============================
    // CONSTRUTOR
    // ============================
    
    /**
     * Construtor do modelo Session
     */
    public function __construct(array $data) {
        $this->id = $data['id'];
        $this->slug = $data['slug'];
        $this->tipo = $data['tipo'];
        $this->titulo = $data['titulo'];
        $this->subtitulo = $data['subtitulo'] ?? null;
        $this->conteudo = $data['conteudo'] ?? [];
        $this->ordem = $data['ordem'] ?? 0;
        $this->ativo = $data['ativo'] ?? true;
        $this->pagina = $data['pagina'] ?? null;
        $this->criacao = $data['criacao'] ?? date('Y-m-d H:i:s');
        $this->atualizacao = $data['atualizacao'] ?? date('Y-m-d H:i:s');
    }

    // ============================
    // GETTERS
    // ============================
    
    public function getId(): int { return $this->id; }
    public function getSlug(): string { return $this->slug; }
    public function getTipo(): string { return $this->tipo; }
    public function getTitulo(): string { return $this->titulo; }
    public function getSubtitulo(): ?string { return $this->subtitulo; }
    public function getConteudo(): array { return $this->conteudo; }
    public function getOrdem(): int { return $this->ordem; }
    public function isAtivo(): bool { return $this->ativo; }
    public function getPagina(): ?string { return $this->pagina; }
    public function getCriacao(): string { return $this->criacao; }
    public function getAtualizacao(): string { return $this->atualizacao; }

    /**
     * Retorna um valor específico do conteúdo
     */
    public function getConteudoValue(string $key, $default = null) {
        return $this->conteudo[$key] ?? $default;
    }

    /**
     * Converte o objeto para array
     */
    public function toArray(): array {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'tipo' => $this->tipo,
            'titulo' => $this->titulo,
            'subtitulo' => $this->subtitulo,
            'conteudo' => $this->conteudo,
            'ordem' => $this->ordem,
            'ativo' => $this->ativo,
            'pagina' => $this->pagina,
            'criacao' => $this->criacao,
            'atualizacao' => $this->atualizacao
        ];
    }

    /**
     * Retorna tipos de seção disponíveis
     */
    public static function getAvailableTypes(): array {
        return [
            self::TYPE_HERO => 'Hero/Banner Principal',
            self::TYPE_BANNER => 'Banner Promocional',
            self::TYPE_PRODUCTS => 'Grid de Produtos',
            self::TYPE_CATEGORIES => 'Categorias',
            self::TYPE_PROMO => 'Promoção',
            self::TYPE_NEWSLETTER => 'Newsletter',
            self::TYPE_TESTIMONIALS => 'Depoimentos',
            self::TYPE_CUSTOM => 'Personalizado'
        ];
    }

    // ============================
    // MÉTODOS DE ARQUIVO JSON
    // ============================
    
    /**
     * Lê todas as seções do arquivo JSON
     */
    private static function readJsonFile(): array {
        $dir = dirname(self::JSON_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        if (!file_exists(self::JSON_FILE)) {
            file_put_contents(self::JSON_FILE, json_encode([], JSON_PRETTY_PRINT));
            return [];
        }
        
        $content = file_get_contents(self::JSON_FILE);
        return json_decode($content, true) ?: [];
    }

    /**
     * Salva as seções no arquivo JSON
     */
    private static function writeJsonFile(array $sessions): bool {
        $dir = dirname(self::JSON_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $json = json_encode($sessions, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        return file_put_contents(self::JSON_FILE, $json) !== false;
    }

    /**
     * Gera um novo ID único
     */
    private static function generateId(): int {
        $sessions = self::readJsonFile();
        if (empty($sessions)) return 1;
        return max(array_column($sessions, 'id')) + 1;
    }

    /**
     * Gera slug único
     */
    private static function generateSlug(string $titulo): string {
        $slug = strtolower(trim($titulo));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Verifica unicidade
        $sessions = self::readJsonFile();
        $baseSlug = $slug;
        $count = 1;
        while (true) {
            $exists = false;
            foreach ($sessions as $s) {
                if ($s['slug'] === $slug) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) break;
            $slug = $baseSlug . '-' . $count++;
        }
        
        return $slug;
    }

    // ============================
    // MÉTODOS DE BUSCA
    // ============================
    
    /**
     * Busca uma seção por ID
     */
    public static function findById(int $id): ?SessionModel {
        $sessions = self::readJsonFile();
        foreach ($sessions as $data) {
            if ($data['id'] === $id) {
                return new SessionModel($data);
            }
        }
        return null;
    }

    /**
     * Busca uma seção por slug
     */
    public static function findBySlug(string $slug): ?SessionModel {
        $sessions = self::readJsonFile();
        foreach ($sessions as $data) {
            if ($data['slug'] === $slug) {
                return new SessionModel($data);
            }
        }
        return null;
    }

    /**
     * Retorna todas as seções
     */
    public static function findAll(): array {
        $sessions = self::readJsonFile();
        usort($sessions, fn($a, $b) => ($a['ordem'] ?? 0) - ($b['ordem'] ?? 0));
        
        $result = [];
        foreach ($sessions as $data) {
            $result[] = new SessionModel($data);
        }
        return $result;
    }

    /**
     * Retorna seções ativas
     */
    public static function findAllActive(): array {
        return array_filter(self::findAll(), fn($s) => $s->isAtivo());
    }

    /**
     * Retorna seções por página
     */
    public static function findByPagina(string $pagina): array {
        $all = self::findAll();
        return array_filter($all, fn($s) => $s->getPagina() === $pagina && $s->isAtivo());
    }

    /**
     * Retorna seções por tipo
     */
    public static function findByTipo(string $tipo): array {
        $all = self::findAll();
        return array_filter($all, fn($s) => $s->getTipo() === $tipo);
    }

    /**
     * Conta total de seções
     */
    public static function count(): int {
        return count(self::readJsonFile());
    }

    // ============================
    // MÉTODOS DE CRIAÇÃO
    // ============================
    
    /**
     * Cria uma nova seção
     */
    public static function create(array $data): ?SessionModel {
        $sessions = self::readJsonFile();
        
        $data['id'] = self::generateId();
        $data['slug'] = $data['slug'] ?? self::generateSlug($data['titulo']);
        $data['criacao'] = date('Y-m-d H:i:s');
        $data['atualizacao'] = date('Y-m-d H:i:s');
        $data['ativo'] = $data['ativo'] ?? true;
        $data['ordem'] = $data['ordem'] ?? count($sessions);
        $data['conteudo'] = $data['conteudo'] ?? [];
        
        $sessions[] = $data;
        
        if (self::writeJsonFile($sessions)) {
            return new SessionModel($data);
        }
        return null;
    }

    // ============================
    // MÉTODOS DE ATUALIZAÇÃO
    // ============================
    
    /**
     * Atualiza a seção atual
     */
    public function update(array $data): bool {
        $sessions = self::readJsonFile();
        
        foreach ($sessions as $index => $sessionData) {
            if ($sessionData['id'] === $this->id) {
                $data['atualizacao'] = date('Y-m-d H:i:s');
                $sessions[$index] = array_merge($sessionData, $data);
                $sessions[$index]['id'] = $this->id;
                
                if (self::writeJsonFile($sessions)) {
                    if (isset($data['titulo'])) $this->titulo = $data['titulo'];
                    if (isset($data['subtitulo'])) $this->subtitulo = $data['subtitulo'];
                    if (isset($data['tipo'])) $this->tipo = $data['tipo'];
                    if (isset($data['conteudo'])) $this->conteudo = $data['conteudo'];
                    if (isset($data['ordem'])) $this->ordem = $data['ordem'];
                    if (isset($data['ativo'])) $this->ativo = $data['ativo'];
                    if (isset($data['pagina'])) $this->pagina = $data['pagina'];
                    $this->atualizacao = $data['atualizacao'];
                    return true;
                }
                break;
            }
        }
        return false;
    }

    /**
     * Atualiza apenas o conteúdo
     */
    public function updateConteudo(array $conteudo): bool {
        return $this->update(['conteudo' => $conteudo]);
    }

    /**
     * Adiciona/atualiza um valor no conteúdo
     */
    public function setConteudoValue(string $key, $value): bool {
        $novoConteudo = $this->conteudo;
        $novoConteudo[$key] = $value;
        return $this->updateConteudo($novoConteudo);
    }

    public function activate(): bool {
        return $this->update(['ativo' => true]);
    }

    public function deactivate(): bool {
        return $this->update(['ativo' => false]);
    }

    /**
     * Reordena todas as seções
     */
    public static function reorder(array $idsOrdenados): bool {
        $sessions = self::readJsonFile();
        
        foreach ($sessions as $index => $data) {
            $novaOrdem = array_search($data['id'], $idsOrdenados);
            if ($novaOrdem !== false) {
                $sessions[$index]['ordem'] = $novaOrdem;
            }
        }
        
        return self::writeJsonFile($sessions);
    }

    // ============================
    // MÉTODOS DE EXCLUSÃO
    // ============================
    
    /**
     * Remove a seção atual
     */
    public function delete(): bool {
        return self::deleteById($this->id);
    }

    /**
     * Remove uma seção por ID
     */
    public static function deleteById(int $id): bool {
        $sessions = self::readJsonFile();
        $sessions = array_filter($sessions, fn($s) => $s['id'] !== $id);
        return self::writeJsonFile(array_values($sessions));
    }

    /**
     * Remove todas as seções
     */
    public static function deleteAll(): bool {
        return self::writeJsonFile([]);
    }
}
