<?php
/**
 * This is a tool to extract information from searching result of
 * http://transit.yahoo.co.jp/ (train path) into simple text form
 * so that I can paste it into my text document or calendar etc.
 *
 * To use this class, open yahoo!Transist, search the path you want,
 * then click "印刷する" button to show only the path you want (in among
 * many available search results). Copy the opened page's URL and pass
 * it as parameter of this class constructor. Call __string() method to
 * get the simplified text.
 *
 */
class YahooJpTransist {
  
  /**
   * URL of page to be extracted.
   * @var string $url;
   */
  private $url;

  /**
   * List of stations.
   * @var YahooJpTransistStation[] $stations
   */
  private $stations;

  /**
   * List of transports between stations.
   * @var YahooJpTransistTransport[] $transports
   */
  private $transports;
  
  /**
   * DOMDocument to be parsed. This contains only part of HTML page
   * that is needed to be parsed.
   * @var DOMDocument $dom
   */
  private $dom;
  
  /**
   * @var DomXPath $finder
   */
  private $finder;
  
  /**
   * @param string $url The URL of the page to be extracted.
   */
  function __construct($url) {
    $this->url = $url;
    $this->parse();
  }
  
  protected function parse() {
    $this->dom = $this->loadNeededDom();
    $this->stations = array();
    $this->transports = array();
    
    $this->finder = new DomXPath($this->dom);
    
    // Parse stations.
    $stations = $this->finder->query("//*[contains(@class, 'station')]");
    foreach ($stations as $station) {
      $this->stations[] = $this->parseStation($station);
    }
    
    // Parse transports.
    $accesses = $this->finder->query("//*[contains(@class, 'access')]");
    foreach ($accesses as $assess) {
      $this->transports[] = $this->parseAccess($assess);
    }
  }
  
  public function __toString() {
    $text = '';
    foreach ($this->transports as $i => $transport) {
      $text .= $this->stations[$i] . "\n" . $this->transports[$i] . "\n";
    }
    $text .= $this->stations[$i + 1];
    return $text;
  }
  
  /**
   * @param DOMElement $station
   * @return Station
   */
  private function parseStation($station) {
//    echo $this->nodeToString($station) . "\n----------\n";
    $times = $this->finder->query('.//*[@class="time"]/li', $station);
    $arriveTime = NULL;
    $departTime = NULL;
    if ($times->length == 1) {
      $departTime = $times[0]->nodeValue;
    } else if ($times->length == 2) {
      $arriveTime = $times[0]->nodeValue;
      $departTime = $times[1]->nodeValue;
    }
    
    $names = $this->finder->query('.//*/dt', $station);
    $name = $names[0]->nodeValue;
    
    return new YahooJpTransistStation($name, $arriveTime, $departTime);
  }
  
  /**
   * @param DOMElement $access
   * @return Transport
   */
  private function parseAccess($access) {
//    echo $this->nodeToString($access) . "\n----------\n";
    $lines = $this->finder->query('.//*[@class="transport"]/div/text()', $access);
    $line = '';
    foreach ($lines as $text) {
      $line .= $text->nodeValue;
    }
    $line = trim($line);

    $platforms = $this->finder->query('.//*[@class="platform"]', $access);
    $platform = trim($platforms[0]->nodeValue);
    
    return new YahooJpTransistTransport($line, $platform);
  }
  
  /**
   * Convert a DOMElement to string.
   * This method is used for debugging.
   * @param DOMElement $domElement
   * @return string
   */
  private function nodeToString($domElement) {
    return $this->dom->saveHTML($domElement);
  }
  
  /**
   * Get HTML part needs to be extracted and convert it to DOMDocument.
   *
   * @return DOMDocument
   */
  private function loadNeededDom() {
    $doc = $this->loadHtmlDom();
    
    // Get only DOM part we need.
    $partElement = $doc->getElementById("srline");

    $newdoc = new DOMDocument();
    $cloned = $partElement->cloneNode(TRUE);
    $newdoc->appendChild($newdoc->importNode($cloned, TRUE));
    
    return $newdoc;
  }
  
  /**
   * Get HTML content and parse to DOMDocument.
   *
   * @return DOMDocument
   */
  private function loadHtmlDom() {
    
    $html = file_get_contents($this->url);

    $doc = new DOMDocument();    

    // Set error level to ignore invalid HTML.
    $internalErrors = libxml_use_internal_errors(true);
    // Convert HTML to DOM
    $doc->loadHtml($html);
    // Restore error level
    libxml_use_internal_errors($internalErrors);
    
    return $doc;
  }
}

class YahooJpTransistStation {
  
  /**
   * @var string $arriveTime
   */
  private $arriveTime;
  
  /**
   * @var string $departTime
   */
  private $departTime;
  
  /**
   * @param string $name
   * @param string $arriveTime
   * @param string $departTime
   */
  function __construct($name, $arriveTime, $departTime) {
    $this->name = $name;
    $this->arriveTime = $arriveTime;
    $this->departTime = $departTime;
  }
  
  /**
   * @return string
   */
  public function __toString() {
    $text = $this->arriveTime;
    if ($text != NULL) {
      $text .= ' ';
    }
    $text .= $this->departTime;
    $text = "【{$this->name}】　$text";
    
    return $text;
  }
}

class YahooJpTransistTransport {
  
  /**
   * @var string $line
   */
  private $line;
  
  /**
   * @var string $platform
   */
  private $platform;
  
  /**
   * @param string $line
   */
  function __construct($line, $platform) {
    $this->line = $line;
    $this->platform = $platform;
  }
  
  /**
   * @return string
   */
  public function __toString() {
    return "　↓　{$this->line} （{$this->platform}）";
  }
}
?>