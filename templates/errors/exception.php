<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - eLonePath</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: monospace;
            background: #1a1a1a;
            color: #e0e0e0;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #2d2d2d;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        h1 {
            color: #ff6b6b;
            font-size: 2em;
            margin-bottom: 10px;
            border-bottom: 2px solid #ff6b6b;
            padding-bottom: 10px;
        }

        .type {
            color: #ffd93d;
            font-size: 1.2em;
            margin-bottom: 20px;
        }

        .message {
            background: #3d3d3d;
            padding: 15px;
            border-left: 4px solid #ff6b6b;
            margin-bottom: 20px;
            color: #fff;
            font-size: 1.1em;
        }

        .location {
            color: #6bcf7f;
            margin-bottom: 20px;
        }

        .location strong {
            color: #8ed99d;
        }

        h2 {
            color: #6bcf7f;
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 1.5em;
        }

        .trace {
            background: #1a1a1a;
            padding: 20px;
            border-radius: 4px;
            overflow-x: auto;
            font-size: 0.9em;
            line-height: 1.6;
        }

        .trace-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #3d3d3d;
        }

        .trace-item:last-child {
            border-bottom: none;
        }

        .trace-num {
            color: #ffd93d;
            font-weight: bold;
        }

        .trace-func {
            color: #6bcf7f;
        }

        .trace-file {
            color: #6eb5ff;
            margin-top: 5px;
        }

        .trace-file.project-file {
            color: #8ed99d;
            font-weight: bold;
        }

        .trace-file.vendor-file {
            color: #999;
            opacity: 0.7;
        }

        .trace-line {
            color: #ff6b6b;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>💥 Exception</h1>
    <div class="type"><?= $exceptionType ?></div>
    <div class="message"><?= $message ?></div>
    <div class="location">
        <strong>File:</strong> <?= $file ?><br>
        <strong>Line:</strong> <span class="trace-line"><?= $line ?></span>
    </div>

    <h2>📋 Stack Trace</h2>
    <div class="trace">
        <?= $trace ?>
    </div>
</div>
</body>
</html>
