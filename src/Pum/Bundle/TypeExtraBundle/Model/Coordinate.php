<?php

namespace Pum\Bundle\TypeExtraBundle\Model;

/**
 * The value of a coordinate object should NEVER change. You should instead use
 * new objects.
 */
class Coordinate
{
    protected $lat;
    protected $lng;

    public function __construct($lat = null, $lng = null)
    {
        $this->lat = $lat;
        $this->lng = $lng;
    }

    /**
     * @see self::__construct
     *
     * @return Coordinate
     */
    static public function createFromString($string)
    {
        $coordinateData = explode(",", $string);
        if (count($coordinateData) !== 2 || (!is_numeric($coordinateData[0]) || !is_numeric($coordinateData[1]))) {
            throw new \Exception('Please provide a valid coordinate string : "latitude,longitude"');
        }

        return new self($coordinateData[0], $coordinateData[1]);
    }

    /**
     * @return string
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @return Coordinate
     */
    public function setLat($lat)
    {
        return new self($lat, $this->getLng());
    }

    /**
     * @return string
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * @return Coordinate
     */
    public function setLng($lng)
    {
        return new self($this->getLat(), $lng);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->lat.','.$this->lng;
    }
}
