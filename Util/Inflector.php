<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Sekou KOÏTA <sekou.koita@supinfo.com>
 */

namespace Tms\Bundle\MediaBundle\Util;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class Inflector
{
    /**
     * Returns given word as CamelCased
     *
     * Converts a word like "send_email" to "SendEmail". It
     * will remove non alphanumeric character from the word, so
     * "who's online" will be converted to "WhoSOnline"
     * @param string $word Word to convert to camel case
     * @return string UpperCamelCasedWord
     */
    static public function camelize($word)
    {
        return str_replace(' ', '', ucwords(preg_replace('/[^A-Z^a-z^0-9]+/', ' ', $word)));
    }

    /**
    * Converts a word "into_it_s_underscored_version"
    *
    * Convert any "CamelCased" or "ordinary Word" into an
    * "underscored_word".
    *
    * @param string $word Word to underscore
    * @return string Underscored word
    */
    static public function underscore($word)
    {
        return  strtolower(preg_replace('/[^A-Z^a-z^0-9]+/', '_',
            preg_replace('/([a-zd])([A-Z])/', '\1_\2',
            preg_replace('/([A-Z]+)([A-Z][a-z])/', '\1_\2', $word))))
        ;
    }

    /**
     * Get  extension
     *
     *@param string $filename
     *@return string
     */
    static public function getExtension($filename)
    {
        $parts = explode('.', $filename);

        return array_pop($parts);
    }

    /**
     *  Get valid image extensions
     *
     * @return string
     */
    static public function getValidImageExtensions()
    {
        return $this->container->getParameter('valid_image_extensions');
    }

}
