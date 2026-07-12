<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Only affects Pest test files. The PHPUnit classes in tests/Feature keep their own setup.
uses(TestCase::class, RefreshDatabase::class)->in('Feature');
