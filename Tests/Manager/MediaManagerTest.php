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

        $this->mediaManager = new MediaManager(
            $entityManager,
            $eventDispatcher,
            array('default_store_path' => '/tmp')
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