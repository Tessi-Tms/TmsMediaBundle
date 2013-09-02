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
        //try {
            $mediaRaw = $request->files->get('media');
            $this->get('tms_media.manager')->addMedia($mediaRaw);
        //} catch (\Exception $e) {
            //die('TODO');
        //}

        $response = new Response();

        return $response;
    }

    /**
     * Delete
     *
     * @param Request $request
     * @param string $id
     * @Route("/media/{id}")
     * @Method({"DELETE"})
     */
    public function deleteAction(Request $request, $id)
    {
        try {
            $this->get('tms_media.manager')->deleteMedia($id);
            $response = new Response();
        } catch (\Exception $e) {
            die('TODO');
        }

        return $response;
    }

    /**
     * Get
     *
     * @param Request $request
     * @param string $id
     * @Route("/media/{id}")
     * @Method({"GET"})
     */
    public function getAction(Request $request, $id)
    {
        try {
            $this->get('tms_media.manager')->retrieveMedia($id);
            $response = new Response();
        } catch (\Exception $e) {
            die('TODO');
        }

        return $response;
    }
}
