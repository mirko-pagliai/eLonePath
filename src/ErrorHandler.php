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

        $type = htmlspecialchars(get_class($e), ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        $file = htmlspecialchars($e->getFile(), ENT_QUOTES, 'UTF-8');
        $line = $e->getLine();

        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Error</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: monospace; background: #1a1a1a; color: #e0e0e0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: #2d2d2d; border-radius: 8px; padding: 30px; }
        h1 { color: #ff6b6b; font-size: 2em; margin-bottom: 10px; border-bottom: 2px solid #ff6b6b; padding-bottom: 10px; }
        .type { color: #ffd93d; font-size: 1.2em; margin-bottom: 20px; }
        .message { background: #3d3d3d; padding: 15px; border-left: 4px solid #ff6b6b; margin-bottom: 20px; color: #fff; }
        .location { color: #6bcf7f; margin-bottom: 20px; }
        h2 { color: #6bcf7f; margin-top: 30px; margin-bottom: 15px; }
        .trace { background: #1a1a1a; padding: 20px; border-radius: 4px; overflow-x: auto; }
        .trace-item { margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #3d3d3d; }
        .trace-item:last-child { border-bottom: none; }
        .trace-num { color: #ffd93d; font-weight: bold; }
        .trace-func { color: #6bcf7f; }
        .trace-file { color: #6eb5ff; margin-top: 5px; }
        .trace-line { color: #ff6b6b; }
    </style>
</head>
<body>
    <div class="container">
        <h1>💥 Exception</h1>
        <div class="type">' . $type . '</div>
        <div class="message">' . $message . '</div>
        <div class="location"><strong>File:</strong> ' . $file . '<br><strong>Line:</strong> <span class="trace-line">' . $line . '</span></div>
        <h2>Stack Trace</h2>
        <div class="trace">';

        foreach ($e->getTrace() as $i => $item) {
            $func = $item['function'] ?? 'unknown';
            $class = $item['class'] ?? '';
            $type = $item['type'] ?? '';
            $itemFile = htmlspecialchars($item['file'] ?? 'unknown', ENT_QUOTES, 'UTF-8');
            $itemLine = $item['line'] ?? '?';
            $fullFunc = $class ? htmlspecialchars("{$class}{$type}{$func}", ENT_QUOTES, 'UTF-8') : htmlspecialchars($func, ENT_QUOTES, 'UTF-8');

            echo '<div class="trace-item">';
            echo '<div><span class="trace-num">#' . $i . '</span> <span class="trace-func">' . $fullFunc . '()</span></div>';
            echo '<div class="trace-file">' . $itemFile . ':<span class="trace-line">' . $itemLine . '</span></div>';
            echo '</div>';
        }

        echo '</div>
    </div>
</body>
</html>';
    }
}
