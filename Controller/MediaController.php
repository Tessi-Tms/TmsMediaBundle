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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Tms\Bundle\MediaBundle\Entity\Media;

class MediaController extends Controller
{
    /**
     * Get
     *
     * @Route("/medias/get/{mediaId}", requirements={"_method" = "GET"}, name="media_get")
     * @param Request $request
     * @param integer $mediaId
     */
    public function getAction(Request $request, $mediaId)
    {
        return array();
    }

    /**
     * Add
     *
     * @Route("/medias/add", requirements={"_method" = "POST"}, name="media_add")
     * @param Request $request
     */
    public function AddAction(Request $request)
    {
        return array();
    }

    /**
     * Delete
     *
     * @Route("/medias/delete/{mediaId}", requirements={"_method" = "GET"}, name="media_delete")
     * @param Request $request
     * @param integer $mediaId
     */
    public function deleteAction(Request $request, $mediaId)
    {
        return array();
    }

    /**
     * Show
     *
     * @Route("/medias/show/{mediaId}", requirements={"_method" = "GET"}, name="media_show")
     * @param Request $request
     * @param integer $mediaId
     */
    public function showAction(Request $request, $mediaId)
    {
        return array();
    }

}
