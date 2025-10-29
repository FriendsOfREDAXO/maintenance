<?php
    $maintenanceFrontendHeadline = rex_config::get('maintenance', 'maintenance_frontend_headline', 'Maintenance / Wartung');
    $maintenanceTextEn = rex_config::get('maintenance', 'maintenance_text_en', 'This website is temporarily unavailable.');
    $maintenanceTextDe = rex_config::get('maintenance', 'maintenance_text_de', 'Diese Website ist vorübergehend nicht erreichbar.');
    $maintenanceFrontendUpdateIntervalNumber = rex_config::get('maintenance', 'maintenance_frontend_update_interval', 60);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="refresh" content="<?= $maintenanceFrontendUpdateIntervalNumber > 0 ? $maintenanceFrontendUpdateIntervalNumber : '' ?>">
    <title>
        <?php
            if (rex_addon::get('yrewrite')->isAvailable() && null !== rex_yrewrite::getCurrentDomain()?->getName()) {
                echo rex_yrewrite::getCurrentDomain()->getName();
            } else {
                echo rex::getServerName();
            }
        ?> - Maintenance
    </title>
    <link rel="stylesheet" href="<?= rex_url::addonAssets('maintenance', 'css/maintenance-frontend.css') ?>" />
    <style nonce="<?= rex_response::getNonce() ?>">
        .maintenance-container {
            position: relative;
            padding-top: 3rem;
        }
        
        .language-switcher {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            z-index: 100;
        }
        
        .language-dropdown {
            position: relative;
            display: inline-block;
        }
        
        .language-button {
            background: #5b98d7;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
            font-weight: 500;
        }
        
        .language-button:hover {
            background: #4a87c6;
            transform: translateY(-1px);
        }
        
        .language-flag {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            opacity: 0.8;
        }
        
        .language-menu {
            position: absolute;
            top: calc(100% + 0.5rem);
            right: 0;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            min-width: 140px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .language-menu.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .language-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.6rem 0.8rem;
            border: none;
            background: none;
            text-align: left;
            cursor: pointer;
            color: #666;
            transition: background-color 0.2s;
            font-size: 0.85rem;
        }
        
        .language-option:hover {
            background-color: #f7f7f7;
        }
        
        .language-option.active {
            background-color: #5b98d7;
            color: white;
        }
        
        .language-code {
            display: inline-block;
            min-width: 24px;
            padding: 2px 4px;
            background: #f7f7f7;
            border-radius: 3px;
            font-size: 0.7rem;
            font-weight: 600;
            text-align: center;
        }
        
        .language-option.active .language-code {
            background: rgba(255,255,255,0.2);
        }
        
        .maintenance-text {
            display: none;
        }
        .maintenance-text.active {
            display: block;
        }
        
        @media (max-width: 600px) {
            .maintenance-container {
                padding-top: 4rem;
            }
            
            .language-switcher {
                top: 0.5rem;
                right: 0.5rem;
            }
            
            .language-button {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
            }
            
            .language-menu {
                min-width: 130px;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <?php if ('' !== $maintenanceTextEn && '' !== $maintenanceTextDe): ?>
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
                    <button class="language-option active" data-lang="en">
                        <span class="language-code">EN</span>
                        English
                    </button>
                    <button class="language-option" data-lang="de">
                        <span class="language-code">DE</span>
                        Deutsch
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="maintenance-error">
            <p class="maintenance-error-title"><?= $maintenanceFrontendHeadline ?></p>
            
            <?php if ('' !== $maintenanceTextEn): ?>
            <p class="maintenance-error-message maintenance-text <?= '' === $maintenanceTextDe ? 'active' : 'active' ?>" data-lang="en"><?= nl2br(rex_escape($maintenanceTextEn)) ?></p>
            <?php endif; ?>
            <?php if ('' !== $maintenanceTextDe): ?>
            <p class="maintenance-error-message maintenance-text" lang="de" data-lang="de"><?= nl2br(rex_escape($maintenanceTextDe)) ?></p>
            <?php endif; ?>
        </div>
        <?php
        // Subfragment announcement.php ausgeben
        echo $this->subfragment('maintenance/announcement.php');
        echo $this->subfragment('maintenance/login.php');
        echo $this->subfragment('maintenance/reload.php');
        ?>
    </div>
    
    <script nonce="<?= rex_response::getNonce() ?>">
    (function() {
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
    })();
    </script>
</body>
</html>
