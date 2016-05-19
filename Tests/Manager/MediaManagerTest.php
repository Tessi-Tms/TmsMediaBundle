<?php

namespace Tms\Bundle\MediaBundle\Tests\Manager;

use Tms\Bundle\MediaBundle\Manager\MediaManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher;

class MediaManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $entityManager = $this->getMockBuilder("Doctrine\ORM\EntityManager")
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $eventDispatcher = $this->getMockBuilder("Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher")
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->mediaManager = new MediaManager(
            $entityManager,
            $eventDispatcher,
            array()
        );
    }

    public function testAddMedia()
    {
        $rawMedia = $this->getMockBuilder("Symfony\Component\HttpFoundation\File\UploadedFile")
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $rawMedia
            ->expects($this->any())
            ->method('getClientMimeType')
            ->will($this->returnValue('image/png'))
            ->method('getClientOriginalName')
            ->will($this->returnValue('dummy_media'))
            ->method('getClientSize')
            ->will($this->returnValue(102400))
        ;

        $this->mediaManager->addMedia($rawMedia, array());
    }
}