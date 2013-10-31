<?php

namespace Pum\Core;

class Events
{
    const BEAM_CHANGE = 'pum.beam.save';
    const BEAM_DELETE = 'pum.beam.delete';

    const PROJECT_CHANGE = 'pum.project.save';
    const PROJECT_DELETE = 'pum.project.delete';

    const OBJECT_PRE_CREATE = 'pum.object.pre_create'; // before object is persisted
    const OBJECT_CREATE     = 'pum.object.create'; // after ID is set
    const OBJECT_CHANGE     = 'pum.object.change';
    const OBJECT_DELETE     = 'pum.object.delete';
}
