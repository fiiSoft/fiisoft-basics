<?php

namespace FiiSoft\Tools\Validation;

interface SpecificationError
{
    const UNSPECIFIED_FIELDS = 1;
    const WRONG_NAME = 2;
    const NO_ATTRIBUTES = 3;
    const UNSPECIFIED_ATTRIBUTES = 4;
    const MISSING_ATTRIBUTE = 5;
    const INVALID_ENUM = 6;
    const TOO_LONG_ATTRIBUTE = 7;
    const INVALID_DATE_FORMAT = 8;
    const NOT_INTEGER = 9;
    const MISSING_CHILDREN = 10;
    const MALFORMED_ITEM_DATA = 11;
    const UNSPECIFIED_CHILD = 12;
    const MISSING_CHILD = 13;
    const CHILDLESS_CHILD = 14;
    const EMPTY_ITEM = 15;
}