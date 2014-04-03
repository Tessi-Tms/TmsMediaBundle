<?php

namespace Tms\Bundle\MediaBundle\Event;

/**
 * MediaEvents
 *
 * @author Gabriel Bondaz <gabriel.bondaz@idci-consulting.fr>
 */
final class MediaEvents
{
    /**
     * @var string
     */
    const PRE_DELETE =  'tms_media.media.pre_delete';
    const POST_DELETE = 'tms_media.media.post_delete';
}