<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: GPL
 *
 */
namespace Tms\Bundle\MediaBundle\Exception;

use Tms\Bundle\MediaBundle\Media\Transformer\MediaTransformerInterface;

class UndefinedMediaTransformerParameterException extends \Exception
{
    /**
     * The constructor.
     */
    public function __construct(MediaTransformerInterface $transformer, $parameters)
    {
        $reflection = new \ReflectionClass($transformer);

        parent::__construct(sprintf(
            'The following parameters are not defined for %s: %s',
            $reflection->getName(),
            print_r($parameters)
        ));
    }
}
