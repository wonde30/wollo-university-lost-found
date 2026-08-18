<?php

namespace DatabaseFactories;

use AppModelsAuditLog;
use IlluminateDatabaseEloquentFactoriesFactory;

class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            // Factory attributes
        ];
    }
}
