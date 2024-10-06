<?php

use PHPUnit\Framework\TestCase;
use YourNamespace\MyLibrary;

class MyLibraryTest extends TestCase
{
    public function testGreet()
    {
        $library = new MyLibrary();
        $this->assertEquals("Hello, John!", $library->greet("John"));
        $this->assertEquals("Hello, Jane!", $library->greet("Jane"));
    }
}
