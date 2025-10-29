<?php
$maintenanceFrontendHeadline = rex_config::get('maintenance', 'maintenance_frontend_headline', 'Maintenance / Wartung');
$maintenanceTextEn = rex_config::get('maintenance', 'maintenance_text_en', 'This website is temporarily unavailable.');
$maintenanceTextDe = rex_config::get('maintenance', 'maintenance_text_de', 'Diese Website ist vorübergehend nicht erreichbar.');
$maintenanceFrontendUpdateIntervalNumber = rex_config::get('maintenance', 'maintenance_frontend_update_interval', 60);

// Check if both languages are available
$multilanguageEnabled = ('' !== $maintenanceTextEn && '' !== $maintenanceTextDe);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta http-equiv="refresh" content="<?= $maintenanceFrontendUpdateIntervalNumber > 0 ? $maintenanceFrontendUpdateIntervalNumber : '' ?>">
    <title>
        <?php
            if (rex_addon::exists('yrewrite') && rex_addon::get('yrewrite')->isAvailable() && null !== rex_yrewrite::getCurrentDomain()?->getName()) {
                echo rex_yrewrite::getCurrentDomain()->getName();
            } else {
                echo rex::getServerName();
            }
        ?> - Maintenance
    </title>
    <style nonce="<?= rex_response::getNonce() ?>">
        :root {
            --primary-color: #5b98d7;
            --text-color: #555;
            --bg-color: #f8f8f8;
            --card-bg: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --error-color: #e74c3c;
            --border-color: #ddd;
            --button-hover: #4a87c6;
        }
        
        @media (prefers-color-scheme: dark) {
            :root {
                --primary-color: #64a0e0;
                --text-color: #e0e0e0;
                --bg-color: #121212;
                --card-bg: #1e1e1e;
                --shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
                --error-color: #e74c3c;
                --border-color: #444;
                --button-hover: #5590c9;
            }
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: var(--bg-color);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        
        .maintenance-container {
            max-width: 450px;
            width: 100%;
            padding: 2rem;
            padding-top: 4rem;
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: var(--shadow);
            text-align: center;
            animation: fadeIn 0.5s ease-in-out;
            position: relative;
        }
        
        .language-switcher {
            position: absolute;
            top: 1rem;
            right: 1rem;
            z-index: 100;
        }
        
        .language-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .language-button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 160px;
            justify-content: center;
            font-weight: 500;
        }
        
        .language-button:hover {
            background: var(--button-hover);
            transform: translateY(-1px);
        }
        
        .language-flag {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            opacity: 0.8;
        }
        
        .language-code {
            display: inline-block;
            min-width: 28px;
            padding: 2px 6px;
            background: var(--bg-color);
            border-radius: 3px;
            font-size: 0.75rem;
            font-weight: 600;
            text-align: center;
            opacity: 0.7;
        }
        
        .language-option.active .language-code {
            background: rgba(255,255,255,0.2);
            opacity: 1;
        }
        
        .language-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            box-shadow: var(--shadow);
            min-width: 150px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
            margin-top: 0.5rem;
        }
        
        .language-menu.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .language-option {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            padding: 0.75rem 1rem;
            border: none;
            background: none;
            text-align: left;
            cursor: pointer;
            color: var(--text-color);
            transition: background-color 0.2s;
            font-size: 0.9rem;
        }
        
        .language-option:hover {
            background-color: var(--bg-color);
        }
        
        .language-option.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .maintenance-title {
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
            opacity: 0;
            animation: slideInUp 0.6s ease 0.2s forwards;
        }
        
        .maintenance-title .maintenance-text {
            display: none;
        }
        
        .maintenance-title .maintenance-text.active {
            display: inline;
        }
        
        .maintenance-message {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0;
            animation: slideInUp 0.6s ease 0.4s forwards;
        }
        
        .maintenance-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            fill: var(--primary-color);
            display: block;
            opacity: 0;
            animation: slideInUp 0.6s ease 0.1s forwards;
        }
        
        .maintenance-text {
            display: none;
        }
        
        .maintenance-text.active {
            display: block;
        }
        
        .maintenance-login {
            margin-top: 2rem;
            max-width: 300px;
            margin-left: auto;
            margin-right: auto;
            opacity: 0;
            animation: slideInUp 0.6s ease 0.6s forwards;
        }
        
        .maintenance-login label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
            text-align: left;
        }
        
        .maintenance-pw-input,
        .maintenance-input {
            display: block;
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            margin-bottom: 1rem;
            font-size: 1rem;
            background-color: var(--card-bg);
            color: var(--text-color);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        
        .maintenance-pw-input:focus,
        .maintenance-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(91, 152, 215, 0.2);
        }
        
        .maintenance-pw-btn,
        .maintenance-button,
        .maintenance-btn {
            display: inline-block;
            width: 100%;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .maintenance-pw-btn:hover,
        .maintenance-button:hover,
        .maintenance-btn:hover {
            background-color: var(--button-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .error-message {
            color: var(--error-color);
            margin-bottom: 1rem;
            font-size: 0.9rem;
            padding: 0.5rem;
            background-color: rgba(231, 76, 60, 0.1);
            border-radius: 4px;
            border: 1px solid var(--error-color);
        }
        
        .maintenance-reload {
            margin-top: 2rem;
            opacity: 0;
            animation: slideInUp 0.6s ease 0.7s forwards;
        }
        
        .maintenance-reload .maintenance-btn {
            display: inline-block;
            width: auto;
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            text-decoration: none;
            background-color: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }
        
        .maintenance-reload .maintenance-btn:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 600px) {
            .maintenance-container {
                width: 90%;
                padding: 1.5rem;
                padding-top: 3.5rem;
            }
            
            .maintenance-title {
                font-size: 2rem;
            }
            
            .language-switcher {
                top: 1rem;
                right: 1rem;
            }
            
            .language-button {
                min-width: 120px;
                font-size: 0.85rem;
                padding: 0.4rem 0.8rem;
            }
            
            .language-menu {
                min-width: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <?php if ($multilanguageEnabled): ?>
        <div class="language-switcher">
            <div class="language-dropdown">
                <button class="language-button" id="language-toggle">
                    <svg class="language-flag" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M2 12h20"></path>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                    <span id="language-text">Language</span>
                </button>
                <div class="language-menu" id="language-menu">
                    <button class="language-option" data-lang="en">
                        <span class="language-code">EN</span>
                        English
                    </button>
                    <button class="language-option active" data-lang="de">
                        <span class="language-code">DE</span>
                        Deutsch
                    </button>
                </div>
            </div>
        </div>
        <?php endif ?>
        
        <svg class="maintenance-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        
        <?php
        // Domain ermitteln
        $domain = '';
        if (rex_addon::exists('yrewrite') && rex_addon::get('yrewrite')->isAvailable() && null !== rex_yrewrite::getCurrentDomain()?->getName()) {
            $domain = rex_yrewrite::getCurrentDomain()->getName();
        } else {
            $domain = rex::getServerName();
        }
        ?>
        
        <h1 class="maintenance-title">
            <?php if ($multilanguageEnabled): ?>
                <span class="maintenance-text" data-lang="en">Maintenance</span>
                <span class="maintenance-text active" data-lang="de">Wartung</span>
            <?php else: ?>
                <?= rex_escape($maintenanceFrontendHeadline) ?>
            <?php endif ?>
        </h1>
        
        <?php if ($domain): ?>
        <p style="color: var(--text-color); opacity: 0.7; font-size: 0.9rem; margin-top: -1rem; margin-bottom: 1.5rem;">
            <?= rex_escape($domain) ?>
        </p>
        <?php endif ?>
        
        <?php if ('' !== $maintenanceTextEn): ?>
        <div class="maintenance-message maintenance-text <?= '' === $maintenanceTextDe ? 'active' : '' ?>" data-lang="en">
            <?= nl2br(rex_escape($maintenanceTextEn)) ?>
        </div>
        <?php endif ?>
        
        <?php if ('' !== $maintenanceTextDe): ?>
        <div class="maintenance-message maintenance-text active" data-lang="de">
            <?= nl2br(rex_escape($maintenanceTextDe)) ?>
        </div>
        <?php endif ?>
        
        <?php
        // Subfragments ausgeben
        echo $this->subfragment('maintenance/announcement.php');
        echo $this->subfragment('maintenance/login.php');
        echo $this->subfragment('maintenance/reload.php');
        ?>
    </div>

    <?php if ($multilanguageEnabled): ?>
    <script nonce="<?= rex_response::getNonce() ?>">
        document.addEventListener('DOMContentLoaded', function() {
            var languageToggle = document.getElementById('language-toggle');
            var languageMenu = document.getElementById('language-menu');
            var languageOptions = document.querySelectorAll('.language-option');
            var langTexts = document.querySelectorAll('.maintenance-text');
            
            if (languageToggle) {
                // Toggle dropdown
                languageToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    languageMenu.classList.toggle('active');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function() {
                    languageMenu.classList.remove('active');
                });
                
                // Handle language selection
                languageOptions.forEach(function(option) {
                    option.addEventListener('click', function(e) {
                        e.stopPropagation();
                        var selectedLang = this.getAttribute('data-lang');
                        
                        // Update button states
                        languageOptions.forEach(function(opt) {
                            opt.classList.remove('active');
                        });
                        this.classList.add('active');
                        
                        // Update text visibility
                        langTexts.forEach(function(text) {
                            if (text.getAttribute('data-lang') === selectedLang) {
                                text.classList.add('active');
                            } else {
                                text.classList.remove('active');
                            }
                        });
                        
                        // Save preference to sessionStorage
                        try {
                            sessionStorage.setItem('maintenance_lang', selectedLang);
                        } catch(e) {}
                        
                        languageMenu.classList.remove('active');
                    });
                });
                
                // Restore language preference from sessionStorage
                try {
                    var savedLang = sessionStorage.getItem('maintenance_lang');
                    if (savedLang) {
                        var savedBtn = document.querySelector('.language-option[data-lang="' + savedLang + '"]');
                        if (savedBtn) {
                            savedBtn.click();
                        }
                    }
                } catch(e) {}
            }
        });
    </script>
    <?php endif ?>
</body>
</html>
