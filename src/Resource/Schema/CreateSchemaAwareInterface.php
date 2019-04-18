<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Admin\Resource\Schema;

use Ixocreate\Admin\Package\UserInterface;
use Ixocreate\Schema\Package\BuilderInterface;
use Ixocreate\Schema\Package\SchemaInterface;

interface CreateSchemaAwareInterface
{
    public function createSchema(BuilderInterface $builder, UserInterface $user): SchemaInterface;
}
