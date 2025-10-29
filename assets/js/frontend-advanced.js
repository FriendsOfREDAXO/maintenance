/**
 * Frontend Advanced Settings - IP Whitelist Management
 */
$(document).on('rex:ready', function() {
    // Funktion zum Hinzufügen einer IP-Adresse zum Whitelist-Feld
    function addIpToWhitelist(ip) {
        var ipField = $('#maintenance-allowed-ips');
        
        if (!ipField.length) {
            console.error('IP field not found');
            return;
        }
        
        if (ipField.val().trim() === '') {
            // Wenn das Feld leer ist, einfach die IP hinzufügen
            ipField.val(ip);
        } else {
            // IP-Adressen als Array verarbeiten und alle Leerzeichen entfernen
            var ips = ipField.val().split(',').map(function(ip) {
                return ip.trim();
            }).filter(function(ip) {
                // Leere Einträge filtern
                return ip !== '';
            });
            
            // Prüfen, ob IP bereits enthalten ist
            if (ips.indexOf(ip) === -1) {
                ips.push(ip);
                // Saubere Komma-getrennte Liste ohne unnötige Leerzeichen
                ipField.val(ips.join(','));
            }
        }
    }
    
    // Client-IP-Adresse hinzufügen
    $('#maintenance-add-ip').on('click', function(e) {
        e.preventDefault();
        var currentIp = $(this).data('ip');
        if (currentIp) {
            addIpToWhitelist(currentIp);
        }
    });
    
    // Server-IP-Adresse hinzufügen
    $('#maintenance-add-server-ip').on('click', function(e) {
        e.preventDefault();
        var serverIp = $(this).data('ip');
        if (serverIp) {
            addIpToWhitelist(serverIp);
        }
    });
});
