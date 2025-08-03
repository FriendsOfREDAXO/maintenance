<?php
    $responsecode = (int) rex_config::get('maintenance', 'http_response_code', 503);
    
    // For permanent lock mode, we provide minimal content to avoid revealing CMS details
    // The HTTP status code should already be set before this fragment is called
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="noindex, nofollow">
    <title>Access Forbidden</title>
    <style type="text/css">
        html, body {
            height: 100%;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Helvetica Neue", Arial, sans-serif;
            font-size: 16px;
            color: #495057;
        }
        .error-container {
            text-align: center;
            max-width: 400px;
            padding: 20px;
        }
        .error-code {
            font-size: 72px;
            font-weight: 300;
            margin: 0;
            color: #dc3545;
        }
        .error-message {
            font-size: 18px;
            margin: 20px 0;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-code"><?= $responsecode ?></h1>
        <?php if ($responsecode === 403): ?>
            <p class="error-message">Access Forbidden</p>
        <?php else: ?>
            <p class="error-message">Service Unavailable</p>
        <?php endif; ?>
    </div>
</body>
</html>