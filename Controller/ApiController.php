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
use Gaufrette\Filesystem;
use Gaufrette\adapter;
use Gaufrette\File as File;
use Gaufrette\FilesystemMap;
use Gaufrette\Adapter\Local as LocalAdapter;

class ApiController extends Controller
{
    /**
     * @Route("/media")
     * @Method({"POST"})
     */
    public function postAction(Request $request)
    {
        $mediaRaw = $request->files->get('media');
        $this->get('tms_media.manager')->addMedia($mediaRaw);

        $response = new Response();

        return $response;
    }

    /**
     * @Route("/media/{id}")
     * @Method({"DELETE"})
     */
    public function deleteAction(Request $request, $id)
    {
        $response = new Response();

        return $response;
    }

    /**
     * @Route("/media/{id}")
     * @Method({"GET"})
     */
    public function getAction(Request $request, $id)
    {
        $response = new Response();

        return $response;
    }
}
