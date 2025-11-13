<?php
namespace Core;

class LogReader {

    private $logDir;

    public function __construct() {
        $this->logDir = __DIR__ . '/../storage/logs/';
    }

    /**
     * Procura e filtra logs.
     *
     * @param string|null $date Data no formato 'Y-m-d'. Se nulo, usa a data de hoje.
     * @param string|null $level Nível do log (ex: 'ERROR', 'INFO').
     * @param string|null $context Texto para buscar na mensagem (ex: nome da classe).
     * @return array Array com as linhas do log que correspondem ao filtro.
     */
    public function search(?string $date = null, ?string $level = null, ?string $context = null): array {

        if ($date === null) {
            $date = date('Y-m-d');
        }

        $logFile = $this->logDir . 'app-' . $date . '.log';

        if (!file_exists($logFile)) {
            return [];
        }

        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $results = array_filter($lines, function($line) use ($level, $context) {
            $matchLevel = true;
            $matchContext = true;

            if ($level !== null) {
                $matchLevel = (stripos($line, "[$level]") !== false);
            }

            if ($context !== null) {
                $matchContext = (stripos($line, $context) !== false);
            }

            return $matchLevel && $matchContext;
        });

        return array_values($results);
    }
}