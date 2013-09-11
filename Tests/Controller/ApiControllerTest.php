<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Sekou KOÏTA <sekou.koita@supinfo.com>
 * @license: GPL
 *
 */

namespace Tms\Bundle\MediaBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class ApiControllerTest extends WebTestCase
{
    /**
     * Test Post
     */
    public function testPost()
    {
        // Create a browser client
        $client = static::createClient();
        $reference = '';

        $dossier = opendir('.');

        while(false !== ($fichier = readdir($dossier))) {
            if($fichier != '.' && $fichier != '..' && $fichier != 'index.php') {
                echo $fichier; die;
                //TODO lister les fichiers
            }
        }

        // Submit a form with an uploaded file
        $image = new UploadedFile(
            $filePath,
            $fileName,
            $mimeType,
            $size)
        ;

        // POST Request 
        $crawler = $client->request(
            'POST',
            '/media',
            array(),
            array('image' => $image))
        ;
    }

    /**
     * Test delete
     */
    public function testDelete()
    {
        $client = static::createClient();
        $reference = '';

        // DELETE Request 
        $client->request(
            'DELETE',
            '/media/'.$reference,
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
        $reference = '';

        // GET Request 
        $client->request(
            'GET',
            '/media/'.$reference,
            array(),
            array(),
            array(
                'CONTENT_TYPE' => 'image/jpeg',
            ))
        ;
    }
}

