<?php

namespace AppBundle\Tests\Controller\SwordController;

class CreateDepositTest extends AbstractSwordTestCase {

	public function testCreateDepositWhitelisted() {
		$depositCount = count($this->em->getRepository('AppBundle:Deposit')->findAll());
		$this->testClient->request(
            'POST',
            '/api/sword/2.0/col-iri/44428B12-CDC4-453E-8157-319004CD8CE6',
            array(),
            array(),
            array(),
            $this->getDepositXml()
		);
		$response = $this->testClient->getResponse();
		$this->assertEquals(201, $response->getStatusCode());
		$this->assertEquals('http://localhost/api/sword/2.0/cont-iri/44428B12-CDC4-453E-8157-319004CD8CE6/5F5C84B1-80BF-4071-8D3F-057AA3184FC9/state', $response->headers->get('Location'));
		$this->assertEquals($depositCount + 1, count($this->em->getRepository('AppBundle:Deposit')->findAll()));
		$xml = $this->getXml($this->testClient);
		$this->assertEquals('depositedByJournal', $this->getXmlValue($xml, '//atom:category[@label="Processing State"]/@term'));
	}

	public function testCreateDepositNotWhitelisted() {
		$depositCount = count($this->em->getRepository('AppBundle:Deposit')->findAll());
		$this->testClient->request(
            'POST',
            '/api/sword/2.0/col-iri/04F2C06E-35B8-43C1-B60C-1934271B0B7E',
            array(),
            array(),
            array(),
            $this->getDepositXml()
		);
		$this->assertEquals(400, $this->testClient->getResponse()->getStatusCode());
		$this->assertContains('Not authorized to create deposits.', $this->testClient->getResponse()->getContent());
		$this->assertEquals($depositCount, count($this->em->getRepository('AppBundle:Deposit')->findAll()));
	}

	private function getDepositXml() {
		$str = <<<'ENDXML'
<entry
    xmlns="http://www.w3.org/2005/Atom"
    xmlns:dcterms="http://purl.org/dc/terms/"
    xmlns:pkp="http://pkp.sfu.ca/SWORD">
    <email>foo@example.com</email>
    <title>Test Data Journal of Testing</title>
    <pkp:journal_url>http://tdjt.example.com</pkp:journal_url>
    <pkp:publisherName>Publisher of Stuff</pkp:publisherName>
    <pkp:publisherUrl>http://publisher.example.com</pkp:publisherUrl>
    <pkp:issn>1234-1234</pkp:issn>
    <id>urn:uuid:5F5C84B1-80BF-4071-8D3F-057AA3184FC9</id>
    <updated>2016-04-22T12:35:48Z</updated>
    <pkp:content size="123" volume="2" issue="4" pubdate="2016-04-22"
		checksumType="SHA-1"
        checksumValue="d46c034ef54c36237b89d456968965432830a603">http://example.com/deposit/foo.zip</pkp:content>
    <pkp:license>
        <pkp:publishingMode>Open</pkp:publishingMode>
        <pkp:openAccessPolicy>OA GOOD</pkp:openAccessPolicy>
        <pkp:licenseUrl>http://example.com/license</pkp:licenseUrl>
        <pkp:copyrightNotice>Copyright ME</pkp:copyrightNotice>
        <pkp:copyrightBasis>ME</pkp:copyrightBasis>
        <pkp:copyrightHolder>MYSELF</pkp:copyrightHolder>
    </pkp:license>
</entry>
ENDXML;
		return $str;
	}
}

