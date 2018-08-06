<?php
namespace System;
import('System.FileDescriptor');

class File implements FileDescriptor{

	public function __construct(){

	}

    public function create(): void
    {
        // TODO: Implement create() method.
    }

    public function copy(): void
    {
        // TODO: Implement copy() method.
    }

    public function exists(): bool
    {
        // TODO: Implement exists() method.
    }

    public function getName(): string
    {
        // TODO: Implement getName() method.
    }

    public function getPath(bool $full = false): string
    {
        // TODO: Implement getPath() method.
    }

    public function getSize(?string $path = null): int
    {
        // TODO: Implement getSize() method.
    }

    public function lastModified(): int
    {
        // TODO: Implement lastModified() method.
    }

    public function move(): void
    {
        // TODO: Implement move() method.
    }

    public function remove(): void
    {
        // TODO: Implement remove() method.
    }

    public function rename(string $name): void
    {
        // TODO: Implement rename() method.
    }
}