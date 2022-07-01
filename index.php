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


// une lettre
Route::get('/(CAC_ED.*)', $home_dir . 'lettres/$1.html');
// Accueil
Route::get('/', $home_dir . 'html/accueil.html');
// liste des lettres, attention, pas lettres/, ou sinon sert le dossier
Route::get('/liste', $home_dir . 'lettres/index.html');
// page simple (le . exlue par défaut les /)
Route::get('/(.*)', $home_dir . 'html/$1.html');

// article, source odt transformé par Odette et Teinte
Route::get('/articles/(.*)', $home_dir . 'html/$1.html'); 

// catch all
Route::route('/404', $home_dir . 'html/404.html');
// No Route has worked
echo "Bad routage, 404.";
