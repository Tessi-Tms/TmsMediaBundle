<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Sekou KOÏTA <sekou.koita@supinfo.com>
 * @license: GPL
 *
 */

namespace Tms\Bundle\MediaBundle\Tests\Controller;

use Tms\Bundle\MediaBundle\Controller\ApiController;
use Tms\Bundle\MediaBundle\Entity\Media;

class ApiControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test Post
     */
    public function testPost()
    {
        // Create a client like the browser
        $client = static::createClient;

        // Soumission de formulaire avec upload de fichier
        $photo = new UploadedFile(
            '/home/sekou/Images/photo.jpg',
            'photo.jpg',
            'image/jpeg',
            44772)
        ;

        // or
/*        $photo = array(
            '/home/sekou/Images/photo.jpg',
            'name' => 'photo.jpg',
            'type' => 'image/jpeg',
            'size' => 123,
            'error' => UPLOAD_ERR_OK)
        ;
*/
        $crawler = $client->request(
            'POST',
            '/media',
            array(),
            array('photo' => $photo))
        ;
    }

    /**
     * Test delete
     */
    public function testDelete()
    {
        $client = static::createClient();

        // Exécute une requête DELETE et passe des entête HTTP
        $client->request(
            'DELETE',
            '/media/reference',
            array(),
            array(),
            array())
        ;
    }

    /**
     * Test Get
     */
    public function testGet()
    {
        $client = static::createClient();

        $client->request(
            'GET',
            '/media/reference',
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'image/jpeg',
            ))
        ;
    }
}

