<?php

declare(strict_types=1);

namespace App\Support;

use Tree\Node\Node;

class TreeNode extends Node
{
    public function hasChildren(): bool
    {
        return filled($this->getChildren());
    }

    public function hasChild($value): bool
    {
        return $this->hasChildren()
            && collect($this->getChildren())
                ->contains(fn (Node $child) => $child->getValue() === $value);
    }

    public function doesntHaveChild($value): bool
    {
        return !$this->hasChild($value);
    }
}
