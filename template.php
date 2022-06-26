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
        <link rel="stylesheet" href="<?= Route::home_href() ?>vendor/teinte.css"/>
        <link rel="stylesheet" href="<?= Route::home_href() ?>theme/delacroix.css"/>
    </head>
    <body class="<?= $folder ?>">
        <div id="page">
            <img alt="Correspondance d’Eugène Delacroix" src="<?= Route::home_href  () ?>theme/delacroix_banniere.png"/>
            <header id="header">
                <a class="home" href="<?= Route::home_href() ?>/.">
                    <em>Correspondance</em>
                    <br/><span>d’Eugène Delacroix</span>
                </a>
                <nav class="tabs">
                    <?= Route::tab('liste', 'Les Lettres') ?>
                    <?= Route::tab('noms', 'Index') ?>
                    <?= Route::tab('apropos', 'À propos') ?>
                </nav>
            </header>
            <div id="content">
                <div class="content">
                    <?php Route::main(); ?>
                    <div id="notebox"></div>
                </div>
            </div>
            <footer id="footer">
                <nav>
                    <a href="remerciement">Remerciements</a>
                    <a href="contact">Contact</a>
                    <a href="credits">Crédits</a>
                    <a href="transcription">Principes de transcription</a>
                    <a href="copyright">©</a>
                </nav>
            </footer>
        </div>
        <script src="<?= Route::home_href() ?>vendor/liser.js"></script>
    </body>
</html>