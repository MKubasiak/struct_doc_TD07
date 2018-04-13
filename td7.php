<?php

class td7{

    private $QUERY_TITLE    =   "//article/header/h1[@class='post-title']";
    private $QUERY_NAME     =   "//p[@class='ob-info']/span[@class='ob-user']/span[@class='ob-name']";
    private $QUERY_DATE     =   "//p[@class='ob-info']/span[@class='ob-user']/span[@class='ob-date']";
    private $QUERY_CONTENT  =   "//p[@class='ob-message']/span[@class='ob-text']";
    private $QUERY_COMMENT  =   "//div[@class='ob-comment']";
    private $QUERY_URLS     =   "//h2[@class='post-title']/a/@href";

    /**
     * Get HTML from link
     * @param $url
     * @return mixed
     */
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

    /**
     * Translate HTML into DOMtree
     * @param $html
     * @return DOMDocument
     */
    public function htmlToTree($html){
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($html);
        $doc->saveHTML();
        return $doc;
    }

    /**
     * Get all URL from DOMtree
     * @param $domdoc
     * @return DOMNodeList
     */
    public function getUrls($domdoc){
        $domdoc->preserveWhiteSpace = false;
        $xpath = new DOMXPath($domdoc);
        $entries = $xpath->query($this->QUERY_URLS);
        return $entries;
    }

    /**
     * Create the header of the RSS
     * @return string
     */
    public function headerRss(){
        $rssfeed = '<?xml version="1.0" encoding="UTF-8"?>';
        $rssfeed .= '<rss version="2.0">';
        $rssfeed .= '<channel>';
        $rssfeed .= '<title>Comments RSS FEED</title>';
        $rssfeed .= '<description>Those are the comments of your favorite blog</description>';
        $rssfeed .= '<language>fr-fr</language>';
        return $rssfeed;
    }

    /**
     * Create the content of the RSS
     * @param $title
     * @param $content
     * @param $url
     * @param $author
     * @param $date
     * @return string
     */
    public function formatComment($title, $content, $url, $author, $date){
        $rssfeed    = '<item>';
        $rssfeed    .= '<title>' . $title . '</title>';
        $rssfeed    .= '<description>' . $content . '</description>';
        $rssfeed    .= '<link>' . $url . '</link>';
        $rssfeed    .= '<author>' . $author . '</author>';
        $rssfeed    .= '<pubDate>' . $date . '</pubDate>';
        $rssfeed    .= '</item>';
        return $rssfeed;
    }

    /**
     * Create the footer of the RSS
     * @return string
     */
    public function footerRss(){
        $rssfeed = '</channel>';
        $rssfeed .= '</rss>';
        return $rssfeed;
    }

    /**
     * Generate the RSS
     */
    public function generateRss(){
        $rssFeed = $this->headerRss();
        $rssFeed .= $this->getData();
        $rssFeed .= $this->footerRss();
        $fp = fopen("fluxRss.xml", 'w+');
        fputs($fp, $rssFeed);
        fclose($fp);
    }

    /**
     * Get data from DOMtree
     * @return string
     */
    public function getData(){
        $html = $this->getHtml("http://www.grelinettecassolettes.com/");
        $dom = $this->htmlToTree($html);
        $urls = $this->getUrls($dom);
        $rssFeed="";
        foreach ($urls as $entry) {
            $html2 = $this->getHtml($entry->nodeValue);
            $domdoc = $this->htmlToTree($html2);

            $xpath = new DOMXPath($domdoc);
            $entries = $xpath->query($this->QUERY_COMMENT);
            $i=0;

            if($entries->length > 0) {
                foreach ($entries as $comment) {
                    $title = $xpath->query($this->QUERY_TITLE)->item(0)->nodeValue;
                    $url = $entry->nodeValue;
                    $content = $xpath->query($this->QUERY_CONTENT, $comment)->item($i)->nodeValue;
                    $author = $xpath->query($this->QUERY_NAME, $comment)->item($i)->nodeValue;
                    $date = $xpath->query($this->QUERY_DATE, $comment)->item($i)->nodeValue;
                    $rssFeed.=$this->formatComment($title, $content, $url, $author, $date);
                    $i++;
                }
            }
        }
        return $rssFeed;
    }
}

$td7 = new td7();
$td7->generateRss();






