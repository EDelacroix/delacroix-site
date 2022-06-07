<?php
/**
 * %LICENCE%
 */

include_once(__DIR__ . '/php/autoload.php');

use Psr\Log\{LogLevel};
use Oeuvres\Kit\{File, I18n, LoggerCli, LoggerFile, Xml};

Delacroix::init();
class Delacroix
{
    /** Needed extensions for this app */
    static $extensions = array('pdo_sqlite', 'xsl');
    /**
     * Initialize static fields, check some things
     */
    static public function init()
    {
        mb_internal_encoding("UTF-8");
        // for debug on install
        // self::deps('mbstring', 'pdo_sqlite', 'xsl');
    }

    /**
     * Test needed extensions
     */
    function deps(...$extensions) {
        // test needed extension, should be only neede
        $mess = array();
        foreach ($extensions as $ext) {
            if (!extension_loaded($ext)) {
                $mess[] = "<h1>Installation problem, check your php.ini, needed extension: <b>" . $ext . "</b></h1>";
            }
        }
        if (count($mess) > 0) {
            throw new Exception(implode("\n", $mess));
            exit();
        }
    }

    /**
     * Test if content should be updated
     */
    static public function update()
    {
        // check if we should build
        if (file_exists(__DIR__ . '/BUILDING'));
        else if (file_exists(__DIR__ . '/BUILD'));
        else {
            // TODO create new log with BUILDING
            $logger = new LoggerFile(__DIR__ . '/BUILDING', LogLevel::INFO);
            try {
                $logger->info(I18n::_('title'));
                self::build($logger);
            }
            finally {
                rename(__DIR__ . '/BUILDING', __DIR__ . '/BUILD');
            }
        }
        
    }
    static public function build($logger, $force=false)
    {
        // clean old lettre.html
        foreach (glob(__DIR__ . '/lettres/*.html') as $dst_file) {
            $tei_file = __DIR__ . '/xml/' . pathinfo($dst_file, PATHINFO_FILENAME) . '.xml';
            if (!file_exists($tei_file)) unlink($dst_file);
        }
        $xsl_file = __DIR__ . '/theme/elicom_html.xsl'; // test freshness

        // regenerate letter list
        $dst_dir = __DIR__ . "/lettres/";
        mkdir($dst_dir);
        $lettres = fopen( $dst_dir . "index.html", 'w');

        fwrite($lettres, "<article class=\"lettres\">
    <h1>Liste des lettres</h1>
    <nav class=\"lettres\">
");
        // loop on xml files
        foreach (glob(__DIR__ . '/xml/*.xml') as $tei_file) {
            $name = pathinfo($tei_file, PATHINFO_FILENAME);
            $dst_file = $dst_dir . $name . '.html';
            $todo = false;
            // temp before database, load dom here to get meta
            $tei_dom = Xml::load($tei_file);
            $meta = self::meta($tei_dom);
            $html = "<a href=\"" . $name . "\">";
            if (isset($meta['date'])) $html .= $meta['date'] . ', ';
            if (isset($meta['sender'])) $html .= 'de ' . $meta['sender'];
            if (isset($meta['receiver'])) $html .= ', à ' . $meta['receiver'];
            $html .= "</a>\n";
            fwrite($lettres, $html);

            // tester date
            if (!file_exists($dst_file)) $todo=true;
            else if (filemtime($dst_file) < filemtime($tei_file)) $todo = true;
            else if (filemtime($dst_file) < filemtime($xsl_file)) $todo = true;
            if (!$force && !$todo) continue;
            self::elicom_html($tei_dom, $dst_file);
            $logger->info($tei_file . ' ->- ' . $dst_file);

        }
        fwrite($lettres, "
    </nav>
</article>
");
        fclose($lettres);

    }

    static public function meta($tei_dom)
    {
        // return meta
        $meta = array();
        $xpath = new DOMXpath($tei_dom);
        $xpath->registerNamespace('tei', "http://www.tei-c.org/ns/1.0");

        $nl = $xpath->query("//tei:correspAction[@type='sent']/tei:persName");
        foreach ($nl as $node) {
            $value = $node->getAttribute('key');
            if (!$value) break;
            list($value) = explode("(", $value);
            $meta['sender'] = trim($value);
            break;
        }
        $nl = $xpath->query("//tei:correspAction[@type='sent']/tei:date");
        foreach ($nl as $node) {
            $value = $node->getAttribute('when');
            if (!$value) break;
            $meta['date'] = $value;
            break;
        }
        $nl = $xpath->query("//tei:correspAction[@type='received']/tei:persName");
        foreach ($nl as $node) {
            $value = $node->getAttribute('key');
            if (!$value) break;
            list($value) = explode("(", $value);
            $meta['receiver'] = trim($value);
            break;
        }
        return $meta;
 
    }

    /**
     * Transfrom one letter
     */
    static public function elicom_html($tei_dom, $dst_file)
    {
        $xsl_file = __DIR__ . '/theme/elicom_html.xsl';
        Xml::transformToUri(
            $xsl_file,
            $tei_dom,
            $dst_file
        );

   }

    static public function cli()
    {
        global $argv;
        $logger = new LoggerCli(LogLevel::INFO);
        Xml::setLogger($logger);
        self::build($logger);
    }
}