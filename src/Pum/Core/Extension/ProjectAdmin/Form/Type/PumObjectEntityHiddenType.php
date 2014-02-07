<?php

namespace Pum\Core\Extension\ProjectAdmin\Form\Type;

use Symfony\Component\Form\AbstractType;

class PumObjectEntityHiddenType extends AbstractType {
    
    public function getParent() {
        return 'pum_object_entity';
    }
    
    public function getName() {
        return 'pum_object_entity_hidden';
    }

}

?>
