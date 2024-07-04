# Wartungsmodus (Maintenance mode) für REDAXO 5.x

![Screenshot](https://raw.githubusercontent.com/FriendsOfREDAXO/maintenance/assets/Maintenance.png)

Das AddOn ermöglicht es Administratoren, das Frontend und/oder des Backend von REDAXO für Besucher und/oder Redakteure zu sperren. Zum Beispiel bei Wartungsarbeiten oder in der Entwicklungsphase.

## Funktionen

### Sperren des Frontends

* Wahl der Authentifizierung: Geheime URL oder Passwort
* Optionales Sperren des Frontends auch für REDAXO-Benutzer (außer Admins)
* Optionale Weiterleitung zu einer festgelegten URL, z.B. REDAXO-Login
* Festlegen des HTTP-Statuscodes (z.B. 503 Service Unavailable)
* Anpassen der Sperrseite durch eigenes Fragment (`maintenance_page.php`)
* Definieren von Ausnahmen, die dennoch Zugriff erhalten, z.B. für
  * IP-Adressen
  * Hosts
  * YRewrite-Domains (neu in Version 3.0.0)
* Meldung und Zeitraum zur Ankündigung eines Wartungsfensters definieren (neu in Version 3.0.0)

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

Diese kann durch eine eigene HTML-Seite ersetzt werden. Dazu muss im Projekt-AddOn ein Ordner `fragments` angelegt werden. In diesem Ordner kann eine Datei `maintenance_page.php` mit eigenem HTML-Code erstellt werden.

So kann bspw. eigener Text, Logo oder komplett andere Gestaltung erfolgen.

## Anzeige des aktuellen Status im REDAXO-Hauptmenü

Das AddOn-Symbol erhält bei Aktivierung einer der Wartungsmodi eine Farbkennung.

* Standard: Alle Funktionen sind deaktiviert.
* Rot: Der Wartungsmodus ist für Frontend und/oder Backend aktiv!

## Extensionpoint MAINTENANCE_MEDIA_UNBLOCK_LIST

Über diesen Extension-Point kann ein Array mit Medien übergeben werden, die das Addon nicht sperren soll.

## `search_it` und Wartungs-Modus

Ist die Frontendsperre aktiviert, kann `search_it` den Index nicht erstellen.

Dazu einfach die aktuelle IP des Servers, auf dem REDAXO installiert ist und von dem aus gecrawlt wird, als  Ausnahme hinzufügen. Schon kann `search_it` wieder crawlen. 🕵🏻

## Konsole

Es wird die im Backend ausgewählte Sperrseite angezeigt. Aktivieren der Frontendsperre

Mit `maintenance:on` oder `frontend:off`

Deaktivieren mit `maintenance:off` oder `frontend:on`

## Autor

### FriendsOfREDAXO

* <http://www.redaxo.org>
* <https://github.com/FriendsOfREDAXO>

### Projekt-Lead

[KLXM Crossmedia / Thomas Skerbis](https://klxm.de)

## Credits

Danke an:

* [Christian Gehrke](https://github.com/chrison94)
* [Alexander Walther](https://github.com/alxndr-w)

Maintenance basiert auf dem out5-Plugin: Wartungsarbeiten

<https://github.com/FriendsOfREDAXO/out5>

[concedra.de / Oliver Kreischer](http://concedra.de)
