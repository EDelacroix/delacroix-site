<?php
declare(strict_types=1);

require_once(__DIR__ . "/Delacroix.php");

use Oeuvres\Kit\{I18n, Route, Web};

// calculate a body class with source
if ( Route::url_request() == '/') $folder = 'home';
else $folder = basename(dirname(Route::resource()));

?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <title><?= Route::title(I18n::_('title')) ?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com"/>
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
        <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,600;1,400;1,600&display=swap" rel="stylesheet"/>
        <link rel="manifest" href="<?= Route::home_href() ?>delacroix.webmanifest">
        <link rel="stylesheet" href="<?= Route::home_href() ?>vendor/teinte.css"/>
        <link rel="stylesheet" href="<?= Route::home_href() ?>theme/delacroix.css"/>
    </head>
    <body class="<?= $folder ?>">
        <div id="page">
            <img alt="Correspondance d’Eugène Delacroix" src="<?= Route::home_href  () ?>theme/delacroix_banniere.png"/>
            <header id="header">
                <a class="home" href="<?= Route::home_href() ?>.">
                    <em>Correspondance</em>
                    <br/><span>d’Eugène Delacroix</span>
                </a>
                <nav class="tabs">
                    <?= Route::tab('liste', 'Lettres') ?>
                    <?= Route::tab('noms', 'Index') ?>
                    <?= Route::tab('inventaire', 'Inventaire') ?>
                    <?= Route::tab('presentation', 'Présentation') ?>
                </nav>
            </header>
            <div id="content">
                <div class="content">
                    <?php Route::main(); ?>
                    <div id="notebox"></div>
                </div>
            </div>
            <footer id="footer">
                <nav class="logos">
                    <a title="Sorbonne Université" href="https://www.sorbonne-universite.fr/">
                        <img class="logo" src="<?= Route::home_href() ?>theme/logo_sorbonne.png"/>
                    </a>
                    <a  title="Centre André Chastel" href="http://www.centrechastel.paris-sorbonne.fr/">
                        <img class="logo" src="<?= Route::home_href() ?>theme/logo_chastel.png"/>
                    </a>
                    <a title=" ObTIC" href="https://obtic.sorbonne-universite.fr/">
                        <img class="logo" src="<?= Route::home_href() ?>theme/logo_obtic.svg"/>
                    </a>
                    <a title="Huma-Num" href="https://www.huma-num.fr/">
                        <img class="logo" src="<?= Route::home_href() ?>theme/logo_hn.png"/>
                    </a>
                    <a title="Nakala" href="https://nakala.fr/">
                        <img class="logo" src="<?= Route::home_href() ?>theme/logo_nakala.png"/>
                    </a>
                </nav>
            </footer>
        </div>
        <script src="<?= Route::home_href() ?>vendor/liser.js"></script>
        <script src="<?= Route::home_href() ?>theme/delacroix.js"></script>
    </body>
</html>