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
I18n::put('title', 'Correspondance Delacroix');
// register the template in which include content
Route::template($home_dir . 'template.php');

// avant de servir des lettres s’assurer que c’est à jour
Delacroix::update();

// welcome page
Route::get('/', $home_dir . 'lettres/index.html');
Route::get('/liste', $home_dir . 'lettres/index.html');
// article, source odt transformé par Odette et Teinte
Route::get('/articles/(.*)', $home_dir . 'articles/$1.html'); 


// try if a php content is available
Route::get('/(.*)', $home_dir . 'pages/$1.php'); 
// try if an html content is available
Route::get('/(.*)', $home_dir . 'pages/$1.html');

// une lettre
Route::get('/(CAC_ED.*)', $home_dir . 'lettres/$1.html');
// catch all
Route::route('/404', $home_dir . 'pages/404.html');
// No Route has worked
echo "Bad routage, 404.";