# hipay
Plugin Hipay for [magixcms 3](https://www.magix-cms.com)

![hipay-logo](https://user-images.githubusercontent.com/356674/51309535-eeeb3100-1a44-11e9-96b1-7ca3833cb1a3.png)

###version 

[![release](https://img.shields.io/github/release/magix-cms/hipay.svg)](https://github.com/magix-cms/hipay/releases/latest)

Authors
-------

* Gerits Aurelien (aurelien[at]magix-cms[point]com)

## Description
Ce plugin est dédié a Magix CMS et travail avec Hipay Wallet et Hipay Direct.

## Installation
 * Décompresser l'archive dans le dossier "plugins" de magix cms
 * Connectez-vous dans l'administration de votre site internet
 * Cliquer sur l'onglet plugins du menu déroulant pour sélectionner Hipay.
 * Une fois dans le plugin, laisser faire l'auto installation
 * Il ne reste que la configuration du plugin pour correspondre avec vos données.
 
 Requirements
   ------------
   * SOAP (http://php.net/manual/en/book.soap.php)
   * CURL (http://php.net/manual/en/book.curl.php)
   
 ####Exemple d'utilisation dans votre panier
 ```php
 Uniquement par POST vers le plugin Hipay !!
 //## Donnée obligatoire pour Hipay
 $purchase = array('purchase'=> array('amount'=>'15.2','email'=>'mymail@mail.com'));
 //## Donnée supplémentaire
 $custom = array('custom'=>array(
    'mydata1'=>'lorem ipsum'
 ));
````
####Exemple de formulaire :
```smarty
<form method="post" action="{$url}/{$lang}/hipay/">
    <input type="hidden" name="purchase[amount]" value="{$price|number_format:2:',':'&thinsp;'}" />
    <input type="hidden" name="purchase[email]" value="mymail@mail.tld"/>
    <input type="hidden" name="callback" value="myplugin"/>
    <input type="hidden" name="redirect" value="myplugin"/>
    <input type="hidden" name="custom[mydata1]" value="lorem ipsum"/>
</form>
````
 Ressources
 -----
  * https://www.hipay.com
  * https://www.magix-cms.com
  

