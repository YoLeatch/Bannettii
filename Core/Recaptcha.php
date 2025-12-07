<?php
namespace Core;

/**
 * Classe para validação do Google reCAPTCHA v2
 * 
 * Como funciona:
 * 1. O usuário clica no checkbox "Não sou um robô"
 * 2. O Google retorna um TOKEN para o frontend
 * 3. O formulário envia esse token junto com os outros dados
 * 4. Esta classe pega esse token e pergunta ao Google: "Esse token é válido?"
 * 5. O Google responde SIM ou NÃO
 */
class Recaptcha 
{
    // URL da API do Google para verificação
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
    
    /**
     * Verifica se o token do reCAPTCHA é válido
     * 
     * @param string $token O token g-recaptcha-response enviado pelo formulário
     * @return bool True se válido, False se inválido ou erro
     */
    public static function verify(string $token): bool 
    {
        // Se não tem token, já retorna falso
        if (empty($token)) {
            return false;
        }
        
        // Pega a chave secreta do arquivo .env
        $secretKey = self::getSecretKey();
        
        if (empty($secretKey)) {
            // Em ambiente de desenvolvimento, pode-se desabilitar
            // Para produção, SEMPRE deve ter a chave configurada
            error_log('reCAPTCHA: Chave secreta não configurada!');
            return false;
        }
        
        // Monta os dados para enviar ao Google
        $data = [
            'secret'   => $secretKey,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '' // IP do usuário (opcional mas recomendado)
        ];
        
        // Faz a requisição POST para a API do Google
        $response = self::makeRequest($data);
        
        // Verifica se a resposta indica sucesso
        return $response['success'] ?? false;
    }
    
    /**
     * Obtém a chave do site (pública) para usar no frontend
     * 
     * @return string
     */
    public static function getSiteKey(): string 
    {
        return $_ENV['RECAPTCHA_SITE_KEY'] ?? getenv('RECAPTCHA_SITE_KEY') ?: '';
    }
    
    /**
     * Obtém a chave secreta (privada) para validação no backend
     * 
     * @return string
     */
    private static function getSecretKey(): string 
    {
        return $_ENV['RECAPTCHA_SECRET_KEY'] ?? getenv('RECAPTCHA_SECRET_KEY') ?: '';
    }
    
    /**
     * Faz a requisição HTTP POST para a API do Google
     * 
     * @param array $data Dados a serem enviados
     * @return array Resposta do Google decodificada
     */
    private static function makeRequest(array $data): array 
    {
        // Usa cURL se disponível (mais robusto)
        if (function_exists('curl_init')) {
            return self::makeRequestCurl($data);
        }
        
        // Fallback para file_get_contents
        return self::makeRequestStream($data);
    }
    
    /**
     * Requisição usando cURL
     */
    private static function makeRequestCurl(array $data): array 
    {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL            => self::VERIFY_URL,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            error_log('reCAPTCHA cURL error: ' . $error);
            return ['success' => false];
        }
        
        return json_decode($response, true) ?: ['success' => false];
    }
    
    /**
     * Requisição usando file_get_contents (fallback)
     */
    private static function makeRequestStream(array $data): array 
    {
        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query($data),
                'timeout' => 10
            ]
        ];
        
        $context = stream_context_create($options);
        $response = @file_get_contents(self::VERIFY_URL, false, $context);
        
        if ($response === false) {
            error_log('reCAPTCHA stream error: Não foi possível conectar ao Google');
            return ['success' => false];
        }
        
        return json_decode($response, true) ?: ['success' => false];
    }
}
