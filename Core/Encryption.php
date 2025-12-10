<?php
/**
 * Encryption - Classe utilitária para criptografia de dados sensíveis
 * 
 * Usa AES-256-GCM para criptografia simétrica segura.
 * Ideal para dados sensíveis como números de cartão de crédito.
 * 
 * @package Core
 */

namespace Core;

class Encryption
{
    /** Algoritmo de criptografia */
    private const CIPHER = 'aes-256-gcm';
    
    /** Tamanho do IV para AES-256-GCM */
    private const IV_LENGTH = 12;
    
    /** Tamanho da tag de autenticação */
    private const TAG_LENGTH = 16;
    
    /**
     * Obtém a chave de criptografia do ambiente
     * 
     * @return string Chave de 32 bytes para AES-256
     */
    private static function getKey(): string
    {
        $key = $_ENV['ENCRYPTION_KEY'] ?? getenv('ENCRYPTION_KEY');
        
        if (!$key) {
            // Fallback para JWT_SECRET se ENCRYPTION_KEY não estiver definida
            $key = $_ENV['JWT_SECRET'] ?? getenv('JWT_SECRET') ?? 'default_encryption_key_change_me';
        }
        
        // Garante que a chave tenha 32 bytes para AES-256
        return hash('sha256', $key, true);
    }
    
    /**
     * Criptografa dados sensíveis
     * 
     * @param string $plaintext Texto a ser criptografado
     * @return string Texto criptografado (base64)
     */
    public static function encrypt(string $plaintext): string
    {
        if (empty($plaintext)) {
            return '';
        }
        
        $key = self::getKey();
        $iv = random_bytes(self::IV_LENGTH);
        $tag = '';
        
        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            self::TAG_LENGTH
        );
        
        if ($ciphertext === false) {
            throw new \RuntimeException('Falha na criptografia');
        }
        
        // Combina IV + Tag + Ciphertext e codifica em base64
        return base64_encode($iv . $tag . $ciphertext);
    }
    
    /**
     * Descriptografa dados
     * 
     * @param string $encrypted Texto criptografado (base64)
     * @return string Texto original
     */
    public static function decrypt(string $encrypted): string
    {
        if (empty($encrypted)) {
            return '';
        }
        
        // Verifica se já é um número de cartão não criptografado (migração)
        if (ctype_digit(preg_replace('/\D/', '', $encrypted)) && strlen($encrypted) <= 19) {
            return $encrypted;
        }
        
        try {
            $data = base64_decode($encrypted, true);
            
            if ($data === false || strlen($data) < self::IV_LENGTH + self::TAG_LENGTH) {
                // Pode ser um número antigo não criptografado
                return $encrypted;
            }
            
            $key = self::getKey();
            
            // Extrai IV, Tag e Ciphertext
            $iv = substr($data, 0, self::IV_LENGTH);
            $tag = substr($data, self::IV_LENGTH, self::TAG_LENGTH);
            $ciphertext = substr($data, self::IV_LENGTH + self::TAG_LENGTH);
            
            $plaintext = openssl_decrypt(
                $ciphertext,
                self::CIPHER,
                $key,
                OPENSSL_RAW_DATA,
                $iv,
                $tag
            );
            
            if ($plaintext === false) {
                // Pode ser um número antigo não criptografado
                return $encrypted;
            }
            
            return $plaintext;
        } catch (\Exception $e) {
            // Fallback para dados não criptografados
            return $encrypted;
        }
    }
    
    /**
     * Mascara número de cartão para exibição segura
     * Mostra apenas os últimos 4 dígitos
     * 
     * @param string $cardNumber Número do cartão
     * @return string Número mascarado (ex: •••• •••• •••• 1234)
     */
    public static function maskCardNumber(string $cardNumber): string
    {
        $digits = preg_replace('/\D/', '', $cardNumber);
        
        if (strlen($digits) < 4) {
            return '•••• •••• •••• ••••';
        }
        
        $lastFour = substr($digits, -4);
        return '•••• •••• •••• ' . $lastFour;
    }
    
    /**
     * Retorna apenas os últimos 4 dígitos do cartão
     * 
     * @param string $cardNumber Número do cartão
     * @return string Últimos 4 dígitos
     */
    public static function getLastFourDigits(string $cardNumber): string
    {
        $digits = preg_replace('/\D/', '', $cardNumber);
        return substr($digits, -4);
    }
}
