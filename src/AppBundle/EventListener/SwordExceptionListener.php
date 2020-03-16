<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace AppBundle\EventListener;

use AppBundle\Controller\SwordController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Description of SwordExceptionListener.
 */
class SwordExceptionListener {
    /**
     * Controller that threw the exception.
     *
     * @var Controller
     */
    private $controller;

    /**
     * Twig instance.
     *
     * @var \Symfony\Component\Templating\EngineInterface
     */
    private $templating;

    /**
     * Symfony environment.
     *
     * @var string
     */
    private $env;

    /**
     * Construct the listener.
     *
     * @param string $env
     */
    public function __construct($env, EngineInterface $templating) {
        $this->templating = $templating;
        $this->env = $env;
    }

    /**
     * Once the controller has been initialized, this event is fired.
     *
     * Grab a reference to the active controller.
     */
    public function onKernelController(FilterControllerEvent $event) : void {
        $this->controller = $event->getController();
    }

    /**
     * Exception handler for all controller events.
     */
    public function onKernelException(GetResponseForExceptionEvent $event) : void {
        if ( ! $this->controller[0] instanceof SwordController) {
            return;
        }

        $exception = $event->getException();
        $response = new Response();
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
        } else {
            $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $response->headers->set('Content-Type', 'text/xml');
        $response->setContent($this->templating->render('AppBundle:sword:exception_document.xml.twig', [
            'exception' => $exception,
            'env' => $this->env,
        ]));
        $event->setResponse($response);
    }
}
