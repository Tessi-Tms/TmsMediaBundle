<?php

/**
 * 
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @author:  Sekou KOÏTA <sekou.koita@supinfo.com>
 * @license: GPL
 *
 */
namespace Tms\Bundle\MediaBundle\StorageMapper\Rule;

use Tms\Bundle\MediaBundle\StorageMapper\Rule\AbstractRule;

abstract class AbstractSizeRule extends AbstractRule
{
    /**
     * To byte size
     *
     * @param  int $p_sFormatted
     */
    function toByteSize($p_sFormatted)
    {
        $aUnits = array('B'=>0, 'KB'=>1, 'MB'=>2, 'GB'=>3, 'TB'=>4, 'PB'=>5, 'EB'=>6, 'ZB'=>7, 'YB'=>8);
        $sUnit = strtoupper(trim(substr($p_sFormatted, -2)));

        if (intval($sUnit) !== 0) {
            $sUnit = 'B';
        }
        if (!in_array($sUnit, array_keys($aUnits))) {
            return false;
        }

        $iUnits = trim(substr($p_sFormatted, 0, strlen($p_sFormatted) - 2));

        if (!intval($iUnits) == $iUnits) {
            return false;
        }

        return $iUnits * pow(1024, $aUnits[$sUnit]);
    }
}
