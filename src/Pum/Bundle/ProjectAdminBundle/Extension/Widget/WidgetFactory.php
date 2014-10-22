<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Widget;

use Doctrine\Common\Collections\ArrayCollection;

class WidgetFactory {

    protected $widgets;
    protected $ordered = false;

    public function __construct()
    {
        $this->widgets = new ArrayCollection(); 
    }

    public function addWidget($widget)
    {
        $this->ordered = false;

        if ($widget instanceof ArrayCollection) {
            foreach ($widget as $w) {
                if ($w instanceof Widget) {
                    $this->widgets[] = $w;
                }
            }
        } elseif ($widget instanceof Widget) {
            $this->widgets[] = $widget;
        }
    }

    public function hasWidget($uid)
    {
        foreach ($this->widgets as $widget) {
            if ($uid === $widget->getUid()) {
                return true;
            }
        }

        return false;
    }

    public function removeWidget($w)
    {
        switch (true) {
            case $w instanceof Widget:
                if ($this->objects->contains($w)) {
                    $this->widgets()->removeElement($w);
                }
                break;

            case is_string($w):
                foreach ($this->widgets as $widget) {
                    if ($w === $widget->getUid()) {
                        $this->widgets()->removeElement($w);

                        break;
                    }
                }
                break;
        }

        return $this;
    }

    public function removeWidgets()
    {
        $this->widgets = new ArrayCollection(); 

        return $this;
    }

    // http://www.algorithmist.com/index.php/Quicksort
    protected function sortWidget($left = 0, $right = NULL)
    {
        if ($right == NULL) {
            $right = $this->widgets->count() - 1;
        }

        $i = $left;
        $j = $right;

        $tmp = $this->widgets[(($left + $right) / 2)];
        do {
            while ($this->widgets[$i] && ($this->widgets[$i]->getWeight() < $tmp->getWeight())) {
                $i++;
            }
            while ($this->widgets[$j] && ($tmp->getWeight() < $this->widgets[$j]->getWeight())) {
                $j--;
            }

            if ($i <= $j) {
                $w = $this->widgets[$i];
                if ($this->widgets[$i]->getWeight() > $this->widgets[$j]->getWeight()) {
                    $this->widgets[$i] = $this->widgets[$j];
                    $this->widgets[$j] = $w;
                }

                $i++;
                $j--;
            }

        } while ($i <= $j);

        if ($left < $j) {
            $this->sortWidget($left, $j);
        }

        if ($i < $right) {
            $this->sortWidget($i, $right);
        }
    }

    public function getWidgets() 
    {
        if (false === $this->ordered && $this->widgets->count() > 1) {
            $this->sortWidget();
            $this->ordered = true;
        }

        return $this->widgets;
    }

}
