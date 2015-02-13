<?php

namespace Pum\Core;

class Events
{
    const PROJECT_CREATE        = 'pum.project.create';
    const PROJECT_UPDATE        = 'pum.project.update';
    const PROJECT_SCHEMA_UPDATE = 'pum.project.schema_update';
    const PROJECT_BEAM_ADDED    = 'pum.project.beam_added';
    const PROJECT_BEAM_REMOVED  = 'pum.project.beam_removed';
    const PROJECT_DELETE        = 'pum.project.delete';

    const BEAM_CREATE         = 'pum.beam.create';
    const BEAM_UPDATE         = 'pum.beam.change';
    const BEAM_OBJECT_ADDED   = 'pum.beam.object_added';
    const BEAM_OBJECT_REMOVED = 'pum.beam.object_removed';
    const BEAM_DELETE         = 'pum.beam.delete';

    const OBJECT_DEFINITION_CREATE        = 'pum.object_definition.create';
    const OBJECT_DEFINITION_UPDATE        = 'pum.object_definition.update';
    const OBJECT_DEFINITION_SEARCH_UPDATE = 'pum.object_definition.search_update';
    const OBJECT_DEFINITION_SEO_UPDATE    = 'pum.object_definition.seo_update';
    const OBJECT_DEFINITION_FIELD_ADDED   = 'pum.object_definition.field_added';
    const OBJECT_DEFINITION_FIELD_UPDATED = 'pum.object_definition.field_updated';
    const OBJECT_DEFINITION_FIELD_REMOVED = 'pum.object_definition.field_removed';
    const OBJECT_DEFINITION_DELETE        = 'pum.object_definition.delete';

    const OBJECT_CREATE      = 'pum.object.create';
    const OBJECT_PRE_CREATE  = 'pum.object.pre_create';
    const OBJECT_INSERT      = 'pum.object.insert';
    const OBJECT_UPDATE      = 'pum.object.update';
    const OBJECT_DELETE      = 'pum.object.delete';
    const OBJECT_POST_LOAD   = 'pum.object.post_load';

    const OBJECT_FORM_PRE_SET_DATA  = 'pum.form.preSetData';
    const OBJECT_FORM_POST_SET_DATA = 'pum.form.postSetData';
    const OBJECT_FORM_PRE_SUBMIT    = 'pum.form.preSubmit';
    const OBJECT_FORM_SUBMIT        = 'pum.form.submit';
    const OBJECT_FORM_POST_SUBMIT   = 'pum.form.postSubmit';
}
