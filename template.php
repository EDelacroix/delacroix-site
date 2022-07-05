<?php
declare(strict_types=1);

require_once(__DIR__ . "/Delacroix.php");

use Oeuvres\Kit\{I18n, Route, Web};

// calculate a body class with source
if ( Route::$url_request == '/') $folder = 'home';
else $folder = basename(dirname(Route::$resource));

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
                <select id="selfont" style="right: 0; position: absolute;">
                    <option>Police</option>
                    <option value="serif">Serif (défaut)</option>
                    <option value="'Noto Serif Display'">Serif (Noto, Google)</option>
                    <option value="'Source Serif VF'">Serif (Source Serif, Adobe)</option>
                    <option value="'EB Garamond'">Serif (EB Garamond, open)</option>
                    <option value="sans-serif">Sans (défaut)</option>
                    <option value="'Noto Sans Display'">Sans (Noto Sans, Google)</option>
                    <option value="'Open Sans'">Sans (Open Sans, Google)</option>
                </select>
            </header>
            <div id="content">
                <div class="content">
                    <?php Route::main(); ?>
                    <div id="notebox"></div>
                </div>
            </div>
            <footer id="footer">
                <nav>
                    <a href="remerciements">Remerciements</a>
                    <a href="contact">Contact</a>
                    <a href="credits">Crédits</a>
                    <a href="transcription">Principes de transcription</a>
                    <a href="copyright">©</a>
                </nav>
            </footer>
        </div>
        <script src="<?= Route::home_href() ?>vendor/liser.js"></script>
        <script src="<?= Route::home_href() ?>theme/delacroix.js"></script>
    </body>
</html>