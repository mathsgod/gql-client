<?php

namespace GQL;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class MutationField extends Field
{
}
