<?php

namespace Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\Fixture;

use Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\Source\FirstAttribute;
use Rector\Tests\CodingStyle\Rector\ClassMethod\OrderAttributesRector\Source\SecondAttribute;

#[FirstAttribute]
#[SecondAttribute]
class SkipExpectedOrder
{
}