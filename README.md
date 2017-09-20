# Maintenance-Mode 

![Screenshot](https://raw.githubusercontent.com/FriendsOfREDAXO/maintenance/assets/Maintenance.png)

Das AddOn ermöglicht die Sperrung des Frontends und des Backends bei Wartungsarbeiten oder in der Entwicklungsphase. 

## Funktionen: 
*Frontend-Sperre*
- Umleitung des Frontends zu einer festgelegten URL
- Freigabe des Frontends für hinterlegte IP-Adressen
- Zugang zum Frontend, wenn in REDAXO eingeloggt

*Backend*
- Redakteure können ausgesperrt werden
- Umleitung zu einer festgelegten URL

Die Konfigurationswert "Variable" steht zur Verfügung um ggf. selbst eigene Lösungen in Templates und Modulen zu realisieren. 

Beispiel-Code: 
``` php
$addon = rex_addon::get('maintenance');
if ($addon->getConfig('frontend_aktiv') == 'Variable') {
// z.B. anderes Template laden, Umleiten zu einer reduzierten Version usw. 
}
```

## Search_it und Maintenance-Mode 

Bei aktivierter Sperre fügt man einfach die IP des Servers hinzu, schon kann search_it wieder crawlen. ;-) 

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
