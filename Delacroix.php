<?php
/**
 * %LICENCE%
 */

include_once(__DIR__ . '/php/autoload.php');

use Psr\Log\{LogLevel};
use Oeuvres\Kit\{File, I18n, LoggerCli, LoggerFile, Xml};
use Oeuvres\Odette\{OdtChain};

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
        $BUILDING = __DIR__ . '/BUILDING';
        $BUILD = __DIR__ . '/BUILD';
        if (file_exists($BUILDING));
        else if (file_exists($BUILD));
        else {
            // TODO create new log with BUILDING
            $logger = new LoggerFile($BUILDING, LogLevel::INFO);
            Xml::setLogger($logger);
            $old = array();
            /*
            foreach(array('log_errors', 'error_log') as $key) {
                $old[$key] = ini_get($key);
            }
            ini_set("log_errors", TRUE);
            ini_set('error_log', $BUILDING);
            */
            try {
                $logger->info(I18n::_('title'));
                self::build($logger);
            }
            finally {
                rename(__DIR__ . '/BUILDING', __DIR__ . '/BUILD');
                // restore ini
                foreach($old as $key => $value) {
                    ini_set($key, $value);
                }
            }
        }
        
    }
    static public function build($logger, $force=false)
    {
        Xml::setLogger($logger);
        self::lettres($logger, $force=false);
        self::articles($logger, $force=false);
    }

    /**
     * Loop on odt articles
     */
    static public function articles($logger, $force=false)
    {
        $odt_dir = __DIR__ . '/odt/';
        $dst_dir = __DIR__ . '/html/';
        // nettoyer les restes
        do {
            if (!is_dir($dst_dir)) break;
            $dh = opendir($dst_dir);
            if (!$dh) break;
            $n = 0;
            while (($basename = readdir($dh)) !== false) {
                if (++$n >= 100) break; // infinite loop ?
                $file = $dst_dir . $basename;
                if (is_dir($file)) continue;
                if ($basename[0] == '.' || $basename[0] == '_') continue;
                if (!preg_match('@\.(xml|html)@', $basename)) continue;
                $name = pathinfo($basename, PATHINFO_FILENAME);
                $odt_file = $odt_dir . $name . '.odt';
                if (!file_exists($odt_file)) unlink($dst_dir . $basename);
            }
            closedir($dh);
        } while(false);


        foreach (glob($odt_dir . '*.odt') as $odt_file) {
            $name = pathinfo($odt_file, PATHINFO_FILENAME);
            if (preg_match('@^[._~]@', $name)) continue;
            // freshness ?
            $tei_file = $dst_dir . $name . '.xml';
            $html_file = $dst_dir . $name . '.html';
            if (file_exists($tei_file)
                && filemtime($tei_file) > filemtime($odt_file)
                && file_exists($html_file)
                && filemtime($html_file) > filemtime($odt_file)
            ) {
                continue;
            }

            try {
                $odt = new OdtChain($odt_file);
            } catch (Exception $e) {
                // shall we log something here ?
                continue;
            }
            $odt->save($tei_file);
            $tei_dom = Xml::load($tei_file);
            $tei_dom->preserveWhiteSpace = false;
            $tei_dom->formatOutput = true; // after multiple tests, keep it
            $tei_dom->substituteEntities = true;
            $xsl_file = __DIR__ . '/php/Oeuvres/Teinte/tei_html_article.xsl';
            Xml::transformToUri(
                $xsl_file,
                $tei_dom,
                $html_file
            );
            $logger->info($odt_file . ' ->- ' . $html_file);
        }
    }

    /**
     * Loop on XML files
     */
    static public function lettres($logger, $force=false)
    {
        $dst_index = __DIR__ . "/lettres/index.html";
        // clean old lettre.html
        foreach (glob(__DIR__ . '/lettres/*.html') as $dst_file) {
            if ($dst_file == $dst_index) continue;
            $tei_file = __DIR__ . '/xml/' . pathinfo($dst_file, PATHINFO_FILENAME) . '.xml';
            if (!file_exists($tei_file)) {
                $logger->info('Delete ' . $dst_file);
                unlink($dst_file);
            }
        }
        $xsl_file = __DIR__ . '/theme/elicom_html.xsl'; // test freshness
        $dst_dir = __DIR__ . "/lettres/";

        // test if a new list should be generated
        $done = true;
        foreach (glob(__DIR__ . '/xml/*.xml') as $tei_file) {
            $name = pathinfo($tei_file, PATHINFO_FILENAME);
            $dst_file = $dst_dir . $name . '.html';
            // tester date
            if ( !file_exists($dst_index)
                || !file_exists($dst_file)
                || filemtime($tei_file) > filemtime($dst_index)
                || filemtime($tei_file) > filemtime($xsl_file)
                || filemtime($tei_file) > filemtime($dst_file)
            ) {
                $done = false;
                break;
            }
        }
        if ($done) return;

        // regenerate letter list
        File::mkdir($dst_dir);
        $lettres = fopen( $dst_index, 'w');

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
        self::build($logger);
    }
}