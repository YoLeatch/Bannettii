<?php
/**
 * SettingsController - Configurações Gerais (Admin)
 * 
 * @package App\Pages\Controllers\admin
 */

namespace App\Pages\Controllers\admin;

use Core\ViewerPlace;

class SettingsController
{
    private const SETTINGS_FILE = __DIR__ . '/../../../../storage/JSON/settings.json';

    /**
     * Página de configurações
     */
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        $settings = $this->loadSettings();

        echo ViewerPlace::render('admin-settings', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador',
            'site_name' => htmlspecialchars($settings['site_name'] ?? 'Bennettii'),
            'site_description' => htmlspecialchars($settings['site_description'] ?? ''),
            'contact_email' => htmlspecialchars($settings['contact_email'] ?? ''),
            'contact_phone' => htmlspecialchars($settings['contact_phone'] ?? ''),
            'social_instagram' => htmlspecialchars($settings['social_instagram'] ?? ''),
            'social_facebook' => htmlspecialchars($settings['social_facebook'] ?? ''),
            'maintenance_mode' => isset($settings['maintenance_mode']) && $settings['maintenance_mode'] ? 'checked' : ''
        ]);
    }

    /**
     * Salva configurações
     */
    public function update()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        $settings = [
            'site_name' => trim($_POST['site_name'] ?? 'Bennettii'),
            'site_description' => trim($_POST['site_description'] ?? ''),
            'contact_email' => trim($_POST['contact_email'] ?? ''),
            'contact_phone' => trim($_POST['contact_phone'] ?? ''),
            'social_instagram' => trim($_POST['social_instagram'] ?? ''),
            'social_facebook' => trim($_POST['social_facebook'] ?? ''),
            'maintenance_mode' => isset($_POST['maintenance_mode']),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Upload do logo se fornecido
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../../../public_html/assets/image/';
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $filename = 'logo_' . time() . '.' . $ext;
            $uploadPath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadPath)) {
                $settings['logo'] = '/assets/image/' . $filename;
            }
        }

        $this->saveSettings($settings);

        header('Location: /admin/settings?success=updated');
        exit;
    }

    /**
     * Página de configurações gerais
     */
    public function general()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            header("Location: /login");
            exit;
        }

        echo ViewerPlace::render('admin-configuracoes', [
            'admin_avatar' => $this->getAdminAvatar(),
            'admin_name' => htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'),
            'admin_role' => 'Administrador'
        ]);
    }

    private function loadSettings(): array
    {
        if (!file_exists(self::SETTINGS_FILE)) {
            return $this->getDefaultSettings();
        }
        $content = file_get_contents(self::SETTINGS_FILE);
        $settings = json_decode($content, true);
        return $settings ?? $this->getDefaultSettings();
    }

    private function saveSettings(array $settings): void
    {
        $dir = dirname(self::SETTINGS_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents(self::SETTINGS_FILE, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function getDefaultSettings(): array
    {
        return [
            'site_name' => 'Bennettii',
            'site_description' => 'Conectando você à natureza com qualidade e estilo.',
            'contact_email' => 'suporte@bennettii.com.br',
            'contact_phone' => '(11) 99999-9999',
            'social_instagram' => '',
            'social_facebook' => '',
            'maintenance_mode' => false
        ];
    }

    private function getAdminAvatar(): string
    {
        $nome = $_SESSION['admin_name'] ?? 'Admin';
        $partes = explode(' ', $nome);
        $iniciais = strtoupper(substr($partes[0], 0, 1));
        if (count($partes) > 1) {
            $iniciais .= strtoupper(substr(end($partes), 0, 1));
        }
        return $iniciais;
    }
}
