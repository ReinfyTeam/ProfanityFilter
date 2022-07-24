<?php

/*
 * PocketMine ClassLoader library
 * Copyright (C) 2021 PMMP Team <https://github.com/pmmp/ClassLoader>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
*/

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class BaseClassLoaderTest extends TestCase{
	/** @var BaseClassLoader */
	private $loader;

	public function setUp() : void{
		$this->loader = new BaseClassLoader();
		$this->loader->register(true);
	}

	public function testFallbackDirAutoloading() : void{
		$this->loader->addPath("", __DIR__ . '/fallback');
		self::assertTrue(class_exists(\GlobalFallbackClass::class, true));
		self::assertTrue(class_exists(\someNamespace\NamespacedFallbackClass::class, true));
		self::assertTrue(class_exists(\someNamespace\someSubNamespace\NestedNamespacedFallbackClass::class, true));
	}

	public function testPsr4DirAutoloading() : void{
		$this->loader->addPath("somePsr4Namespace", __DIR__ . '/psr4');
		self::assertTrue(class_exists(\somePsr4Namespace\TopNamespacePsr4Class::class, true));
		self::assertTrue(class_exists(\somePsr4Namespace\someSubNamespace\NestedNamespacePsr4Class::class, true));
	}
}
