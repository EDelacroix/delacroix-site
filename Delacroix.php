<?php
/**
 * %LICENCE%
 */

include_once(__DIR__ . '/php/autoload.php');

use Psr\Log\{LogLevel};
use Oeuvres\Kit\{File, LoggerCli, Xml};

Delacroix::init();
class Delacroix
{
    /** Needed extensions for this app */
    static $extensions = array('pdo_sqlite', 'xsl');
    static public function init()
    {
        // test needed extension
        $mess = array();
        foreach (self::$extensions as $ext) {
            if (!extension_loaded($ext)) {
                $mess[] = "<h1>Installation problem, check your php.ini, needed extension: <b>" . $ext . "</b></h1>";
            }
        }
        if (count($mess) > 0) {
            throw new Exception(implode("\n", $mess));
        }
        mb_internal_encoding("UTF-8");
    }

    /**
     * Transfrom one letter
     */
    static public function elicom_html($tei_file)
    {
        // CLI, remember to set a logger Xml::setLogger()
        $tei_dom = Xml::load($tei_file);
        $xsl_file = __DIR__ . '/theme/elicom_html.xsl';
        $dst_dir = __DIR__ . '/lettres/';
        $dst_file = $dst_dir . pathinfo($tei_file, PATHINFO_FILENAME) . '.html';
        Xml::transformToUri(
            $xsl_file,
            $tei_dom,
            $dst_file
        );
    }

    public static function readme()
    {
        include(dirname(dirname(__FILE__)) . '/teinte/teidoc.php');
        $readme = "
# Delacroix, des lettres

Liens vers les fichiers XML/TEI. En cliquant, un texte devrait vous apparaître 
sans balises et proprement mis en page, avec une transformatoin XSLT à la volée
qui se fait dans le navigateur.

| De   | À    | Date | XML/TEI |
| :--- | :--- | ---: | ------: | 
";
        $glob = dirname(__FILE__) . "/*.xml";
        $i = 1;
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->substituteEntities = true;

        foreach (glob($glob) as $srcfile) {
            $name = pathinfo($srcfile,  PATHINFO_FILENAME);

            $dom->load($srcfile, LIBXML_NOENT | LIBXML_NONET | LIBXML_NSCLEAN | LIBXML_NOCDATA | LIBXML_NOWARNING);
            $xpath = new DOMXpath($dom);
            $xpath->registerNamespace('tei', "http://www.tei-c.org/ns/1.0");
            $de = "???";
            $nl = $xpath->query("//tei:correspAction[@type='sent']/tei:persName");
            foreach ($nl as $node) {
                $value = $node->getAttribute('key');
                if (!$value) break;
                list($value) = explode("(", $value);
                $de = trim($value);
                break;
            }
            $date = "???";
            $nl = $xpath->query("//tei:correspAction[@type='sent']/tei:date");
            foreach ($nl as $node) {
                $value = $node->getAttribute('when');
                if (!$value) break;
                $date = $value;
                break;
            }
            $a = "???";
            $nl = $xpath->query("//tei:correspAction[@type='received']/tei:persName");
            foreach ($nl as $node) {
                $value = $node->getAttribute('key');
                if (!$value) break;
                list($value) = explode("(", $value);
                $a = trim($value);
                break;
            }

            // $readme .= "|$i.";
            $readme .= "|$de";
            $readme .= "|$a";
            $readme .= "|$date";
            $readme .= "|$name";
            $readme .= "|\n";
            $i++;
        }
        return $readme;
    }

    static public function build()
    {
        global $argv;
        $count = count($argv); 
        if ($count < 2) exit("
php build.php \"../delacroix-xml/*.xml\"
cf. https://github.com/EDelacroix/xml
"       );
        $logger = new LoggerCli(LogLevel::INFO);
        Xml::setLogger($logger);
        $glob = $argv[1];
        foreach (glob($glob) as $tei_file) {
            // tester date ?
            self::elicom_html($tei_file);
        }
    }
}