# Maintenance-Mode 

![Screenshot](https://raw.githubusercontent.com/FriendsOfREDAXO/maintenance/assets/Maintenance.png)

Das AddOn ermöglicht die Sperrung des Frontends und des Backends bei Wartungsarbeiten oder in der Entwicklungsphase. 

## Funktionen: 
*Frontend-Sperre*
- Umleitung des Frontends zu einer festgelegten URL
- Freigabe des Frontends für hinterlegte IP-Adressen
- Zugang zum Frontend, wenn in REDAXO eingeloggt (auswählbar ob erlauben oder nicht)

*Backend*
- Redakteure können ausgesperrt werden
- Umleitung zu einer festgelegten URL

Der Konfigurationswert "Selfmade" steht zur Verfügung um ggf. selbst eigene Lösungen in Templates und Modulen zu realisieren. 

Beispiel-Code: 
``` php
$addon = rex_addon::get('maintenance');
if ($addon->getConfig('frontend_aktiv') == 'Selfmade') {
// z.B. anderes Template laden, Umleiten zu einer reduzierten Version usw. 
}
```

*Farblegende (auch durch Hover über Maintenance-Symbol ersichtlich)* 
- Rot: Der Modus "Frontend-Sperre" ist aktiv!
- Grün: Der Modus "Eigene Lösung" ist aktiv! 
- Gelb: Der Modus "Backend-Sperre" ist aktiv!



## Search_it und Maintenance-Mode 

Bei aktivierter Sperre fügt man einfach die IP des Servers in den Frontendeinstellungen hinzu, schon kann search_it wieder crawlen. ;-) 

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
