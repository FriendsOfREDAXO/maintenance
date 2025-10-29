# Release Notes - Maintenance AddOn 3.5.0

## 🎉 Neue Features

### Zeitgesteuerte Wartung
Automatische Aktivierung und Deaktivierung des Wartungsmodus zu festgelegten Zeiten.

**Wichtig:** Funktioniert ausschließlich über Cronjob!

**Einrichtung:**
1. System > Cronjobs > Neuen Cronjob erstellen
2. Typ: "Geplante Wartung prüfen" auswählen
3. Ausführungsart: z.B. "Jede Minute" oder "Alle 5 Minuten"
4. Maintenance > Frontend > Planung: Start- und Endzeitpunkt eingeben

### Neue Planungs-Seite
Unter **Maintenance > Frontend > Planung** finden Sie:
- Zeitgesteuerte Wartung mit Cronjob-Status-Anzeige
- Wartungsankündigung mit Editor-Unterstützung
- Code-Beispiele für die Template-Integration
- Quick-Links zur Navigation

### Silent Mode
Sendet nur HTTP-Status-Code ohne HTML-Inhalt - ideal für Staging-Umgebungen.

**Aktivierung:** Maintenance > Frontend > Einstellungen > Silent Mode

### Mehrsprachige Sperrseite
- Language-Switcher (DE/EN) in der oberen rechten Ecke
- Domain-Anzeige unter der Überschrift
- Responsive Layout (max-width: 450px)
- SessionStorage speichert Sprachpräferenz

### Domain-Verwaltung vereinfacht
Domain-basierte Wartung jetzt ausschließlich über **Maintenance > Domains**
- Toggle-Buttons für schnelles Aktivieren/Deaktivieren
- Übersichtliche Tabelle mit allen YRewrite-Domains

## 🔧 Verbesserungen

### Performance
- YRewrite-Check nur einmal pro Request (statt mehrfach)
- Silent Mode Early Exit (ohne HTML-Rendering)
- Redundante Checks entfernt

### UI/UX
- IP-Whitelist vereinfacht (ohne Bootstrap Tokenfield)
- Click-to-Add-Buttons für IP-Adressen
- Komma-getrennte IP-Listen
- Sidebar optimiert

### Code-Qualität
- PHP CS Fixer durchgängig angewendet
- Inline-Assets in externe Dateien ausgelagert
- Cronjob nach REDAXO-Standard implementiert

## 🐛 Bugfixes

- Frontend-Sperre funktioniert wieder (#156)
- IP Click-to-Add repariert
- rex_i18n-Fehler im Frontend-Fragment behoben
- Undefined Variable $currentPage behoben
- Authentifizierungs-Typo korrigiert
- Language-Button zeigt korrekte Sprache
- Editor-Einstellung wird berücksichtigt

## ⚠️ Breaking Changes

**Manuelle Domain-Whitelist entfernt**

Die Konfiguration `allowed_yrewrite_domains` existiert nicht mehr. 

**Migration:** 
- Domain-basierte Wartung jetzt ausschließlich über **Maintenance > Domains**
- Keine manuelle Eingabe von Domains mehr nötig
- Alle YRewrite-Domains werden automatisch erkannt

**Vorteil:** Vereinfachte Konfiguration und keine Inkonsistenzen mehr!

## 📦 Update-Anleitung

### Schritt 1: AddOn aktualisieren
Installieren Sie die neue Version über den Installer.

### Schritt 2: Cache leeren
Backend > System > Einstellungen > Cache löschen

### Schritt 3: Cronjob einrichten (optional)
Nur erforderlich, wenn Sie die **zeitgesteuerte Wartung** nutzen möchten:

1. System > Cronjobs
2. Neuen Cronjob erstellen
3. Typ: "Geplante Wartung prüfen"
4. Ausführungsart: "Jede Minute" oder "Alle 5 Minuten"
5. Umgebung: "Frontend, Backend, Skript"
6. Speichern

### Schritt 4: Domain-Einstellungen prüfen
Falls Sie zuvor manuelle Domain-Whitelists genutzt haben:

1. Öffnen Sie **Maintenance > Domains**
2. Aktivieren/Deaktivieren Sie die gewünschten Domains per Toggle
3. Die alte Konfiguration wird ignoriert

## 🎯 Neue Konfigurationswerte

Werden automatisch gesetzt:
```yaml
silent_mode: false
scheduled_start: ''
scheduled_end: ''
```

## 📚 Verwendung

### Zeitgesteuerte Wartung
```yaml
# In Maintenance > Frontend > Planung:
scheduled_start: 2025-12-31 02:00:00
scheduled_end: 2025-12-31 06:00:00
```

### Wartungsankündigung im Template
```php
<?php
use FriendsOfREDAXO\Maintenance\Maintenance;
Maintenance::showAnnouncement();
?>
```

## 🔍 Getestet mit

- ✅ REDAXO 5.17.0+
- ✅ PHP 8.2+
- ✅ YRewrite (optional)
- ✅ Mit/ohne Mehrsprachigkeit
- ✅ Desktop & Mobile
- ✅ Light & Dark Mode

## 🙏 Danke

Vielen Dank an:
- Alle Reviewer von PR #156
- Das Upkeep AddOn für die IP-Whitelist-Inspiration
- Die Community für wertvolles Feedback

---

**Bei Fragen oder Problemen:** https://github.com/FriendsOfREDAXO/maintenance/issues
