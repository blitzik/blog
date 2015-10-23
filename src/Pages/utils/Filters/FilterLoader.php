<?php

namespace Pages\Filters;

use Pages\Utils\BlogTexy;

class FilterLoader extends \Nette\Object
{
    /** @var BlogTexy  */
    private $texy;

    public function __construct(BlogTexy $texy)
    {
        $this->texy = $texy;
    }

    public function loader($filter)
    {
        if (!method_exists($this, $filter)) {
            return null;
        }

        return call_user_func_array([$this, $filter], array_slice(func_get_args(), 1));
    }

    public function texy($text)
    {
        return $this->texy->process($text);
    }

    /**
     * @param int $monthNumber
     * @return null|string
     */
    public function monthWord($monthNumber) // todo better name for method
    {
        switch ($monthNumber) {
            case 1: return 'Ledna'; break;
            case 2: return 'Února'; break;
            case 3: return 'Března'; break;
            case 4: return 'Dubna'; break;
            case 5: return 'Května'; break;
            case 6: return 'Června'; break;
            case 7: return 'Července'; break;
            case 8: return 'Srpna'; break;
            case 9: return 'Září'; break;
            case 10: return 'Října'; break;
            case 11: return 'Listopadu'; break;
            case 12: return 'Prosince'; break;

            default: return null;
        }
    }
}