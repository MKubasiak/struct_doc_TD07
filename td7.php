<?php

class td7{

    public function getHtml($url){

        // initialisation de la session
        $ch = curl_init();

        $userAgent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';


        // configuration des options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // exécution de la session
        $page = curl_exec($ch);

        // fermeture des ressources
        curl_close($ch);
        return $page;

    }

    public function htmlToTree($html){
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        $doc->saveHTML();
        return $doc;
    }

    public function getUrls($domdoc){
        $domdoc->preserveWhiteSpace = false;
        $xpath = new DOMXPath($domdoc);
        $query = "/html/body/div[4]/div[2]/section[1]/article/div/header//a/@href";
        $entries = $xpath->query($query);
        return $entries;
    }

    public function getComments($domdoc){
        $domdoc->preserveWhiteSpace = false;
        $xpath = new DOMXPath($domdoc);
        $query = "//div[class='ob-comment']";
        $entries = $xpath->query($query);
        return $entries;
    }

}

$td7 = new td7();
$html = $td7->getHtml("http://www.grelinettecassolettes.com/");
$dom = $td7->htmlToTree($html);
$urls = $td7->getUrls($dom);
foreach ($urls as $entry) {
        $html2 = $td7->getHtml($entry->nodeValue);
        $domdoc2 = $td7->htmlToTree($html2);
        $comments = $td7->getComments($domdoc2);
        foreach($comments as $comment){
            echo "commentaire : {$comment->nodeValue}\n";
        }


}






