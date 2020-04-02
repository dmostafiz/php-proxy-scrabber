<?php
namespace Mostafiz\Scrabber;

class proxyScrabber{

    private $proxies;
    private $uname;
    private $pwd;
    private $text;

    public function __construct($text, $proxies = array(), $username,  $password)
    {
        $this->text = $text;
        $this->proxies = $proxies; 
        $this->uname = $username;
        $this->pwd = $password;
    }
    
    public function getResult()
    {
        $text = $this->text;
        $proxies = $this->proxies; 
        $proxyUsername = $this->uname; 
        $proxyPassword = $this->pwd; 
        // Find a randm proxy from the array
        $proxy = $proxies[ array_rand($proxies) ];   
        // make seperate proxy IP and Port
        $proxyPortArray = explode(':', $proxy);
        //IP
        $proxyIP = $proxyPortArray[0];
        //Port
        $proxyPort = $proxyPortArray[1];
        $text = urlencode($text);
        //Google search url with data
        $url = "https://www.google.com/search?q=".$text;
        
        $ch = curl_init($url);
        // Set any other cURL options that are required
        curl_setopt($ch, CURLOPT_PROXY, $proxy);   
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL , 1);
        
        //Set the proxy IP.
        curl_setopt($ch, CURLOPT_PROXY, $proxyIP);
        //Set the port.
        curl_setopt($ch, CURLOPT_PROXYPORT, $proxyPort);
        //Specify the username and password.
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, "$proxyUsername:$proxyPassword");
        //Execute the request.
        $search = curl_exec($ch);
        //close the CURL
        curl_close($ch); 
        
        $html = str_get_html($search);
        
        $contentTitle = [];
        $contentLink = [];
        $contentText = [];
        $percent = [];
        
        // Its presents all title in a array
        foreach($html->find('a > div.vvjwJb') as $result)
        {
            $contentTitle[] = $result->plaintext;
        }
        // this will find out  form search results
        foreach($html->find('div.kCrYT') as $result)
        {
            $links = $result->find('a');
        
            foreach($links as $link){
        
                $url = str_replace('/url?q=', '', $link->href);
                // echo $url . "<hr>";
                $url = explode("&", $url);
        
                $contentLink[] = $url[0];
                //   echo $url[0] . "<hr>";
                }
        //    $url[] = $result->plaintext;
        }
        // this will get the content form search results
        foreach($html->find('div > div.s3v9rd') as $result)
        {
            $contentText[] = $result;
        }
        
        // This will count percentage of similar words between search content and request text
        foreach($contentText as $content)
        {
            similar_text($text, $content, $perc);
            $percent[] = round($perc, 0);
        }
        // this will contain all the data
        $data = [];

        for($i = 0; $i <= count($contentTitle); $i++ )
        {
            $data[$i] = [
                            'title' => $contentTitle[$i],
                            'url' => $contentLink[$i],
                            'text' => $contentText[$i],
                            'percent' => $percent[$i],
                        ];
        }

        return $data;
    }

}