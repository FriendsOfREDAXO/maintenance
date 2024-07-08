<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Maintenance</title>
    <style type="text/css">
        html, body {
            height: 100%;
            background-color: #f7f7f7;
        }
        body {
            display: flex;
            align-items: center;
        }
        .maintenance-container {
            max-width: 500px;
            min-width: 300px;
            width: 50%;
            margin: 0 auto;
            color: #999;
            font-family: -apple-system, BlinkMacSystemFont, "Helvetica Neue", Arial, sans-serif;
            font-size: 15px;
            line-height: 1.5;
            text-align: center;
        }
        .maintenance-error a {
            color: #666;
        }
        .maintenance-error a:hover {
            color: #111;
        }
        .maintenance-error-title {
            margin: 0;
            font-size: 40px;
            font-weight: 700;
            color: #5b98d7;
            text-shadow: 0 4px 2px rgba(255, 255, 255, 1);
            line-height: 1.2em;
        }
        .maintenance-error-message {
            padding: 0 20px;
        }
		
		.maintenance-pw-input {
			margin: 1em 1em 1em 1em;
			padding: 1em 2em 1em 1em;
			background: rgba(255,255,255,1);
			border-radius: 30px;
			border: none;
			font-size: 1em;
			}
		
		.maintenance-pw-btn {
			margin: 1em 1em 1em 1em;
			padding: 1em 1em 1em 1em;
			background: #5b98d7;
			border-radius: 10px;
			color: #ffffff;
			border: none;
			font-size: 1em;
			cursor: default;
			}	
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-error">
            <p class="maintenance-error-title">Login</p>
            <form action="<?= rex_url::base(); ?>" method="post">
            <input name="maintenance_secret" class="maintenance-pw-input" type="password" placeholder="**********"/><br>
            <button type="submit" class="maintenance-pw-btn">Enter</button>
            </form>
        </div>
    </div>
</body>
</html>

