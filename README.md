# Wartung (Maintenance) für REDAXO 5.x

Das AddOn ermöglicht es Administratoren, das Frontend und/oder des Backend von REDAXO für Besucher und/oder Redakteure zu sperren. Zum Beispiel bei Wartungsarbeiten oder in der Entwicklungsphase.

## Funktionen

### Sperren des Frontends

* Wahl der Authentifizierung: Geheime URL oder Passwort
* Optionales Sperren des Frontends auch für REDAXO-Benutzer (außer Admins)
* Optionale Weiterleitung zu einer festgelegten URL, z.B. REDAXO-Login
* Festlegen des HTTP-Statuscodes (z.B. 503 Service Unavailable)
* **Silent Mode**: Nur HTTP-Status ohne HTML-Content (ideal für Staging/Development)
* Anpassen der Sperrseite durch eigenes Fragment (`maintenance/frontend.php`)
* Definieren von Ausnahmen, die dennoch Zugriff erhalten, z.B. für
  * IP-Adressen (einzelne IPs und CIDR-Bereiche, z.B. `192.168.1.0/24` oder `2001:db8::/32`, IPv4 und IPv6)
  * Hosts
  * YRewrite-Domains (neu in Version 3.0.0)
* **Automatische search_it-Erkennung**: Der search_it-Crawler wird anhand des Absenders automatisch erkannt und darf crawlen (neu in Version 4.1.0)
* Meldung und Zeitraum zur Ankündigung eines Wartungsfensters definieren (neu in Version 3.0.0)
* **Zeitgesteuerte Wartung**: Automatische Aktivierung/Deaktivierung zu festgelegten Zeiten (neu in Version 3.5.0)

### Zeitgesteuerte Wartung

Die **zeitgesteuerte Wartung** ermöglicht es, den Wartungsmodus automatisch zu einem bestimmten Zeitpunkt zu aktivieren und zu deaktivieren:

* **Cronjob-basiert**: Die Ausführung erfolgt ausschließlich über den Cronjob "Geplante Wartung prüfen"
* **Automatische Bereinigung**: Nach erfolgreicher Deaktivierung werden die geplanten Zeiten automatisch gelöscht

**Verwendung:**

1. **Cronjob einrichten** (erforderlich):
   - Im REDAXO-Backend zu **System > Cronjobs** navigieren
   - Auf **"+"** klicken, um einen neuen Cronjob zu erstellen
   - Folgende Einstellungen vornehmen:
     - **Name**: z.B. "Geplante Wartung prüfen"
     - **Typ**: `Geplante Wartung prüfen` (aus Dropdown wählen)
     - **Ausführungsart**: z.B. "Jede Minute" oder "Alle 5 Minuten"
     - **Umgebung**: "Frontend, Backend, Skript"
   - Speichern

2. In den Frontend-Einstellungen unter "Zeitgesteuerte Wartung":
   - **Startzeitpunkt** eingeben (z.B. `2025-12-31 02:00:00`)
   - **Endzeitpunkt** eingeben (z.B. `2025-12-31 06:00:00`)
3. Speichern - der Wartungsmodus wird zur konfigurierten Zeit automatisch aktiviert und deaktiviert

**Format**: `YYYY-MM-DD HH:MM:SS` (z.B. `2025-12-31 23:59:59`)

**Wichtig**: Ohne eingerichteten Cronjob funktioniert die zeitgesteuerte Wartung nicht!

### Sperren des REDAXO-Backends

* Sperren des REDAXO-Backends für alle Benutzer (außer Admins)

### Wartungsmodus ankündigen

Eine Meldung und Zeitraum zur Ankündigung eines Wartungsfensters definieren (neu in Version 3.0.0). Die Ausgabe erfolgt über `FriendsOfRedaxo\Maintenance\Maintenance::getAnnouncement()`, z.B.

```php
$announcement = FriendsOfRedaxo\Maintenance\Maintenance::getAnnouncement();
if($announcement) {
    echo '<div class="alert alert-danger">'.$announcement.'</div>';
}
```

Eine für Nutzer*innen hilfreiche Meldung ist beispielsweise:

> Geplante Wartungsarbeiten am 01.01.2022 von 00:00 bis 06:00 Uhr. In dieser Zeit ist die Website möglicherweise nicht erreichbar.

### Eigene HTML-Seite für den Wartungsmodus

Standardmäßig wird eine einfache HTML-Seite angezeigt, die den Wartungsmodus anzeigt.

Diese kann durch eine eigene HTML-Seite ersetzt werden. Dazu muss im Projekt-AddOn ein Ordner `fragments/maintenance` angelegt werden. In diesem Ordner kann eine Datei `frontend.php` mit eigenem HTML-Code erstellt werden. D.h. `/src/addons/maintenance/fragments/maintenance/frontend.php`

So kann bspw. eigener Text, Logo oder komplett andere Gestaltung erfolgen.

### Silent Mode für Staging/Development-Umgebungen

Der **Silent Mode** ist ideal für Staging-Systeme und Development-Umgebungen, die permanent gesperrt sein sollen:

* Sendet nur den HTTP-Status-Code (z.B. 503 oder 403)
* Zeigt **keine** HTML-Wartungsseite an
* Verhindert Rückschlüsse auf das verwendete CMS
* Perfekt für Produktiv-Vorschau-Systeme, die nur nach Login zugänglich sein sollen

**Aktivierung:** In den erweiterten Einstellungen (Einstellungen) unter "HTTP-Einstellungen" die Option "Silent Mode" aktivieren.

## Anzeige des aktuellen Status im REDAXO-Hauptmenü

Der Menüeintrag erhält bei Aktivierung einer der Wartungsmodi ein zusätzliches Tag.

* Standard: Alle Funktionen sind deaktiviert.
* `F` in rotem Tag: Der Wartungsmodus ist für das Frontend aktiv.
* `B` in blauem Tag: Der Wartungsmodus ist für das Backend aktiv.

## Extensionpoint MAINTENANCE_MEDIA_UNBLOCK_LIST

Über diesen Extension-Point kann ein Array mit Medien übergeben werden, die das Addon nicht sperren soll.

## `search_it` und Wartungs-Modus

Ist die Frontendsperre aktiviert, erkennt das Addon den `search_it`-Crawler automatisch und lässt ihn durch. Der Crawler sendet beim Indexieren den Parameter `search_it_build_index` und wird zusätzlich über die Absender-IP (Loopback oder Server-IP) verifiziert. Ein manueller Eintrag der Server-IP in der Whitelist ist **nicht mehr nötig**.

> **Sicherheitshinweis:** Der Bypass gilt nur für Requests, die vom Server selbst kommen (`127.0.0.1`, `::1` oder `SERVER_ADDR`). Externe Besucher können diesen Mechanismus nicht missbrauchen.

## Konsole

Das Addon bietet verschiedene Konsolen-Befehle zur Verwaltung des Wartungsmodus:

### Status anzeigen

Zeigt den aktuellen Status aller Wartungsmodi an:

```bash
php redaxo/bin/console maintenance:mode status
```

### Frontend-Wartungsmodus

Aktivieren:
```bash
php redaxo/bin/console maintenance:mode frontend on
```

Deaktivieren:
```bash
php redaxo/bin/console maintenance:mode frontend off
```

### Backend-Wartungsmodus

Aktivieren:
```bash
php redaxo/bin/console maintenance:mode backend on
```

Deaktivieren:
```bash
php redaxo/bin/console maintenance:mode backend off
```

### Alle Modi gleichzeitig

Alle Wartungsmodi (Frontend, Backend und alle Domains) aktivieren:
```bash
php redaxo/bin/console maintenance:mode all on
```

Alle Wartungsmodi deaktivieren:
```bash
php redaxo/bin/console maintenance:mode all off
```

### Domain-spezifische Wartung (YRewrite)

Einzelne Domain sperren:
```bash
php redaxo/bin/console maintenance:mode domain example.com --lock
```

Einzelne Domain entsperren:
```bash
php redaxo/bin/console maintenance:mode domain example.com --unlock
```

### Legacy-Unterstützung

Die alten Befehle funktionieren weiterhin (steuern nur den Frontend-Modus):

```bash
php redaxo/bin/console maintenance:mode on
php redaxo/bin/console maintenance:mode off
```

## Autor

[FriendsOfREDAXO](https://github.com/FriendsOfREDAXO)

## Projekt-Lead

* [Simon Krull](https://github.com/crydotsnake)
* [Thomas Skerbis](https://github.com/skerbis)


## Credits

Danke an:

* [Christian Gehrke](https://github.com/chrison94) // first version
* [Joachim Dörr](https://github.com/joachimdoerr)
* [Thorben](https://github.com/eaCe)
* [Oliver Kreischer](https://github.com/olien)
* [Alexander Walther](https://www.alexplus.de)

* u.v.a


