<?php

namespace Tms\Bundle\MediaBundle\Tests\Manager;

use Tms\Bundle\MediaBundle\Manager\MediaManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $entityRepository = $this->getMockBuilder("Doctrine\ORM\EntityRepository")
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $entityRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue(null))
        ;

        $entityManager = $this->getMockBuilder("Doctrine\ORM\EntityManager")
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($entityRepository))
        ;

        $eventDispatcher = $this->getMockBuilder("Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher")
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $gaufrette = $this->getMockBuilder("Gaufrette\FilesystemMap")
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $gaufrette
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue('test'))
        ;

        $this->mediaManager = new MediaManager(
            array(
                'working_directory'        => '/tmp/media_working',
                'cache_directory'          => '/tmp/media_cache',
                'default_storage_provider' => 'gaufrette.default_media_filesystem',
                'api_public_endpoint'      => '//media-manager.local/app_dev.php/api',
            ),
            $entityManager,
            $eventDispatcher,
            $gaufrette
        );
    }

    public function testAddMedia()
    {
        copy(__DIR__.'/../data/linux.png', '/tmp/test_copy_linux.png');

        $uploadedFile = new UploadedFile(
            '/tmp/test_copy_linux.png',
            'dummy_test_file',
            null,
            null,
            null,
            true
        );

        $this->mediaManager->addMedia(array('media' => $uploadedFile));
    }
}