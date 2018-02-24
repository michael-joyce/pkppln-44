<?php

/*
 *  This file is licensed under the MIT License version 3 or
 *  later. See the LICENSE file for details.
 *
 *  Copyright 2018 Michael Joyce <ubermichael@gmail.com>.
 */

namespace AppBundle\Menu;

use AppBundle\Entity\Pln;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 *
 */
class Builder implements ContainerAwareInterface {

    use ContainerAwareTrait;

    // U+25BE, black down-pointing small triangle.
    const CARET = ' ▾';

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authChecker;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * 
     * @param FactoryInterface $factory
     * @param AuthorizationCheckerInterface $authChecker
     * @param TokenStorageInterface $tokenStorage
     * @param EntityManagerInterface $em
     */
    public function __construct(FactoryInterface $factory, AuthorizationCheckerInterface $authChecker, TokenStorageInterface $tokenStorage, EntityManagerInterface $em) {
        $this->factory = $factory;
        $this->authChecker = $authChecker;
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
    }

    /**
     * 
     * @param type $role
     * @return boolean
     */
    private function hasRole($role) {
        if (!$this->tokenStorage->getToken()) {
            return false;
        }
        return $this->authChecker->isGranted($role);
    }

    /**
     *
     */
    public function mainMenu(array $options) {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttributes(array(
            'class' => 'nav navbar-nav',
        ));

        $menu->addChild('home', array(
            'label' => 'Home',
            'route' => 'homepage',
        ));

        $menu->addChild('terms', array(
            'label' => 'Terms of Use',
            'route' => 'termofuse_index',
        ));

        if (!$this->hasRole('ROLE_USER')) {
            return $menu;
        }

        $journals = $menu->addChild('journals', array(
            'uri' => '#',
            'label' => 'Journals ' . self::CARET,
        ));
        $journals->setAttribute('dropdown', true);
        $journals->setLinkAttribute('class', 'dropdown-toggle');
        $journals->setLinkAttribute('data-toggle', 'dropdown');
        $journals->setChildrenAttribute('class', 'dropdown-menu');

        $journals->addChild('All Journals', array('route' => 'journal_index'));
        $journals->addChild('Search Journals', array('route' => 'journal_search'));
        $journals->addChild('Whitelist', array('route' => 'whitelist_index'));
        $journals->addChild('Blacklist', array('route' => 'blacklist_index'));
        
        $menu->addChild('Docs', array('route' => 'document_index'));
        
        return $menu;
    }

}
