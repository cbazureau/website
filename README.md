# Introduction
Repository qui contient mon site www.cedric.bazureau.com.
Site en PHP (light, pas besoin de sortir l'artillerie lourde).
Listes des modules utilisés (cf. composer.json) 
- Twig
....

Template basée sur http://html5up.net/

## Installation (Mac OSx)
### Pré-requis 
Apache/PHP : http://jason.pureconcepts.net/2014/11/install-apache-php-mysql-mac-os-x-yosemite/ (la partie Mysql n'est pas nécessaire)
Gulp : https://github.com/gulpjs/gulp/blob/master/docs/getting-started.md  
Node/NPM : https://nodejs.org/

### Récupération des dépendances composer
Récupération du composer.phar
```
curl -sS https://getcomposer.org/installer | php
```
Installation des dépendances composer
```
php composer.phar install
```

### Gulp
Gulp sert à automatiser la production du livrable :
- Concatenation et minification des CSS
- Concatenation et uglification des JS
- Génération de noms uniques sur les CSS/JS
- Compression des images
La première fois lancer :
```
npm install
```
Puis à chaque build
```
gulp build
```


