<?php
declare(strict_types=1);

namespace eLonePath;

use Throwable;

/**
 * Handles exceptions and displays detailed error pages
 */
class ErrorHandler
{
    /**
     * Display detailed exception page
     *
     * @param \Throwable $e
     * @return void
     */
    public static function display(Throwable $e): void
    {
        http_response_code(500);

        $exceptionType = htmlspecialchars(get_class($e), ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        $file = htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8');
        $line = $e->getLine();
        $trace = self::formatTrace($e->getTrace());

        /** @link templates/errors/exception.php */
        require_once TEMPLATES . '/errors/exception.php';
    }

    /**
     * Format stack trace with highlighting
     *
     * @param array<int, array<string, mixed>> $trace
     * @return string
     */
    private static function formatTrace(array $trace): string
    {
        $html = '';

        foreach ($trace as $i => $item) {
            $function = $item['function'] ?? 'unknown';
            $class = $item['class'] ?? '';
            $callType = $item['type'] ?? '';
            $itemFile = $item['file'] ?? 'unknown';
            $itemLine = $item['line'] ?? '?';

            // Build full function name
            $fullFunc = $class ? "{$class}{$callType}{$function}" : $function;
            $fullFunc = htmlspecialchars($fullFunc, ENT_QUOTES, 'UTF-8');

            // Highlight project files vs vendor files
            $isVendor = str_contains($itemFile, '/vendor/');
            $fileClass = $isVendor ? 'text-secondary' : 'fw-bold';
            $itemFile = htmlspecialchars($itemFile, ENT_QUOTES, 'UTF-8');

            $html .= '<div class="mb-2">';
            $html .= '<div><span class="fw-bolder me-1 text-secondary">#' . $i . '</span> <span class="trace-func">' . $fullFunc . '()</span></div>';
            $html .= '<div class="mt-1 small ' . $fileClass . '">' . $itemFile . ':<span class="ms-1 text-danger fw-bold">' . $itemLine . '</span></div>';
            $html .= '</div>';
        }

        return $html;
    }
}
