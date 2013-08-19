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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Tms\Bundle\MediaBundle\Entity\Media;

class MediaController extends Controller
{
    /**
     * [POST] /media
     */
    public function postAction()
    {
        return array();
    }

    /**
     * [DELETE] /media/{mediaId}
     */
    public function deleteAction($mediaId)
    {
        return array();
    }

    /**
     * [GET] /media/{mediaId}
     */
    public function getAction($mediaId)
    {
        return array();
    }
}
