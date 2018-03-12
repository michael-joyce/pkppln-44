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
     * HTTP request response.
     *
     * @var ResponseInterface
     */
    private $response;
    
    /**
     * Parsed XML from the response.
     *
     * @var SimpleXMLElement
     */
    private $xml;
    
    /**
     * Error from parsing the XML response.
     *
     * @var array|string[]
     */
    private $error;
    
    /**
     * Construct a ping result from an HTTP request.
     *
     * @param ResponseInterface $response
     *   The HTTP response.
     */
    public function __construct(ResponseInterface $response) {
        $this->response = $response;
        $this->error = array();
        $this->xml = null;
        $oldErrors = libxml_use_internal_errors(true);
        $this->xml = simplexml_load_string($response->getBody());
        if ($this->xml === false) {
            foreach (libxml_get_errors() as $error) {
                $this->error[] = "{$error->line}:{$error->column}:{$error->code}:{$error->message}";
            }
        }
        libxml_use_internal_errors($oldErrors);
    }
    
    /**
     * Get the HTTP response status.
     *
     * @return int
     *   The HTTP status code.
     */
    public function getHttpStatus() {
        return $this->response->getStatusCode();
    }
    
    /**
     * Return true if the request generated an error.
     *
     * @return bool
     *   True if there was an error.
     */
    public function hasError() {
        return count($this->error) > 0;
    }
    
    /**
     * Get the XML processing error.
     *
     * @return string
     *   The formatted error string.
     */
    public function getError() {
        return implode("\n", $this->error);
    }

    /**
     * Get the response body.
     *
     * Optionally strips out the tags.
     *
     * @param bool $stripTags
     *   If true, remove all tags from the body.
     *
     * @return string
     *   The response body.
     */
    public function getBody($stripTags = true) {
        if ($stripTags) {
            return strip_tags($this->response->getBody());
        }
        return $this->response->getBody();
    }
    
    /**
     * Check if the http response was XML.
     *
     * @return bool
     *   True if there was well-formed XML in the response body.
     */
    public function hasXml() {
        return $this->xml !== null;
    }
    
    /**
     * Get the response XML.
     *
     * @return SimpleXMLElement
     *   Parsed XML.
     */
    public function getXml() {
        return $this->xml;
    }
    
    /**
     * Get an HTTP header.
     *
     * @param string $name
     *   Get an HTTP header value.
     *
     * @return string
     *   Header value.
     */
    public function getHeader($name) {
        return $this->response->getHeader($name);
    }
    
    /**
     * Get the OJS release version.
     *
     * @return string
     *   Version string.
     */
    public function getOjsRelease() {
        return Xpath::getXmlValue($this->xml, '//ojsInfo/release', Deposit::DEFAULT_JOURNAL_VERSION);
    }
    
    /**
     * Get the plugin release version.
     *
     * @return string
     *   Version string.
     */
    public function getPluginReleaseVersion() {
        return Xpath::getXmlValue($this->xml, '//pluginInfo/release');
    }
    
    /**
     * Get the plugin release date.
     *
     * @return string
     *   Date, as a string.
     */
    public function getPluginReleaseDate() {
        return Xpath::getXmlValue($this->xml, '//pluginInfo/releaseDate');
    }
    
    /**
     * Check if the plugin thinks its current.
     *
     * @return string
     *   "Yes" or "No"
     */
    public function isPluginCurrent() {
        return Xpath::getXmlValue($this->xml, '//pluginInfo/current');
    }
    
    /**
     * Check if the terms of use have been accepted.
     *
     * @return string
     *   Returns "yes" or "no".
     */
    public function areTermsAccepted() {
        return Xpath::getXmlValue($this->xml, '//terms/@termsAccepted');
    }
    
    /**
     * Get the journal title from the response.
     *
     * @return string
     *   The reported journal title.
     */
    public function getJournalTitle($default = null) {
        return Xpath::getXmlValue($this->xml, '//journalInfo/title', $default);
    }
    
    /**
     * Get the number of articles the journal has published.
     *
     * @return int
     *   The number of articles.
     */
    public function getArticleCount() {
        return Xpath::getXmlValue($this->xml, '//articles/@count');
    }
    
    /**
     * Get a list of article titles reported in the response.
     *
     * @return array[]
     *   Array of associative array data.
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
