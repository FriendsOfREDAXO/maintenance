# Maintenance mode / Wartungsmodus

![Screenshot](https://raw.githubusercontent.com/FriendsOfREDAXO/maintenance/assets/Maintenance.png)

Das AddOn ermöglicht die Sperrung des Frontends und/oder des Backends bei Wartungsarbeiten oder in der Entwicklungsphase. Die Sperrung kann wahlweise über einen speziellen Link oder Passworteingabe aufgehoben werden. 

## Funktionen:

### Frontend-Sperre
- Umleitung des Frontends zu einer festgelegten URL
- Freigabe des Frontends für hinterlegte IP-Adressen
- Freigabe des Frontends durch ein Passwort (Aufruf über eine geheime URL oder eine Passworteingabe)
- Zugang zum Frontend, wenn in REDAXO eingeloggt (auswählbar ob erlauben oder nicht)
- Wird keine URL eingegeben, wird eine gestaltete Maintenance-Seite ausgegeben. Diese kann durch ein eigenes Fragment überschrieben werden. 

### Backend
- Redakteure können ausgesperrt werden
- Umleitung zu einer festgelegten URL
- Wird keine URL eingegeben, wird eine gestaltete Maintenance-Seite ausgegeben. Diese kann durch ein eigenes Fragment überschrieben werden. 

Der Konfigurationswert "Nur Config-Wert setzen" steht zur Verfügung um ggf. selbst eigene Lösungen in Templates und Modulen zu realisieren. Es wird nur ein Config-Wert erstellt. Alle weiteren Angaben entfallen.  

### Eigene Maintenance-Seiten

Will man keine Umleitung einrichten und stattdessen eine gestaltete Seite anzeigen kann man das Fragment überschreiben und so eine individuelle Info hinterlegen.  

Hierzu einfach im Project-AddOn einen Ordner `fragments` erstellen und eine Datei `maintenance_page.php`, `maintenance_page_pw_form.php` (Mit Passworteingabe)  oder `maintenance_page_be.php`(für die Backend-Sperrung) mit eigenem Text, Logo oder komplett anderer Gestaltung anlegen. 

## Anzeige des aktuellen Status im REDAXO-Hauptmenü
Das AddOn-Symbol erhält je nach Status eine andere Farbe. Durch Mouse-Over auf dem Symbol erhält man den passenden Text (title-attribut). 

### Farblegende
- Standard: Alle Funktionen sind deaktiviert. 
- Rot: Der Modus "Frontend-Sperre" ist aktiv!
- Gelb: Der Modus "Backend-Sperre" ist aktiv!
- Orange: Der Modus "Eigene Lösung" ist aktiv! 

## Search_it und Maintenance-Mode 

Ist die Frontendsperre aktiviert, kann Search_it den Index nicht erstellen. 
Bei aktivierter Sperre fügt man einfach die IP des Servers in den Frontendeinstellungen hinzu, schon kann search_it wieder crawlen. ;-) 

## Konsole

Es wird die im Backend ausgewählte Sperrseite angezeigt. 
Aktivieren der Frontendsperre 

Mit `maintenance:on` oder `frontend:off`

Deaktivieren mit `maintenance:off` oder `frontend:on`


## Autor

**Friends Of REDAXO**

* http://www.redaxo.org
* https://github.com/FriendsOfREDAXO

**Projekt-Lead**

[KLXM Crossmedia / Thomas Skerbis](https://klxm.de)

## Credits
Danke an: 
[Christian Gehrke](https://github.com/chrison94)

**Ursprung**

Basiert auf out5-Plugin: Wartungsarbeiten 

https://github.com/FriendsOfREDAXO/out5

[concedra.de / Oliver Kreischer](http://concedra.de)

