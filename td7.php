<?php

class td7{




    public function getHtml($url){

        // initialisation de la session
        $ch = curl_init();

        $userAgent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/525.13 (KHTML, like Gecko) Chrome/0.A.B.C Safari/525.13';


        // configuration des options
        curl_setopt($ch, CURLOPT_URL, "http://www.grelinettecassolettes.com/");
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);

        // exécution de la session
        $page = curl_exec($ch);

        // fermeture des ressources
        curl_close($ch);
        return $page;

    }

}






