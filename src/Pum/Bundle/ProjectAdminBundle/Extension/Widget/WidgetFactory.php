<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Widget;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\SecurityContext;

class WidgetFactory
{
    protected $widgets;
    protected $ordered = false;

    protected $securityContext;

    public function __construct(SecurityContext $securityContext)
    {
        $this->widgets = new ArrayCollection();

        $this->securityContext = $securityContext;
    }

    public function addWidget($widget)
    {
        $this->ordered = false;

        if ($widget instanceof ArrayCollection) {
            foreach ($widget as $w) {
                if ($w instanceof Widget) {
                    if (false === $this->hasWidget($w->getName())) {
                        $this->widgets->add($w);
                    } else {
                        throw new \RuntimeException(sprintf('A widget named "%s" already exists.', $w->getName()));
                    }
                }
            }
        } elseif ($widget instanceof Widget) {
            if (false === $this->hasWidget($widget->getName())) {
                $this->widgets->add($widget);
            } else {
                throw new \RuntimeException(sprintf('A widget named "%s" already exists.', $widget->getName()));
            }
        }
    }

    public function hasWidget($name)
    {
        foreach ($this->widgets as $widget) {
            if ($name === $widget->getName()) {
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
                    if ($w === $widget->getName()) {
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
    protected function sortWidget($left = 0, $right = null)
    {
        if ($right == null) {
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

        $user = $this->securityContext->getToken()->getUser();

        return $this->widgets->filter(function($widget) use ($user) {
            $permission = $widget->getPermission();
            $groups = $widget->getGroups();

            $userGroup = $user->getGroup();

            if ((!$permission || ($permission && $this->securityContext->isGranted($permission, $widget->getPermissionParameters())) &&
                ($groups->isEmpty() || ($userGroup && $groups->contains($userGroup->getName()))))) {
                return true;
            }

            return false;
        });
    }
}
