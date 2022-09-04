<?php

namespace BenyCode\Slim\JadMiddleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Jad\Jad;
use Jad\Configure;
use Jad\Map\AnnotationsMapper;
use Doctrine\ORM\EntityManager;

final class JsonApiMiddleware implements MiddlewareInterface
{
    private EntityManager $entityManager;
	
	private ResponseFactoryInterface $responseFactory;
	
	private Configure $configure;
	
	private string $path;

    public function __construct(
		EntityManager $entityManager, 
		ResponseFactoryInterface $responseFactory,
		Configure $configure,
		string $path
	)
    {
        $this->entityManager = $entityManager;
		$this->responseFactory = $responseFactory;
		$this->configure = $configure;
		$this->path = $path;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
		
		$rootPath = mb_substr($request
			->getUri()
			->getPath(), 0, 9)
			;
			
		if($rootPath === $this->path) {	
		
			$jad = new Jad(new AnnotationsMapper($this->entityManager));
			$jad
				->setPathPrefix($this->path)
				;
	
			$response = $this
				->responseFactory
				->createResponse()
				;
	
			$jad
				->jsonApiResult()
				;
				
			return $response;

		} else {
			return $handler->handle($request);
		}
    }
}
