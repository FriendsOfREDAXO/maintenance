/**
 * Frontend Advanced Settings - IP Whitelist Management
 */
(function($) {
    'use strict';
    
    $(function() {
        console.log('Maintenance IP management loaded');
        
        // Funktion zum Hinzufügen einer IP-Adresse zum Whitelist-Feld
        function addIpToWhitelist(ip) {
            console.log('Adding IP:', ip);
            var ipField = $('#maintenance-allowed-ips');
            
            console.log('Field found:', ipField.length);
            
            if (!ipField.length) {
                console.error('IP field not found');
                return;
            }
            
            var currentValue = ipField.val().trim();
            console.log('Current value:', currentValue);
            
            if (currentValue === '') {
                // Wenn das Feld leer ist, einfach die IP hinzufügen
                ipField.val(ip);
                console.log('Set value to:', ip);
            } else {
                // IP-Adressen als Array verarbeiten und alle Leerzeichen entfernen
                var ips = currentValue.split(',').map(function(ip) {
                    return ip.trim();
                }).filter(function(ip) {
                    // Leere Einträge filtern
                    return ip !== '';
                });
                
                console.log('Current IPs:', ips);
                
                // Prüfen, ob IP bereits enthalten ist
                if (ips.indexOf(ip) === -1) {
                    ips.push(ip);
                    // Saubere Komma-getrennte Liste ohne unnötige Leerzeichen
                    ipField.val(ips.join(','));
                    console.log('Updated value to:', ips.join(','));
                } else {
                    console.log('IP already exists');
                }
            }
        }
        
        // Client-IP-Adresse hinzufügen
        $(document).on('click', '#maintenance-add-ip', function(e) {
            e.preventDefault();
            console.log('Add IP button clicked');
            var currentIp = $(this).data('ip');
            console.log('IP from data attribute:', currentIp);
            if (currentIp) {
                addIpToWhitelist(currentIp);
            }
        });
        
        // Server-IP-Adresse hinzufügen
        $(document).on('click', '#maintenance-add-server-ip', function(e) {
            e.preventDefault();
            console.log('Add Server IP button clicked');
            var serverIp = $(this).data('ip');
            console.log('Server IP from data attribute:', serverIp);
            if (serverIp) {
                addIpToWhitelist(serverIp);
            }
        });
    });
})(jQuery);
