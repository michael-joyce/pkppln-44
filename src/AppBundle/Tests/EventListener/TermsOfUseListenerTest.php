<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Tests\EventListener;

use AppBundle\Entity\TermOfUse;
use AppBundle\Entity\TermOfUseHistory;
use Nines\UtilBundle\Tests\Util\BaseTestCase;

/**
 * Description of TermsOfUseListenerTest
 */
class TermsOfUseListenerTest extends BaseTestCase {

    protected function getFixtures() {
        return [];
    }
    
    public function testCreate() {
        $term = new TermOfUse();
        $term->setContent('test 1');
        $term->setKeyCode('t1');
        $term->setWeight(1);
        
        $this->em->persist($term);
        $this->em->flush();
        
        $history = $this->em->getRepository(TermOfUseHistory::class)->findOneBy(array(
            'termId' => $term->getId(),
        ));
        $this->assertNotNull($history);
        $this->assertEquals('create', $history->getAction());
        $changeset = $history->getChangeSet();
        $this->assertEquals([null, 1], $changeset['id']);
        $this->assertEquals([null, 1], $changeset['weight']);
        $this->assertEquals([null, 't1'], $changeset['keyCode']);
        $this->assertEquals([null, 'test 1'], $changeset['content']);
    }
    
    public function testUpdate() {
        $term = new TermOfUse();
        $term->setContent('test 1');
        $term->setKeyCode('t1');
        $term->setWeight(1);
        
        $this->em->persist($term);
        $this->em->flush();
        
        $term->setContent('updated');
        $term->setKeyCode('u1');
        $term->setWeight(3);
        $this->em->flush();
        
        $history = $this->em->getRepository(TermOfUseHistory::class)->findOneBy(array(
            'termId' => $term->getId(),
            'action' => 'update',
        ));
        $this->assertNotNull($history);
        $this->assertEquals('update', $history->getAction());
        
        $changeset = $history->getChangeSet();
        $this->assertEquals([1, 3], $changeset['weight']);
        $this->assertEquals(['t1', 'u1'], $changeset['keyCode']);
        $this->assertEquals(['test 1', 'updated'], $changeset['content']);
        
    }
    
    public function testDelete() {
        $term = new TermOfUse();
        $term->setContent('test 1');
        $term->setKeyCode('t1');
        $term->setWeight(1);
        
        $this->em->persist($term);
        $this->em->flush();

        // save for later.
        $termId = $term->getId();
        
        $this->em->remove($term);
        $this->em->flush();
        
        $history = $this->em->getRepository(TermOfUseHistory::class)->findOneBy(array(
            'termId' => $termId,
            'action' => 'delete',
        ));
        $this->assertNotNull($history);
        $this->assertEquals('delete', $history->getAction());
        
        $changeset = $history->getChangeSet();
        $this->assertEquals([1, null], $changeset['weight']);
        $this->assertEquals(['t1', null], $changeset['keyCode']);
        $this->assertEquals(['test 1', null], $changeset['content']);
        
    }
}
