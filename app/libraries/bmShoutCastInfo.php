<?php
 /**
 *  ShoutCastInfo
 * 
 * Retrieve Stream information from a shoutcast server
 * @link http://www.shoutcast.com
 * Please note that this package requires php5 or higher including the simplexml extension.
 * 
 * @package bmShoutCastInfo
 * @author Jan-Simon Winkelmann <winkelmann@webnauts.net>
 * @version 1.0 / 2006-01-21
 * @copyright Copyright &copy; 2006, Jan-Simon Winkelmann
 **************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 **************************************************************************/
 require ('bmShoutCastException.class.php');
 
 /**
  * This class uses the user "admin" and the password specified in the constructor to
  * log into the shoutcast servers admin area, reading the xml statistics file generated
  * by the shoutcast server on request. 
  */
 class bmShoutCastInfo {
 
 	/**
 	 * @staticvar int STREAM_STATUS_ONLINE constant defining the status of the stream when its online
 	 * @access public
 	 */
 	const STREAM_STATUS_ONLINE = 1;
 	
 	/**
 	 * @staticvar int STREAM_STATUS_OFFLINE constant defining the status of the stream when its offline
 	 * @access public 
 	 */
 	const STREAM_STATUS_OFFLINE = 0;
 
 	/**
 	 * @var string host of the shoutcast server
 	 * @access private
 	 */
 	private $stationHost;
 	
 	/**
 	 * @var int port of the shoutcast server
 	 * @link http://php.net/function.fsockopen
 	 * @access private
 	 */
 	private $stationPort;
 	
 	/**
 	 * @var stream $errorNo containing a pointer to the http socket error no
 	 * @link http://php.net/function.fsockopen
 	 * @access private
 	 */
 	private $errorNo = null;
 	
 	/**
 	 * @var stream $errorNo containing a pointer to the http socket error stream
 	 * @access private
 	 */
 	private $errorStr = null;
 
 	/**
 	 * @var object contains the SimpleXml data representation of the shoutcast xml data
 	 * @access private
 	 */
 	private $xmlData = null;
 	
 	/**
 	 * Default Constructor
 	 * 
 	 * Connect to the shoutcast server and read the data
 	 * 
 	 * @access public
 	 * @param string $stationHost The host of the station you want to gather information on
 	 * @param int $stationPort The port of the station you want to gather information on
 	 * @throws bmShoutCastException when an error occurrs
 	 */
 	public function __construct( $stationHost, $stationPort, $adminPassword) {
 		$this->stationHost = $stationHost;
 		$this->stationPort = $stationPort;

 		$this->adminPassword = $adminPassword;
 		
 		$this->getData();
 	}
 	/**
 	 * getData()
 	 * send an http request to the shoutcast server and read the request into a class variable, then
 	 * call extractHeaders() and loadXml() to retrieve the information
 	 * 
 	 * @access private
 	 * @throws bmShoutCastException
 	 */
 	private function getData() {
 		// intialize socket connection
 		if ( !$dataSocket = fsockopen($this->stationHost, intval($this->stationPort), $this->errorNo, $this->errorStr, 30) ) {
 			throw new bmShoutCastException('Error encountered.');
 		}

		$base64data = base64_encode('admin:' . $this->adminPassword);
	
      	$httpRequest = "GET /admin.cgi?mode=viewxml HTTP/1.0\r\n"
                        ."Host: 127.0.0.1\r\n"
                        ."User-Agent: Mozilla/4.0 (compatible; bmShoutCastInfo/1.0; " . PHP_OS . ")\r\n"
						."Authorization: Basic " . $base64data . "\r\n"
                        ."\r\n";

 		if ( !@fputs($dataSocket, $httpRequest) ) {
 			throw new bmShoutCastException('Error encountered writing to stream.');
 		}
 		
 		
 		
 		// load data
 		while ( !feof($dataSocket) ) {
 			$byte = fread($dataSocket, 1);
 			$this->xmlData .= $byte;
 		}
 		
 		// close socket connection
 		if ( !fclose($dataSocket) ) {
 			throw new bmShoutCastException('Error closing socket.');
 		}

		$this->extractHeaders();
 		
 		$this->loadXml();
 	
 	}
 	
 	/**
 	 * extractHeaders()
 	 * 
 	 * extract the headers from the raw http reply and store them in $this->httpHeaders
 	 * @access private
 	 * @throws bmShoutCastException
 	 */
 	private function extractHeaders() {
 		 $splitLines = explode("\r\n", $this->xmlData);
 		 
 		 $headers = array();
 		 
 		 $i = 0;
 		 $foundEndOfHeaders = false;
 		 do {
 		 	$currentLine = $splitLines[$i];
 		 	
 		 	if ( $currentLine = '' ) {
 		 		$foundEndOfHeaders = true;
 		 	} else {
 		 		if ( strstr($currentLine,'HTTP/1.0') && !strstr($currentLine,'200 OK') ) {
 		 			throw new bmShoutCastException('Http Error occurred: ' . $currentLine);
 		 		} else {
 		 			$splitHeader = explode(':',$currentLine);
 		 			if ( count($splitHeader)==2 ) {
 		 				$headers[$splitHeader[0]] = $splitHeader[1];
 		 			}
 		 		}
 		 	}
 		 	$i++;
 		 } while ( $i<count($splitLines) && !$foundEndOfHeaders );
 		 
 		 $this->httpHeaders = $headers;
 		 
 		 $this->xmlData = $splitLines[--$i];
 		
 	}
 	
 	/**
 	 * loadXml()
 	 * 
 	 * use php5's simplexml extension to convert $this->xmlData to a simplexml object
 	 * @access private
 	 */
 	private function loadXml() {
 		$this->xmlStructure = simplexml_load_string($this->xmlData);
 	}
 	
 	/**
 	 * &getErrNo()
 	 * @return pointer to errorNo from php fsockopen function
 	 * @link http://php.net/fsockopen fsockopen documentation
 	 */
 	public function &getErrNo() {
 		return $this->errorNo;
 	}

 	/**
 	 * &getErrorStr()
 	 * @return pointer to errorStr from php fsockopen function
 	 * @link http://php.net/fsockopen fsockopen documentation
 	 */
 	public function &getErrorStr() {
 		return $this->errorStr;
 	}
 	
 	/**
 	 * get()
 	 * 
 	 * general getter for any element within the SimpleXml tree
 	 * @param string $element element name to retrieve value for
 	 * @access private
 	 * @return mixed the contents of the specified xml element
 	 */
 	private function get($element) {
 		if ( $this->xmlStructure->STREAMSTATUS==self :: STREAM_STATUS_OFFLINE ) {
 			return false;
 		}
 		
 		return $this->xmlStructure->$element;	
 	}
 	
 	/**
 	 * getCurrentListeners()
 	 * 
 	 * return the number of current listeners
 	 * @access public
 	 * @return mixed false if stream is offline, otherwise the number of current listeners
 	 */
 	public function getCurrentListeners() {
 		return $this->get('CURRENTLISTENERS');
 	}

 	/**
 	 * getPeakListeners()
 	 * 
 	 * return the number of peak listeners
 	 * @access public
 	 * @return mixed false if stream is offline, otherwise the number of peak listeners
 	 */
 	public function getPeakListeners() {
 		return $this->get('PEAKLISTENERS');
 	}
 	
 	/**
 	 * getMaxListeners()
 	 * 
 	 * return the maximum number of listeners
 	 * @access public
 	 * @return mixed false if stream is offline, otherwise the maximum number of listeners
 	 */
 	public function getMaxListeners() {
 		return $this->get('MAXLISTENERS');
 	}

 	/**
 	 * getReportedListeners()
 	 * 
 	 * return the reported number of listeners
 	 * @access public
 	 * @return mixed false if stream is offline, otherwise the reported number of listeners
 	 */
 	public function getReportedListeners() {
 		return $this->get('REPORTEDLISTENERS');
 	}
 	
 	/**
 	 * getAverageListenTime()
 	 * 
 	 * return the average listen time
 	 * @access public
 	 * @return mixed false if stream is offline, otherwise the average listen time
 	 */
 	public function getAverageListenTime() {
 		return $this->get('AVERAGETIME');
 	}
 	
 	/**
 	 * getServerGenre()
 	 * 
 	 * return the server genre
 	 * @access public
 	 * @return mixed false if stream is offline, otherwise the server genre
 	 */
  	public function getServerGenre() {
 		return $this->get('SERVERGENRE');
 	}
 	
 	/**
 	 * getServerTitle()
 	 * 
 	 * return the server title
 	 * @access public
 	 * @return mixed false if stream is offline, otherwise the server title
 	 */
 	public function getServerTitle() {
 		return $this->get('SERVERTITLE');
 	}
 	
 	/**
 	 * getSongTitle()
 	 * 
 	 * return the current song title
 	 * @access public
 	 * @return mixed false if stream is offline, otherwise the current song title
 	 */
 	public function getSongTitle() {
 		return $this->get('SONGTITLE');
 	}

  	/**
 	 * getSongUrl()
 	 * 
 	 * return the current song url
 	 * @access public
 	 * @return mixed false if stream is offline, otherwise the current song url
 	 */
 	public function getSongUrl() {
 		return $this->get('SONGURL');
 	}
 	
  	/**
 	 * getIrc()
 	 * 
 	 * return the station irc
 	 * @access public
 	 * @return mixed false if stream is offline, otherwise the station irc
 	 */
 	public function getIrc() {
 		return $this->get('IRC');
 	}

  	/**
 	 * getIcq()
 	 * 
 	 * return the station Icq
 	 * @access public
 	 * @return mixed false if stream is offline, otherwise the station Icq
 	 */
 	public function getIcq() {
 		return $this->get('ICQ');
 	}
 	
  	/**
 	 * getAim()
 	 * 
 	 * return the station Aim
 	 * @access public
 	 * @return mixed false if stream is offline, otherwise the station Aim
 	 */
 	public function getAim() {
 		return $this->get('AIM');
 	}
 	
  	/**
 	 * getWebHits()
 	 * 
 	 * return the number of web hits
 	 * @access public
 	 * @return mixed false if stream is offline, otherwise the number of web hits
 	 */
 	public function getWebHits() {
 		return $this->get('WEBHITS');
 	}

  	/**
 	 * getStreamHits()
 	 * 
 	 * return the number of stream hits
 	 * @access public
 	 * @return mixed false if stream is offline, otherwise the number of stream hits
 	 */
 	public function getStreamHits() {
 		return $this->get('STREAMHITS');
 	}
 	
 	/**
 	 * getStreamStatus()
 	 * 
 	 * returns the current stream status
 	 * @access public
 	 * @return int the stream status
 	 */
 	public function getStreamStatus() {
		/* ALTERAÇÃO */
		/* ALTERAÇÃO */
		/* ALTERAÇÃO */
		/* ALTERAÇÃO */
		if (empty($this->xmlStructure)) {
			return self::STREAM_STATUS_OFFLINE;
		}
		
 		return $this->xmlStructure->STREAMSTATUS;
 	}
 	
  	/**
 	 * getBitRate()
 	 * 
 	 * return the stream bitrate
 	 * @access public
 	 * @return mixed false if stream is offline, otherwise the stream bitrate
 	 */
 	public function getBitRate() {
 		return $this->get('BITRATE');
 	}
 	
  	/**
 	 * getContentType()
 	 * 
 	 * return the stream content type
 	 * @access public
 	 * @return mixed false if stream is offline, otherwise the stream content type
 	 */
 	public function getContentType() {
 		return $this->get('CONTENT');
 	}
 	
  	/**
 	 * getServerVersion()
 	 * 
 	 * return the stream server version
 	 * @access public
 	 * @return mixed false if stream is offline, otherwise the stream server version
 	 */
 	public function getServerVersion() {
 		return $this->get('VERSION');
 	}
 	
 }
 
?>
