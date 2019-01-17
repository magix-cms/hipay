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
 $hipay = new plugins_hipay_public();
 $hipayProcess = $hipay->getData(
     array(
         'plugin'    =>  'myplugincart',
         'key'       =>  $session_key,
         'order'     =>  $id_cart,
         'amount'    =>  $amount,
         'shipping'  =>  $shipping,
         'locale'    =>  'fr',
         'customerEmail'=> 'mymail@myhost.com'
     )
 );
````

 Ressources
 -----
  * https://www.hipay.com
  * https://www.magix-cms.com
  

