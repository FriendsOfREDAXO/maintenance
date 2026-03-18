# Changelog

## Version 4.1.0 – 2026-03-18

### Neue Features

- **IP-CIDR-Support**: Die IP-Whitelist unterstützt jetzt CIDR-Bereiche (z.B. `192.168.1.0/24`, `2001:db8::/32`). IPv4 und IPv6 werden vollständig unterstützt. Einzelne IPs und CIDR-Ranges können gemischt, kommagetrennt eingetragen werden.
- **search_it-Integration**: Der search_it-Crawler wird automatisch erkannt und darf das Frontend auch während der Wartung crawlen. Der Crawler-Request wird anhand des Parameters `search_it_build_index` und der Absender-IP (Loopback/Server-IP) identifiziert. Ein manueller IP-Eintrag in der Whitelist ist nicht mehr nötig.

### Bugfixes

- **Cronjob-Whoops behoben**: Der Aufruf von `rex_cronjob_manager::registerType()` wurde aus dem direkten Boot-Kontext in den `PACKAGES_INCLUDED`-Extension-Point verschoben. Damit ist garantiert, dass der Cronjob-Manager bereits geladen ist, und es kommt kein Whoops mehr bei bestimmten Ladeszenarien (z.B. Console-Kontext).

---

## Version 4.0.1

- Bugfixes und Stabilisierung

## Version 4.0.0

- Domain-spezifische Wartung (YRewrite-Integration)
- Backend-Wartungsmodus mit Impersonate-Warnung
- Konsolen-Befehle für alle Modi

## Version 3.5.0

- Zeitgesteuerte Wartung (Cronjob-basiert)
- Silent Mode für Staging/Development
- Mehrsprachige Sperrseite (DE/EN)
- Neue Planungs-Seite im Backend

## Version 3.0.0

- YRewrite-Domain-Ausnahmen
- Wartungsankündigung mit Zeitraum
- Erweiterte Zugriffskontrolle
