<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Sekou KOÏTA <sekou.koita@supinfo.com>
 * @license: GPL
 *
 */

namespace Tms\Bundle\MediaBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Tms\Bundle\MediaBundle\Entity\Media;
use Tms\Bundle\MediaBundle\Exception\MediaAlreadyExistException;
use Tms\Bundle\MediaBundle\Exception\NoMatchedStorageMapperException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ApiController extends Controller
{
    /**
     * Post
     *
     * @param Request $request
     * @Route("/media")
     * @Method({"POST"})
     */
    public function postAction(Request $request)
    {
        $response = new Response();
        try {
            $media = $this->get('tms_media.manager')->addMedia(
                $request->files->get('media'),
                sprintf('[%s] %s',
                    $request->getClientIp(),
                    $request->request->get('source', null)
                ),
                $request->request->get('name', null),
                $request->request->get('description', null)
            );

            $response->setStatusCode(201);
            $response->setContent(json_encode($media->toArray()));
        } catch (MediaAlreadyExistException $e) {
            $response->setStatusCode(400);
            $response->setContent($e->getMessage());
        } catch (NoMatchedStorageMapperException $e) {
            $response->setStatusCode(415);
            $response->setContent($e->getMessage());
        }  catch (FileException $e) {
            $response->setStatusCode(413);
            $response->setContent($e->getMessage());
        } catch (\Exception $e) {
            $response->setStatusCode(418);
            $response->setContent($e->getMessage());
        }

        return $response;
    }

    /**
     * Delete
     *
     * @param Request $request
     * @param string $reference
     * @Route("/media/{reference}")
     * @Method({"DELETE"})
     */
    public function deleteAction(Request $request, $reference)
    {
        $response = new Response();
        try {
            $this->get('tms_media.manager')->deleteMedia($reference);
            $response->setStatusCode(204);
        } catch (\Exception $e) {
            $response->setStatusCode(404);
            $response->setContent($e->getMessage());
        }

        return $response;
    }

    /**
     * Get
     *
     * @param Request $request
     * @param string $reference
     * @Route("/media/{reference}.{_format}", defaults={"_format"=null})
     * @Route("/media/{reference}")
     * @Method({"GET"})
     */
    public function getAction(Request $request, $reference)
    {
        $format = $request->getRequestFormat();
        $response = new Response();
        try {
            $media = $this->get('tms_media.manager')->retrieveMedia($reference);
            $storageProvider = $this->get('tms_media.manager')->getStorageProvider($media->getProviderServiceName());
            $response->setStatusCode(200);

            if(is_null($format) || $format == $media->getExtension()) {
                $response->headers->set('Content-Type', $media->getMimeType());
                $response->headers->set('Content-Length', $media->getSize());
                $response->setETag($media->getReference());
                $response->setLastModified($media->getCreatedAt());
                $response->setContent($storageProvider->read($media->getReference()));
            } else {
                // TODO: Improve this part with a service
            }

            // TODO: Improve this part with configuration
            $response->setPublic();
            $date = new \DateTime();
            $date->modify('+3600 seconds');
            $response->setExpires($date);
            $response->setMaxAge(3600);
            $response->setSharedMaxAge(3600);
        } catch (\Exception $e) {
            $response->setStatusCode(404);
            $response->setContent($e->getMessage());
        }

        return $response;
    }
}
