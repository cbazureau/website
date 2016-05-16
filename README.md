# Introduction
Repository qui contient mon site www.cedric.bazureau.com.
- Site en PHP (light, pas besoin de sortir l'artillerie lourde).
- Utilisation de Gulp/Composer/Bower/Npm

Template basée sur http://html5up.net/

## Installation (Mac OSx)
### Pré-requis 
- Apache/PHP : http://jason.pureconcepts.net/2014/11/install-apache-php-mysql-mac-os-x-yosemite/ (la partie Mysql n'est pas nécessaire)
- Gulp : https://github.com/gulpjs/gulp/blob/master/docs/getting-started.md
- Bower : http://bower.io/
- Node/NPM : https://nodejs.org/

### Gulp
Gulp sert à automatiser la production du livrable :
- Récupération des dépendances composer (alternatif : curl -sS https://getcomposer.org/installer | php; php composer.phar install)
- Récupération des dépendances bower (alternatif : bower install)
- Concatenation et minification des CSS
- Concatenation et uglification des JS
- Génération de noms uniques sur les CSS/JS
- Compression des images

La première fois il suffit de lancer :
```
npm install
gulp build
```
### Optimisation
Le site a été optimisé pour l'ensemble des devices (Responsive design) tout en gardant une bonne note de performance.
![alt tag](https://raw.githubusercontent.com/cbazureau/website/master/www/cedric/src/img/blog/intro-gtmetrix-2.png)



