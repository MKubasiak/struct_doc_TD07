<?php

class td7{

    private $QUERY_TITLE    =   "//article/header/h1[@class='post-title']";
    private $QUERY_NAME     =   "//div[@class='ob-comment']/p[@class='ob-info']/span[@class='ob-user']/span[@class='ob-name']/a";
    private $QUERY_DATE     =   "//div[@class='ob-comment']/p[@class='ob-info']/span[@class='ob-user']/span[@class='ob-date']";
    private $QUERY_COMMENT  =   "//div[@class='ob-comment']";
    private $QUERY_URLS     =   "/html/body/div[4]/div[2]/section[1]/article/div/header//a/@href";
    public $i;

    //on stocke pour chacun des commentaires le titre/le nom/ la date/ le commentaire et l'url. On sait que on gardera l'ordre des commenaires dans les tableaux.
    public $tab_title           =   array();
    public $tab_name            =   array();
    public $tab_date            =   array();
    public $tab_comment         =   array();
    public $tab_article_link    =   array();


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
     * Get article title
     * @param $domdoc
     */
    public function getTitle($domdoc){
        $domdoc->preserveWhiteSpace = false;
        $xpath = new DOMXPath($domdoc);
        $entries = $xpath->query($this->QUERY_TITLE);
        foreach($entries as $entry){
            array_push($this->tab_title, $entry->nodeValue);
        }
    }

    /**
     * Get names from comments
     * @param $domdoc
     */
    public function getName($domdoc){
        $domdoc->preserveWhiteSpace = false;
        $xpath = new DOMXPath($domdoc);
        $entries = $xpath->query($this->QUERY_NAME);
     //   var_dump($entries);
        $this->i = 0;
        foreach($entries as $entry){
            array_push($this->tab_name, $entry->nodeValue);
            $this->i++;
        }
    }

    /**
     * Get dates from comments
     * @param $domdoc
     */
    public function getDate($domdoc){
        $domdoc->preserveWhiteSpace = false;
        $xpath = new DOMXPath($domdoc);
        $entries = $xpath->query($this->QUERY_DATE);
        foreach($entries as $entry){
            array_push($this->tab_date, $entry->nodeValue);
        }
    }

    /**
     * Get texts from comments
     * @param $domdoc
     */
    public function getComments($domdoc){
        $domdoc->preserveWhiteSpace = false;
        $xpath = new DOMXPath($domdoc);
        $entries = $xpath->query($this->QUERY_COMMENT);
        foreach($entries as $entry){
            array_push($this->tab_comment, $entry->nodeValue);
        }
    }

    public function setTabArticleLink($val){
        array_push($this->tab_article_link, $val->nodeValue);
    }


    public function headerRss(){
        $rssfeed = '<?xml version="1.0" encoding="UTF-8"?>';
        $rssfeed .= '<rss version="2.0">';
        $rssfeed .= '<channel>';
        $rssfeed .= '<title>Comments RSS FEED</title>';
        $rssfeed .= '<description>Those are the comments of your favorite blog</description>';
        $rssfeed .= '<language>fr-fr</language>';
        return $rssfeed;
    }

    public function formatComment($index){
        $rssfeed    = '<item>';
        $rssfeed    .= '<title>' . $this->tab_title[$index] . '</title>';
        $rssfeed    .= '<description>' . $this->tab_comment[$index] . '</description>';
        $rssfeed    .= '<link>' . $this->tab_article_link[$index] . '</link>';
        $rssfeed    .= '<author>' . $this->tab_name[$index] . '</author>';
        $rssfeed    .= '<pubDate>' . $this->tab_date[$index] . '</pubDate>';
        $rssfeed    .= '</item>';
        return $rssfeed;
    }

    public function footerRss(){
        $rssfeed = '</channel>';
        $rssfeed .= '</rss>';
        return $rssfeed;
    }

    public function generateRss(){
        $rssFeed = $this->headerRss();
        for($i = 0; $i<=count($this->tab_date);$i++){
            $rssFeed .= $this->formatComment($i);
        }
        $rssFeed .= $this->footerRss();
        return $rssFeed;
    }

}

$td7 = new td7();
$html = $td7->getHtml("http://www.grelinettecassolettes.com/");
$dom = $td7->htmlToTree($html);
$urls = $td7->getUrls($dom);
foreach ($urls as $entry) {
    //on sait que l'url de l'article correspondant au commentaire est $entry->nodeValue
    $html2 = $td7->getHtml($entry->nodeValue);
    $domdoc2 = $td7->htmlToTree($html2);
    $td7->getName($domdoc2);
    $td7->getComments($domdoc2);
    $td7->getDate($domdoc2);
    $td7->getTitle($domdoc2);
    for($j=0;$j<=$td7->i;$j++){
        $td7->setTabArticleLink($entry->nodeValue);
    }
}

echo $td7->generateRss();






