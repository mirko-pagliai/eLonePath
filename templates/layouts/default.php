<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'eLonePath' ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .wrapper {
            max-width: 1200px;
            margin: 0 auto;
        }
        header {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 20px 40px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header h1 {
            color: #333;
            font-size: 1.8em;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        nav a {
            color: #667eea;
            text-decoration: none;
            margin-left: 20px;
            font-weight: 600;
            transition: color 0.3s;
        }
        nav a:hover {
            color: #764ba2;
        }
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 60px;
            min-height: 400px;
        }
        h1 {
            color: #333;
            font-size: 2.5em;
            margin-bottom: 20px;
        }
        p {
            color: #666;
            font-size: 1.2em;
            line-height: 1.6;
        }
        footer {
            text-align: center;
            color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            margin-top: 20px;
            font-size: 0.9em;
        }
        <?= $styles ?? '' ?>
    </style>
    <?= $headExtra ?? '' ?>
</head>
<body>
    <div class="wrapper">
        <header>
            <h1>eLonePath</h1>
            <nav>
                <a href="/">Home</a>
                <a href="/about">About</a>
                <a href="/start">Start Game</a>
            </nav>
        </header>
        
        <div class="container">
            <?= $content ?>
        </div>
        
        <footer>
            <p>&copy; <?= date('Y') ?> eLonePath - Interactive Gamebook Engine</p>
        </footer>
    </div>
    <?= $scripts ?? '' ?>
</body>
</html>
