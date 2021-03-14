<?php

namespace  Framework;

use Exception;
use Framework\Event\RequestEvent;
use Framework\Event\ArgumentsEvent;
use Framework\Event\ControllerEvent;
use Framework\Event\ResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

class Simplex
{
    protected UrlMatcherInterface $urlMatcher;
    protected ControllerResolverInterface $controllerResolver;
    protected ArgumentResolverInterface $argumentResolver;
    protected EventDispatcherInterface $dispatcher;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        UrlMatcherInterface $urlMatcher, 
        ControllerResolverInterface $controllerResolver, 
        ArgumentResolverInterface $argumentResolver
    ) {
        $this->dispatcher = $dispatcher;
        $this->urlMatcher = $urlMatcher;
        $this->controllerResolver = $controllerResolver;
        $this->argumentResolver = $argumentResolver;
    }

    public function handle(Request $request)
    {
        $this->urlMatcher->getContext()->fromRequest($request);
        
        try {
            $request->attributes->add($this->urlMatcher->match($request->getPathInfo()));
            $this->dispatcher->dispatch(new RequestEvent($request), 'kernel.request');

            $controller = $this->controllerResolver->getController($request);
            $this->dispatcher->dispatch(new ControllerEvent($request, $controller), 'kernel.controller');

            $arguments = $this->argumentResolver->getArguments($request, $controller);
            $this->dispatcher->dispatch(new ArgumentsEvent($request, $controller, $arguments), 'kernel.arguments');

            $response = call_user_func_array($controller, $arguments);
            $this->dispatcher->dispatch(new ResponseEvent($response), 'kernel.response');

        } catch (ResourceNotFoundException $e) {
            $response = new Response("Page not found", 404);
        } catch (Exception $e) {
            $response = new Response("Server Error", 500);
        }

        return $response;
    }
}