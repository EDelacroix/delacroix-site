<?php
/**
 * Aiguillage de l’application (routage)
 */
declare(strict_types=1);

// where am I ?
$home_dir = __DIR__ . '/';

// load the master class
require_once($home_dir . "Delacroix.php");

use Oeuvres\Kit\{Route,I18n};

// Titre par défaut des pages
I18n::put('title', 'Delacroix');
// register the template in which include content
Route::template($home_dir . 'template.php');

// transformation Odette pour document odt

// welcome page
Route::get('/', $home_dir . 'pages/accueil.html');


// try if a php content is available
Route::get('/(.*)', $home_dir . 'pages/$1.php'); 
// try if an html content is available
Route::get('/(.*)', $home_dir . 'pages/$1.html');
// une lettre
Route::get('/(CAC_ED.*)', $home_dir . 'pages/lettre.php', array('id' => '$1'));
// catch all
Route::route('/404', $home_dir . 'pages/404.html');
// No Route has worked
echo "Bad routage, 404.";