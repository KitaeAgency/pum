<?php

namespace Pum\Bundle\ProjectAdminBundle\Extension\Widget;

use Doctrine\Common\Collections\ArrayCollection;
use Pum\Bundle\ProjectAdminBundle\Extension\Widget\Widget;

class WidgetContainer {
	private $widgets;
	private $ordered = false;

	public function __construct() {
		$this->widgets = new ArrayCollection();	
	}

	public function addWidget($widget) {
		$this->ordered = false;

		if ($widget instanceof ArrayCollection) {
			foreach ($widget as $w) {
				$this->widgets[] = $w;
			}
		}
		else if ($widget instanceof Widget) {
			$this->widgets[] = $widget;
		}
	}

	// http://www.algorithmist.com/index.php/Quicksort
	private function sortWidget($left = 0, $right = NULL) {
		if ($right == NULL) {
			$right = $this->widgets->count() - 1;
		}
 
		$i = $left;
		$j = $right;
 
		$tmp = $this->widgets[(($left + $right) / 2)];
		do
		{
			while ($this->widgets[$i]->getWeight() < $tmp->getWeight()) {
				$i++;
			}
			while ($tmp->getWeight() < $this->widgets[$j]->getWeight()) {
				$j--;
			}
 
			if ($i <= $j)
			{
				$w = $this->widgets[$i];
				if ($this->widgets[$i]->getWeight() > $this->widgets[$j]->getWeight()) {
					$this->widgets[$i] = $this->widgets[$j];
					$this->widgets[$j] = $w;
				}
 
				$i++;
				$j--;
			}
		} while ($i <= $j);
 
		if( $left < $j ) {
			$this->sortWidget(NULL, $left, $j);
		}

		if( $i < $right ) {
			$this->sortWidget(NULL, $i, $right);
		}
	}

	public function getWidgets() {
		if ($this->ordered == false && $this->widgets->count() >= 2) {
			// Order logic
			$this->sortWidget();
			$this->ordered = true;
		}

		return $this->widgets;
	}
}