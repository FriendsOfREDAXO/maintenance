# Release Notes - Maintenance AddOn 4.0.0-beta1

## Was ist neu?

**Zeitgesteuerte Wartung** – Der Wartungsmodus kann jetzt automatisch zu bestimmten Zeiten aktiviert und deaktiviert werden (nur über Cronjob).

**Silent Mode** – Sendet nur HTTP-Status-Code ohne HTML-Inhalt, ideal für Staging-Umgebungen.

**Planungs-Seite** – Neue Übersichtsseite unter *Maintenance > Frontend > Planung* für zeitgesteuerte Wartung und Wartungsankündigungen.

**Mehrsprachige Sperrseite** – Language-Switcher (DE/EN) mit SessionStorage-Unterstützung.

**Domain-Verwaltung** – YRewrite-Domains können jetzt direkt über *Maintenance > Domains* verwaltet werden (keine manuelle Eingabe mehr nötig).

## Was hat sich geändert?

### UX-Verbesserungen
- Einstellungen reorganisiert: Frontend > Planung, Frontend > Erweitert
- IP-Whitelist vereinfacht: Click-to-Add-Buttons, komma-getrennte Liste
- Sidebar mit Quick-Links und Bypass-URLs (nur für gesperrte Domains)
- Toggle-Buttons für Domain-Verwaltung
- Moderne Card-basierte UI mit Dark Mode Support

### Performance & Code-Qualität
- YRewrite-Check nur einmal pro Request
- Redundante Checks entfernt
- PHP CS Fixer, externe Assets, REDAXO-Standards

## Was ist beim Update zu beachten?

**⚠️ Breaking Change:** Die manuelle Domain-Whitelist (`allowed_yrewrite_domains`) wurde entfernt. Domain-basierte Wartung läuft jetzt ausschließlich über die neue Seite *Maintenance > Domains*.

**Nach dem Update:**

1. **Cache leeren** (Backend > System > Einstellungen)

2. **Cronjob einrichten** (nur für zeitgesteuerte Wartung):
   - System > Cronjobs > Neuen Cronjob erstellen
   - Typ: "Geplante Wartung prüfen"
   - Ausführungsart: "Jede Minute" oder "Alle 5 Minuten"

3. **Domain-Einstellungen prüfen** (falls YRewrite verwendet):
   - Öffnen Sie *Maintenance > Domains*
   - Aktivieren/Deaktivieren Sie Domains per Toggle

Die alte Konfiguration wird automatisch migriert.
