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
            $mediaRaw = $request->files->get('media');
            $reference = $this->get('tms_media.manager')->addMedia($mediaRaw);
            $response->setStatusCode(200);
            $response->setContent($reference);
        } catch (\Exception $e) {
            $response->setStatusCode(400);
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
            $response->setStatusCode(400);
            $response->setContent($e->getMessage());
        }

        return $response;
    }

    /**
     * Get
     *
     * @param Request $request
     * @param string $reference
     * @Route("/media/{reference}")
     * @Method({"GET"})
     */
    public function getAction(Request $request, $reference)
    {
        $response = new Response();
        try {
            $media = $this->get('tms_media.manager')->retrieveMedia($reference);
            $storageProvider = $this->get('tms_media.manager')->getStorageProvider($media->getProviderServiceName());
            var_dump($storageProvider->read($media->getReference()));die;

            $response->setStatusCode(200);
            $response->headers->set('Content-Type', $media->getContentType());
            $response->headers->set('Content-Length', $media->getSize());
            $response->setETag($media->getReference());
            $response->setLastModified($media->getCreatedAt());
            $response->setContent($storageProvider->read($media->getReference()));

            // TODO: Improve this part with configuration
            $response->setPublic();
            $date = new \DateTime();
            $date->modify('+3600 seconds');
            $response->setExpires($date);
            $response->setMaxAge(3600);
            $response->setSharedMaxAge(3600);
        } catch (\Exception $e) {
            $response->setStatusCode(400);
            $response->setContent($e->getMessage());
        }

        return $response;
    }
}
