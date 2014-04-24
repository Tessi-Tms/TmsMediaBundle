<?php

namespace Tms\Bundle\MediaBundle\Manager;

//use Tms\Bundle\MediaBundle\Entity\Media;
use Tms\Bundle\MediaBundle\Event\MediaEvent;
use Tms\Bundle\MediaBundle\Event\MediaEvents;

/**
 * Media manager.
 *
 * @author Gabriel Bondaz <gabriel.bondaz@idci-consulting.fr>
 */
class MediaManager extends AbstractManager
{
    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return "TmsMediaBundle:Media";
    }

    /**
     * {@inheritdoc}
     */
    public function delete($entity)
    {
        $this->getEventDispatcher()->dispatch(
            MediaEvents::PRE_DELETE,
            new MediaEvent($entity)
        );

        parent::delete($entity);

        $this->getEventDispatcher()->dispatch(
            MediaEvents::POST_DELETE,
            new MediaEvent($entity)
        );
    }
}