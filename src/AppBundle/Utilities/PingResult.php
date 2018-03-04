<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Utilities;

use AppBundle\Entity\Deposit;
use Exception;
use Psr\Http\Message\ResponseInterface;
use SimpleXMLElement;

/**
 * Description of PingResult.
 */
class PingResult {

    /**
     * @var ResponseInterface
     */
    private $response;
    
    /**
     * @var SimpleXMLElement
     */
    private $xml;
    
    /**
     * @var string|null
     */
    private $error;
    
    /**
     * @param ResponseInterface $response
     */
    public function __construct(ResponseInterface $response) {
        $this->response = $response;
        $this->error = null;
        $this->xml = null;
        try {
            $oldErrors = libxml_use_internal_errors(true);
            $this->xml = simplexml_load_string($response->getBody());
            if ($this->xml === false) {
                $this->error = '';
                foreach (libxml_get_errors() as $error) {
                    $this->error = "{$error->line}:{$error->column}:{$error->code}:{$error->message}";
                }
            }
            libxml_use_internal_errors($oldErrors);
        } catch (Exception $ex) {
            $this->error = get_class($ex) . ":" . $ex->getMessage();
        }
    }
    
    /**
     *
     */
    public function getHttpStatus() {
        return $this->response->getStatusCode();
    }
    
    /**
     *
     */
    public function hasError() {
        return $this->error !== null;
    }
    
    /**
     *
     */
    public function getError() {
        return $this->error;
    }
    
    /**
     *
     */
    public function getBody($stripTags = true) {
        if ($stripTags) {
            return strip_tags($this->response->getBody());
        }
        return $this->response->getBody();
    }
    
    /**
     *
     */
    public function hasXml() {
        return $this->xml !== null;
    }
    
    /**
     * @return \SimpleXMLElement
     */
    public function getXml() {
        return $this->xml;
    }
    
    /**
     *
     */
    public function getHeader($name) {
        return $this->response->getHeader($name);
    }
    
    /**
     *
     */
    public function getOjsRelease() {
        return Xpath::getXmlValue($this->xml, '//ojsInfo/release', Deposit::DEFAULT_JOURNAL_VERSION);
    }
    
    /**
     *
     */
    public function getPluginReleaseVersion() {
        return Xpath::getXmlValue($this->xml, '//pluginInfo/release');
    }
    
    /**
     *
     */
    public function getPluginReleaseDate() {
        return Xpath::getXmlValue($this->xml, '//pluginInfo/releaseDate');
    }
    
    /**
     *
     */
    public function isPluginCurrent() {
        return Xpath::getXmlValue($this->xml, '//pluginInfo/current');
    }
    
    /**
     *
     */
    public function areTermsAccepted() {
        return Xpath::getXmlValue($this->xml, '//terms/@termsAccepted');
    }
    
    /**
     *
     */
    public function getJournalTitle($default = null) {
        return Xpath::getXmlValue($this->xml, '//journalInfo/title', $default);
    }
    
    /**
     *
     */
    public function getArticleCount() {
        return Xpath::getXmlValue($this->xml, '//articles/@count');
    }
    
    /**
     *
     */
    public function getArticleTitles() {
        $articles = array();
        foreach (Xpath::query($this->xml, '//articles/article') as $node) {
            $articles[] = array(
            'date' => (string) $node['pubDate'],
            'title' => trim((string) $node),
            );
        }
        return $articles;
    }

}
